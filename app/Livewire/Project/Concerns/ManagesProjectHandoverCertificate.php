<?php

namespace App\Livewire\Project\Concerns;

use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait ManagesProjectHandoverCertificate
{
    private function validateHandoverCertificate(): void
    {
        if (! $this->handoverCertificate) {
            return;
        }

        $this->validate([
            'handoverCertificate' => ['required', 'file', 'extensions:pdf', 'mimes:pdf', 'max:10240'],
        ], [
            'handoverCertificate.extensions' => 'The Project Handover Certificate must be a PDF file.',
            'handoverCertificate.mimes' => 'Only a valid PDF file is allowed.',
            'handoverCertificate.max' => 'The Project Handover Certificate may not be larger than 10 MB.',
        ]);
    }

    private function storeHandoverCertificate(Project $project): void
    {
        if (! $this->handoverCertificate) {
            return;
        }

        $originalName = $this->handoverCertificate->getClientOriginalName();
        $baseName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'project-handover-certificate';
        $path = $this->handoverCertificate->storeAs(
            "projects/{$project->id}/handover-certificates",
            Str::uuid().'-'.$baseName.'.pdf',
            'public'
        );
        $previousPath = $project->handover_certificate_path;

        $project->update([
            'handover_certificate_path' => $path,
            'handover_certificate_name' => $originalName,
        ]);

        if (filled($previousPath) && $previousPath !== $path
            && str_starts_with($previousPath, "projects/{$project->id}/handover-certificates/")) {
            Storage::disk('public')->delete($previousPath);
        }

        $this->reset('handoverCertificate');
    }
}
