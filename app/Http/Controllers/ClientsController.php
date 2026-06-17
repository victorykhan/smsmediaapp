<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClientsController extends Controller
{
    public function index()
    {
        $owned = auth()->user()->ownedClients;
        $member = auth()->user()->clients;

        return view('clients.index', compact('owned', 'member'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'timezone' => ['nullable', 'string', 'max:64'],
        ]);

        $client = auth()->user()->ownedClients()->create($validated);

        return redirect()->route('clients.onboarding', $client)
            ->with('success', 'Client created. Let\'s complete the setup.');
    }

    public function show(Client $client)
    {
        $this->authorizeAccess($client);
        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        $this->authorizeAccess($client);
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $this->authorizeAccess($client);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'brand_colors' => ['nullable', 'json'],
        ]);

        if (isset($validated['brand_colors'])) {
            $validated['brand_colors'] = json_decode($validated['brand_colors'], true);
        }

        $client->update($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client updated.');
    }

    public function destroy(Client $client)
    {
        $this->authorizeAccess($client);
        $client->delete();
        return redirect()->route('clients.index')
            ->with('success', 'Client deleted.');
    }

    public function onboarding(Client $client)
    {
        $this->authorizeAccess($client);
        return view('clients.onboarding', compact('client'));
    }

    public function onboardingStep(Request $request, Client $client)
    {
        $this->authorizeAccess($client);

        $step = $request->input('step', 1);
        $completed = $request->boolean('completed');

        if ($completed) {
            if ($step >= 4) {
                $client->update(['onboarding_step' => 4, 'is_onboarded' => true]);
                return redirect()->route('clients.show', $client)
                    ->with('success', 'Client setup complete!');
            }
            $client->update(['onboarding_step' => $step]);
        }

        return response()->json(['step' => $client->onboarding_step]);
    }

    public function invite(Request $request, Client $client)
    {
        $this->authorizeAccess($client);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:admin,editor,viewer'],
        ]);

        $validated['client_id'] = $client->id;
        $validated['token'] = Str::random(32);
        $validated['expires_at'] = now()->addDays(7);

        $client->invitations()->create($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Invitation sent to ' . $validated['email']);
    }

    public function updateMemberRole(Request $request, Client $client, User $user)
    {
        if ($client->user_id !== auth()->id()) {
            abort(403, 'Only the owner can change roles.');
        }

        $validated = $request->validate([
            'role' => ['required', 'in:admin,editor,viewer'],
        ]);

        $client->users()->updateExistingPivot($user->id, ['role' => $validated['role']]);

        return redirect()->route('clients.show', $client)
            ->with('success', $user->name . "'s role updated to " . $validated['role'] . '.');
    }

    public function removeMember(Client $client, User $user)
    {
        if ($client->user_id !== auth()->id()) {
            abort(403, 'Only the owner can remove team members.');
        }

        $client->users()->detach($user->id);

        return redirect()->route('clients.show', $client)
            ->with('success', $user->name . ' removed from team.');
    }

    private function authorizeAccess(Client $client)
    {
        if ($client->user_id !== auth()->id() &&
            !$client->users()->where('user_id', auth()->id())->exists()) {
            abort(403);
        }
    }
}
