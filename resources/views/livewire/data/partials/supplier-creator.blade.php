@if ($showSupplierCreator)
    <div data-modal-open="true" class="app-modal fixed inset-0 z-[190] flex items-center justify-center p-4" role="dialog"
        aria-modal="true" aria-labelledby="new-supplier-title">
        <button type="button" wire:click="toggleSupplierCreator" data-no-global-loading aria-label="Close"
            class="absolute inset-0 bg-slate-950/50"></button>
        <div class="relative z-10 max-h-[calc(100dvh-2rem)] w-full max-w-lg overflow-y-auto overscroll-contain rounded-lg bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <h3 id="new-supplier-title" class="text-base font-semibold text-slate-900">Create supplier</h3>
                <button type="button" wire:click="toggleSupplierCreator" data-no-global-loading aria-label="Close"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md text-xl text-red-600 hover:bg-red-50">&times;</button>
            </div>
            <div class="space-y-5 px-5 py-5">
                <p class="text-sm text-slate-600">{{ $project->company->company_name }}</p>
                <label class="block text-sm font-medium text-gray-700" for="new-supplier-name">Name <span class="text-red-500">*</span></label>
                <input id="new-supplier-name" type="text" wire:model="newSupplierName" maxlength="255"
                    placeholder="Supplier name" x-init="$nextTick(() => $el.focus())"
                    wire:keydown.enter.prevent="createSupplier"
                    class="block w-full rounded-lg border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('newSupplierName')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex flex-wrap justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" wire:click="toggleSupplierCreator" data-no-global-loading
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="button" wire:click="createSupplier" wire:loading.attr="disabled" wire:target="createSupplier" data-no-global-loading
                    class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                    <span wire:loading.remove wire:target="createSupplier">Create</span>
                    <span wire:loading wire:target="createSupplier">Creating...</span>
                </button>
            </div>
        </div>
    </div>
@endif
