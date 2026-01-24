<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\MediaService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /* ==================================================
     | Media Library
     ================================================== */
    public function index(Request $request)
    {
        $query = Media::query();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where('original_name', 'like', '%' . $request->search . '%');
        }

        $media = $query
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('content.media.index', compact('media'));
    }

    /* ==================================================
     | Upload Form
     ================================================== */
    public function create()
    {
        return view('content.media.create');
    }

    /* ==================================================
     | Store Media
     ================================================== */
    public function store(Request $request, MediaService $mediaService)
    {
        $request->validate([
            'files'   => ['required', 'array', 'min:1'],
            'files.*' => [
                'file',
                'max:51200', // 50MB
                'mimetypes:image/*,video/*,audio/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
        ]);

        DB::beginTransaction();

        try {
            $directory = 'uploads/' . now()->format('Y/m');

            foreach ($request->file('files') as $file) {
                $mediaService->upload(
                    $file,
                    auth()->user(), // owner
                    $directory
                );
            }

            DB::commit();

            return redirect()
                ->route('media.index')
                ->with('success', 'Media uploaded successfully');

        } catch (\Throwable $e) {

            DB::rollBack();
            report($e);

            return back()
                ->withErrors([
                    'upload' => 'Upload failed. Please try again.',
                ])
                ->withInput();
        }
    }


    /* ==================================================
     | Delete Media
     ================================================== */
    public function destroy(Media $media)
    {
        DB::beginTransaction();

        try {
            if (Storage::disk($media->disk)->exists($media->path)) {
                Storage::disk($media->disk)->delete($media->path);
            }

            $media->delete();

            DB::commit();

            return back()->with('success', 'Media deleted successfully');

        } catch (\Throwable $e) {

            DB::rollBack();
            report($e);

            return back()->withErrors([
                'delete' => 'Failed to delete media.',
            ]);
        }
    }

    /* ==================================================
     | Helpers
     ================================================== */
    protected function detectType(string $mime): string
    {
        return match (true) {
            str_starts_with($mime, 'image/') => 'image',
            str_starts_with($mime, 'video/') => 'video',
            str_starts_with($mime, 'audio/') => 'audio',
            in_array($mime, [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]) => 'document',
            default => 'other',
        };
    }

    
}
