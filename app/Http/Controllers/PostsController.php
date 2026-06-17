<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Post;
use App\Models\PostVersion;
use App\Notifications\PostApproved;
use App\Notifications\PostRejected;
use App\Notifications\PostSubmitted;
use App\Services\Platforms\PlatformFactory;
use App\Services\PreUploadService;
use App\Services\PublishService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostsController extends Controller
{
    public function __construct(
        private PreUploadService $preUpload,
        private PlatformFactory $platforms
    ) {}

    public function index(Client $client)
    {
        $this->authorizeView($client);
        $posts = Post::forClient($client)
            ->with('versions.socialAccount', 'user', 'reviewer')
            ->latest()
            ->paginate(20);
        return view('posts.index', compact('client', 'posts'));
    }

    public function create(Client $client)
    {
        $this->authorizeEditorOrAbove($client);
        $accounts = $client->socialAccounts()->active()->get();
        $platforms = config('platforms');
        $role = $client->userRole(auth()->user());
        return view('posts.composer', compact('client', 'accounts', 'platforms', 'role'));
    }

    public function store(Request $request, Client $client, PublishService $publish)
    {
        $this->authorizeEditorOrAbove($client);
        $canPostDirectly = $client->canPostDirectly(auth()->user());

        $validated = $request->validate([
            'content' => ['nullable', 'string'],
            'accounts' => ['required', 'array', 'min:1'],
            'accounts.*' => ['exists:social_accounts,id'],
            'schedule_at' => ['nullable', 'date', 'after_or_equal:now'],
            'media_paths' => ['nullable', 'string'],
        ]);

        $accounts = $client->socialAccounts()->active()->whereIn('id', $validated['accounts'])->get();
        if ($accounts->isEmpty()) {
            return back()->with('error', 'No valid accounts selected.');
        }

        $isScheduled = isset($validated['schedule_at']);
        $mediaPaths = $validated['media_paths'] ? json_decode($validated['media_paths'], true) : [];

        $validationError = $this->validateConstraints($accounts, $validated['content'] ?? '', $mediaPaths);
        if ($validationError) {
            return back()->with('error', $validationError)->withInput();
        }

        $post = DB::transaction(function () use ($client, $validated, $accounts, $mediaPaths) {
            $post = Post::create([
                'client_id' => $client->id,
                'user_id' => auth()->id(),
                'content' => $validated['content'],
                'status' => 'draft',
            ]);

            foreach ($accounts as $account) {
                PostVersion::create([
                    'post_id' => $post->id,
                    'social_account_id' => $account->id,
                    'platform' => $account->platform,
                    'content' => $validated['content'],
                    'media' => $mediaPaths,
                    'status' => 'draft',
                    'media_status' => !empty($mediaPaths) ? 'pending' : 'no_media',
                ]);
            }

            return $post;
        });

        // Pre-upload media to all selected platforms immediately
        if (!empty($mediaPaths)) {
            $post->load('versions.socialAccount');
            $this->preUpload->uploadPostMedia($post);
            $post->refresh();
        }

        if ($canPostDirectly) {
            if ($isScheduled) {
                $publish->schedule($post, $validated['schedule_at']);
                return redirect()->route('posts.index', $client)
                    ->with('success', 'Post scheduled for ' . $validated['schedule_at'] . '.');
            }

            $publish->publishNow($post);
            return redirect()->route('posts.index', $client)
                ->with('success', 'Post published successfully.');
        }

        $post->update(['status' => 'pending_approval']);
        $adminIds = $client->users()->wherePivot('role', 'admin')->pluck('user_id')->push($client->user_id);
        \App\Models\User::whereIn('id', $adminIds)->get()->each(fn($u) => $u->notify(new PostSubmitted($post)));

        return redirect()->route('posts.index', $client)
            ->with('success', 'Post submitted for approval.');
    }

    public function show(Client $client, Post $post)
    {
        $this->authorizeView($client);
        $post->load('versions.socialAccount', 'user', 'reviewer');
        return view('posts.show', compact('client', 'post'));
    }

    public function edit(Client $client, Post $post)
    {
        $this->authorizeEditorOrAbove($client);
        $accounts = $client->socialAccounts()->active()->get();
        $platforms = config('platforms');
        $selectedAccounts = $post->versions->pluck('social_account_id')->toArray();
        return view('posts.edit', compact('client', 'post', 'accounts', 'platforms', 'selectedAccounts'));
    }

    public function update(Request $request, Client $client, Post $post, PublishService $publish)
    {
        $this->authorizeEditorOrAbove($client);
        $canPostDirectly = $client->canPostDirectly(auth()->user());

        $validated = $request->validate([
            'content' => ['nullable', 'string'],
            'accounts' => ['required', 'array', 'min:1'],
            'accounts.*' => ['exists:social_accounts,id'],
            'schedule_at' => ['nullable', 'date', 'after_or_equal:now'],
            'media_paths' => ['nullable', 'string'],
        ]);

        $accounts = $client->socialAccounts()->active()->whereIn('id', $validated['accounts'])->get();
        if ($accounts->isEmpty()) {
            return back()->with('error', 'No valid accounts selected.');
        }

        $isScheduled = isset($validated['schedule_at']);
        $mediaPaths = $validated['media_paths'] ? json_decode($validated['media_paths'], true) : [];
        $existingMedia = $post->versions->first()->media ?? [];
        $finalMedia = !empty($mediaPaths) ? $mediaPaths : $existingMedia;

        $validationError = $this->validateConstraints($accounts, $validated['content'] ?? '', $finalMedia);
        if ($validationError) {
            return back()->with('error', $validationError)->withInput();
        }

        DB::transaction(function () use ($post, $validated, $accounts, $mediaPaths, $existingMedia) {
            $post->update(['content' => $validated['content']]);
            $post->versions()->delete();

            $finalMedia = !empty($mediaPaths) ? $mediaPaths : $existingMedia;

            foreach ($accounts as $account) {
                PostVersion::create([
                    'post_id' => $post->id,
                    'social_account_id' => $account->id,
                    'platform' => $account->platform,
                    'content' => $validated['content'],
                    'media' => $finalMedia,
                    'status' => 'draft',
                    'media_status' => !empty($finalMedia) ? 'pending' : 'no_media',
                ]);
            }
        });

        // Pre-upload media if needed
        $post->load('versions.socialAccount');
        $needsUpload = $post->versions->contains(fn($v) => $v->media_status === 'pending');
        if ($needsUpload) {
            $this->preUpload->uploadPostMedia($post);
            $post->refresh();
        }

        if ($canPostDirectly) {
            if ($isScheduled) {
                $publish->schedule($post, $validated['schedule_at']);
                return redirect()->route('posts.show', [$client, $post])
                    ->with('success', 'Post updated and scheduled.');
            }

            $publish->publishNow($post);
            return redirect()->route('posts.show', [$client, $post])
                ->with('success', 'Post updated and published.');
        }

        $post->update(['status' => 'pending_approval']);
        $adminIds = $client->users()->wherePivot('role', 'admin')->pluck('user_id')->push($client->user_id);
        \App\Models\User::whereIn('id', $adminIds)->get()->each(fn($u) => $u->notify(new PostSubmitted($post)));
        return redirect()->route('posts.show', [$client, $post])
            ->with('success', 'Post updated and submitted for approval.');
    }

    public function destroy(Client $client, Post $post)
    {
        $this->authorizeEditorOrAbove($client);
        $post->delete();
        return redirect()->route('posts.index', $client)
            ->with('success', 'Post deleted.');
    }

    public function approve(Client $client, Post $post, PublishService $publish)
    {
        $this->authorizeApproval($client);

        if (!$post->isPendingApproval()) {
            return back()->with('error', 'Post is not pending approval.');
        }

        $publish->publishNow($post);
        $post->update([
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $post->user->notify(new PostApproved($post));

        return redirect()->route('posts.show', [$client, $post])
            ->with('success', 'Post approved and published.');
    }

    public function reject(Request $request, Client $client, Post $post)
    {
        $this->authorizeApproval($client);

        if (!$post->isPendingApproval()) {
            return back()->with('error', 'Post is not pending approval.');
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $post->update([
            'status' => 'draft',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => $validated['reason'] ?? null,
        ]);

        $post->user->notify(new PostRejected($post));

        return redirect()->route('posts.show', [$client, $post])
            ->with('success', 'Post rejected and returned to draft.');
    }

    private function authorizeView(Client $client): void
    {
        if (!$client->canView(auth()->user())) abort(403);
    }

    private function authorizeEditorOrAbove(Client $client): void
    {
        $role = $client->userRole(auth()->user());
        if (!$role || $role === 'viewer') abort(403);
    }

    private function authorizeApproval(Client $client): void
    {
        if (!$client->canApprove(auth()->user())) abort(403);
    }

    private function validateConstraints($accounts, string $content, array $mediaPaths): ?string
    {
        $errors = [];

        foreach ($accounts as $account) {
            try {
                $service = $this->platforms->make($account->platform);
            } catch (\Exception $e) {
                continue;
            }

            $constraints = $service->getConstraints();

            $mediaRequired = $constraints['media_required'] ?? null;
            $mediaAllowed = $constraints['media_allowed'] ?? [];
            $textLimit = $constraints['text_limit'] ?? null;

            $label = $constraints['label'] ?? ucfirst($account->platform);

            if ($mediaRequired && empty($mediaPaths)) {
                $errors[] = "$label requires media ({$mediaRequired}).";
            }

            if ($textLimit && mb_strlen($content) > $textLimit) {
                $errors[] = "$label has a {$textLimit}-character limit.";
            }
        }

        return !empty($errors) ? implode(' ', $errors) : null;
    }
}
