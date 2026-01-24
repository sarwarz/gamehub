<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        $media = $query->latest()->paginate(30)->withQueryString();

        return view('content.media.index', compact('media'));
    }

    /* ==================================================
     | Upload Form
     ================================================== */
    public function create()
    {
        return view('media.create');
    }

    /* ==================================================
     | Store Media
     ================================================== */
    public function store(Request $request)
    {
        $request->validate([
            'files'   => ['required', 'array'],
            'files.*' => ['file', 'max:51200'], // 50MB
        ]);

        DB::beginTransaction();

        try {

            foreach ($request->file('files') as $file) {

                $disk      = 'public';
                $directory = 'uploads/' . now()->format('Y/m');

                $originalName = $file->getClientOriginalName();
                $extension    = $file->getClientOriginalExtension();
                $mime         = $file->getMimeType();
                $size         = $file->getSize();

                $filename = Str::uuid() . '.' . $extension;

                Storage::disk($disk)->putFileAs(
                    $directory,
                    $file,
                    $filename
                );

                $type = $this->detectType($mime);

                $meta = [];

                if ($type === 'image') {
                    [$width, $height] = getimagesize($file->getRealPath());
                    $meta = compact('width', 'height');
                }

                Media::create([
                    'disk'          => $disk,
                    'directory'     => $directory,
                    'filename'      => $filename,
                    'original_name' => $originalName,
                    'mime_type'     => $mime,
                    'extension'     => $extension,
                    'type'          => $type,
                    'size'          => $size,
                    'meta'          => $meta,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('media.index')
                ->with('success', 'Media uploaded successfully');

        } catch (\Throwable $e) {

            DB::rollBack();

            return back()
                ->withErrors(['upload' => $e->getMessage()])
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

            // Delete file
            if (Storage::disk($media->disk)->exists($media->path)) {
                Storage::disk($media->disk)->delete($media->path);
            }

            $media->delete();

            DB::commit();

            return back()->with('success', 'Media deleted successfully');

        } catch (\Throwable $e) {

            DB::rollBack();

            return back()->withErrors(['delete' => $e->getMessage()]);
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
