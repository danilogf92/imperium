<?php

namespace App\Http\Controllers;

use App\Models\ExcelTemplate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelTemplateDownloadController
{
    public function __invoke(ExcelTemplate $excelTemplate): StreamedResponse
    {
        abort_unless(auth()->check(), 403);
        abort_unless(ExcelTemplate::query()->active()->visibleTo(auth()->user())
            ->whereKey($excelTemplate->id)->exists(), 404);

        $disk = Storage::disk($excelTemplate->disk);
        abort_unless($disk->exists($excelTemplate->file_path), 404, 'Template file not found.');

        return $disk->download(
            $excelTemplate->file_path,
            $excelTemplate->original_file_name,
            ['Content-Type' => ExcelTemplate::FILE_TYPES[$excelTemplate->fileType()] ?? 'application/octet-stream']
        );
    }
}
