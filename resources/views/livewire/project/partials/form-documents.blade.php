{{-- Project document uploads --}}
<section x-show="activeSection === 'documents'" x-cloak
    class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="border-b border-gray-200 bg-gray-50 px-5 py-4">
        <h3 class="font-semibold text-gray-900">Project documents</h3>
        <p class="mt-1 text-sm text-gray-500">
            Upload or replace the PDA, Project ideas and Project Handover Certificate files.
        </p>
    </div>

    <div class="grid grid-cols-1 items-start gap-5 p-5 lg:grid-cols-3">
        @include('livewire.project.partials.form-document-pda')
        @include('livewire.project.partials.form-document-ideas')
        @include('livewire.project.partials.form-document-handover')
    </div>
</section>
