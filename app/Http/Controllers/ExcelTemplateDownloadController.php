<?php

namespace App\Http\Controllers;

use App\Models\ExcelTemplate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelTemplateDownloadController
{
    public function __invoke(ExcelTemplate $excelTemplate): StreamedResponse
    {
        abort_unless($excelTemplate->is_active, 404);

        $disk = Storage::disk($excelTemplate->disk);
        abort_unless($disk->exists($excelTemplate->file_path), 404, 'Template file not found.');

        return $disk->download(
            $excelTemplate->file_path,
            $excelTemplate->original_file_name,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }
}
