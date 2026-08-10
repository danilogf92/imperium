<div>

    <button type="button" wire:click="openModal" data-no-global-loading title="Delete project"
        class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-red-600 text-white shadow-sm transition duration-150 hover:-translate-y-px hover:bg-red-500 hover:shadow-md active:translate-y-0 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1">
        <span class="sr-only">Delete project</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M4 7h16M9 7V4.5h6V7M7 7l.75 12h8.5L17 7M10 11v5M14 11v5" />
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
                This deletes the entire project and its associated imported data. It is not the Project ideas file-only
                action.
            </p>
        </x-slot>

        <x-slot name="footer">
            <div class="flex w-full flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-secondary-button wire:click="closeModal" data-no-global-loading wire:loading.attr="disabled"
                    wire:target="deleteProject"
                    style="background-color: #ef4444; border-color: #dc2626; color: #ffffff;"
                    onmouseenter="this.style.backgroundColor='#dc2626'"
                    onmouseleave="this.style.backgroundColor='#ef4444'">
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
