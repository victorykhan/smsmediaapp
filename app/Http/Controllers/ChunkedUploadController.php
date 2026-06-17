<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChunkedUploadController extends Controller
{
    private string $chunkDir = 'chunks';

    /**
     * Receive a single chunk and append it to the temp file.
     *
     * POST /upload/chunk
     * Body: { file_id, index, total_chunks, data (base64), name, size }
     */
    public function chunk(Request $request)
    {
        $request->validate([
            'file_id' => 'required|string',
            'index'   => 'required|integer|min:0',
            'total'   => 'required|integer|min:1',
            'data'    => 'required|string',
            'name'    => 'required|string|max:255',
        ]);

        $fileId = preg_replace('/[^a-zA-Z0-9_-]/', '', $request->file_id);
        $chunkDir = storage_path("app/{$this->chunkDir}/{$fileId}");
        if (!is_dir($chunkDir)) {
            mkdir($chunkDir, 0755, true);
        }

        $chunkPath = "{$chunkDir}/chunk_{$request->index}";
        $decoded = base64_decode($request->data);
        file_put_contents($chunkPath, $decoded);

        // If this is the last chunk, assemble the file
        if ((int)$request->index === (int)$request->total - 1) {
            return $this->assemble($fileId, $request->name, $request->total);
        }

        return response()->json([
            'status' => 'chunk_received',
            'index'  => (int)$request->index,
            'total'  => (int)$request->total,
        ]);
    }

    /**
     * Assemble all chunks into the final file.
     */
    private function assemble(string $fileId, string $name, int $total): \Illuminate\Http\JsonResponse
    {
        $chunkDir = storage_path("app/{$this->chunkDir}/{$fileId}");
        $safeName = $fileId . '-' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
        $finalPath = storage_path("app/public/uploads/{$safeName}");

        if (!is_dir(storage_path('app/public/uploads'))) {
            mkdir(storage_path('app/public/uploads'), 0755, true);
        }

        $fp = fopen($finalPath, 'wb');
        if (!$fp) {
            return response()->json(['error' => 'Could not open final file.'], 500);
        }

        for ($i = 0; $i < $total; $i++) {
            $chunkPath = "{$chunkDir}/chunk_{$i}";
            if (!file_exists($chunkPath)) {
                fclose($fp);
                return response()->json(['error' => "Missing chunk {$i}."], 400);
            }
            fwrite($fp, file_get_contents($chunkPath));
            unlink($chunkPath);
        }

        fclose($fp);
        rmdir($chunkDir);

        $relativePath = "uploads/{$safeName}";

        return response()->json([
            'status' => 'complete',
            'path'   => $relativePath,
            'url'    => Storage::disk('public')->url($relativePath),
            'size'   => filesize($finalPath),
        ]);
    }

    /**
     * Check which chunks exist for a given fileId.
     * Used for resuming interrupted uploads after page reload.
     */
    public function status(string $fileId)
    {
        $fileId = preg_replace('/[^a-zA-Z0-9_-]/', '', $fileId);
        $chunkDir = storage_path("app/{$this->chunkDir}/{$fileId}");

        if (!is_dir($chunkDir)) {
            return response()->json([
                'file_id' => $fileId,
                'chunks' => [],
                'exists' => false,
            ]);
        }

        $chunks = [];
        $files = glob("{$chunkDir}/chunk_*");
        if ($files) {
            foreach ($files as $f) {
                $index = (int) str_replace('chunk_', '', basename($f));
                $chunks[] = $index;
            }
        }

        return response()->json([
            'file_id' => $fileId,
            'chunks' => $chunks,
            'exists' => !empty($chunks),
        ]);
    }

    /**
     * Delete uploaded chunks (cancel upload).
     */
    public function cancel(Request $request)
    {
        $fileId = preg_replace('/[^a-zA-Z0-9_-]/', '', $request->file_id);
        $chunkDir = storage_path("app/{$this->chunkDir}/{$fileId}");
        if (is_dir($chunkDir)) {
            array_map('unlink', glob("{$chunkDir}/*"));
            rmdir($chunkDir);
        }
        return response()->json(['status' => 'cancelled']);
    }
}
