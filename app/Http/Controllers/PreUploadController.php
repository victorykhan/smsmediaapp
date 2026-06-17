<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Post;
use App\Models\PostVersion;
use App\Services\PreUploadService;
use Illuminate\Http\Request;

class PreUploadController extends Controller
{
    public function __construct(
        private PreUploadService $preUpload
    ) {}

    /**
     * Trigger media pre-upload for all versions of a post.
     * Called after post creation — uploads media as drafts to each platform.
     */
    public function upload(Client $client, Post $post)
    {
        $this->authorize('view', $client);

        $post->load('versions.socialAccount');
        $results = $this->preUpload->uploadPostMedia($post);

        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $failCount = count($results) - $successCount;

        $allMediaStatuses = $post->versions()->pluck('media_status');

        return response()->json([
            'results' => $results,
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'all_uploaded' => $allMediaStatuses->every(fn($s) => in_array($s, ['uploaded', 'no_media'])),
        ]);
    }

    /**
     * Get pre-upload status for all versions of a post.
     */
    public function status(Client $client, Post $post)
    {
        $versions = $post->versions()->select('id', 'platform', 'media_status', 'error_message', 'media')->get();

        return response()->json([
            'versions' => $versions->map(fn($v) => [
                'id' => $v->id,
                'platform' => $v->platform,
                'label' => config("platforms.{$v->platform}.label", $v->platform),
                'icon' => config("platforms.{$v->platform}.icon", '?'),
                'color' => config("platforms.{$v->platform}.color", '#666'),
                'media_status' => $v->media_status,
                'has_media' => !empty($v->media),
                'error' => $v->error_message,
            ]),
        ]);
    }
}
