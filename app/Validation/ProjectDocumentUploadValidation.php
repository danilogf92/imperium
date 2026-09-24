<?php

namespace App\Validation;

class ProjectDocumentUploadValidation
{
    public static function rules(): array
    {
        return [
            'document' => [
                'required',
                'file',
                'extensions:pdf',
                'mimes:pdf',
                'max:10240',
            ],
        ];
    }

    public static function messages(): array
    {
        return [
            'document.required' => 'Select a document to upload.',
            'document.file' => __('The selected item must be a valid file.'),
            'document.extensions' => __('The PDA must use the .pdf extension.'),
            'document.mimes' => __('Only PDF files are allowed.'),
            'document.max' => __('The document may not be larger than 10 MB.'),
        ];
    }

    public static function attributes(): array
    {
        return [
            'document' => 'project document',
        ];
    }
}
