<div>
    <button type="button" wire:click="openModal" data-no-global-loading title="Delete project"
        class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-red-600 text-white shadow-sm transition duration-150 hover:-translate-y-px hover:bg-red-500 hover:shadow-md active:translate-y-0 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1">
        <span class="sr-only">Delete project</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
            stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M6 7.5h12m-10.5 0 .75 11.25A2.25 2.25 0 0 0 10.5 21h3a2.25 2.25 0 0 0 2.25-2.25L16.5 7.5M9.75 7.5V4.875A1.875 1.875 0 0 1 11.625 3h.75a1.875 1.875 0 0 1 1.875 1.875V7.5M10 11v6m4-6v6" />
        </svg>
    </button>

    <x-dialog-modal :name="$modalName" maxWidth="md" close-method="closeModal">
        <x-slot name="title">Delete project</x-slot>

        <x-slot name="content">
            <p class="text-sm text-gray-600">
                Are you sure you want to delete
                <strong class="font-semibold text-gray-900">{{ $projectName }}</strong>?
            </p>
            <p class="mt-3 text-sm font-medium text-red-600">
                Its associated project data will also be deleted.
            </p>
        </x-slot>

        <x-slot name="footer">
            <div class="flex w-full justify-end gap-3">
                <x-secondary-button wire:click="closeModal" data-no-global-loading wire:loading.attr="disabled"
                    wire:target="deleteProject">
                    Cancel
                </x-secondary-button>

                <x-danger-button wire:click="deleteProject" data-global-loading wire:loading.attr="disabled"
                    wire:target="deleteProject">
                    <span wire:loading.remove wire:target="deleteProject">Delete project</span>
                    <span wire:loading wire:target="deleteProject">Deleting...</span>
                </x-danger-button>
            </div>
        </x-slot>
    </x-dialog-modal>
</div>
