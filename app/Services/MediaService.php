<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MediaService
{
    /**
     * Upload a single file and create media record
     */
    public function upload(
        UploadedFile $file,
        ?Model $owner = null,
        string $directory = 'uploads/media',
        bool $isPrimary = false
    ): Media {
        // Ensure directory exists
        $destinationPath = public_path($directory);

        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        // File info
        $originalName = $file->getClientOriginalName();
        $extension    = strtolower($file->getClientOriginalExtension());
        $mime         = $file->getMimeType();
        $size         = $file->getSize();
        $filename     = Str::uuid() . '.' . $extension;

        // Move file
        $file->move($destinationPath, $filename);

        // Detect media type
        $type = $this->detectType($mime);

        // Meta (image dimensions)
        $meta = [];

        if ($type === 'image') {
            try {
                [$width, $height] = getimagesize($destinationPath . '/' . $filename);
                $meta['width']  = $width;
                $meta['height'] = $height;
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $data = [
            'disk'          => 'public',
            'directory'     => $directory,
            'filename'      => $filename,
            'original_name' => $originalName,
            'mime_type'     => $mime,
            'extension'     => $extension,
            'type'          => $type,
            'size'          => $size,
            'meta'          => $meta,
            'is_primary'    => $isPrimary,
        ];

        // Save via morph relation if owner exists
        if ($owner) {
            if ($isPrimary) {
                $this->unsetPrimary($owner);
            }

            return $owner->media()->create($data);
        }

        return Media::create($data);
    }

    /**
     * Upload multiple files
     */
    public function uploadMany(
        array $files,
        ?Model $owner = null,
        string $directory = 'uploads/media'
    ): array {
        $media = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $media[] = $this->upload($file, $owner, $directory);
            }
        }

        return $media;
    }

    /**
     * Delete media (file + DB)
     */
    public function delete(Media $media): bool
    {
        $path = public_path($media->path);

        if (file_exists($path)) {
            @unlink($path);
        }

        return (bool) $media->delete();
    }

    /**
     * Detect media type by mime
     */
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
            ]) => 'document',
            default => 'file',
        };
    }

    /**
     * Unset existing primary media for owner
     */
    protected function unsetPrimary(Model $owner): void
    {
        $owner->media()
            ->where('is_primary', true)
            ->update(['is_primary' => false]);
    }
}
