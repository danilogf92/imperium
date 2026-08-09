<x-dialog-modal name="weekly-project-activity" maxWidth="lg" close-method="closeActivityModal">
    <x-slot name="title">
        <div>
            <h2 class="text-lg font-bold text-slate-900">Weekly project activity</h2>
            <p class="mt-1 text-sm font-normal text-slate-500">
                Week {{ str_pad($activityWeekNumber, 2, '0', STR_PAD_LEFT) }} · {{ $activityWeekYear }}
            </p>
        </div>
    </x-slot>

    <x-slot name="content">
        @if ($weekActivities !== [])
            <div class="mb-5 space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    {{ count($weekActivities) }} saved activities
                </p>
                @foreach ($weekActivities as $activity)
                    <div wire:key="weekly-activity-{{ $activity['id'] }}"
                        class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                        <p class="min-w-0 flex-1 whitespace-pre-line text-sm text-slate-700">{{ $activity['activity'] }}</p>
                        <button type="button" wire:click="editWeeklyActivity({{ $activity['id'] }})"
                            data-no-global-loading title="Edit activity"
                            class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-blue-600 text-white hover:bg-blue-500">
                            ✎
                        </button>
                        <button type="button" wire:click="deleteWeeklyActivity({{ $activity['id'] }})"
                            wire:confirm="Delete this weekly activity?" data-no-global-loading title="Delete activity"
                            class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-red-600 text-white hover:bg-red-500">
                            ×
                        </button>
                    </div>
                @endforeach
            </div>
        @endif

        <label class="block">
            <span class="mb-2 block text-sm font-semibold text-slate-700">
                {{ $activityEditingId ? 'Edit activity' : 'Add another activity' }}
            </span>
            <textarea wire:model="weeklyActivity" rows="7" maxlength="5000"
                placeholder="Describe the activity planned for this project and week..."
                class="block w-full resize-y rounded-xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-cyan-500 focus:ring-cyan-500"></textarea>
            <div class="mt-1.5 flex justify-between gap-3 text-xs text-slate-500">
                <span>Up to 5,000 characters.</span>
                <span>{{ mb_strlen($weeklyActivity) }}/5000</span>
            </div>
            @error('weeklyActivity')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </label>
    </x-slot>

    <x-slot name="footer">
        <div class="flex w-full justify-end gap-3">
            <button type="button" wire:click="closeActivityModal" data-no-global-loading
                class="inline-flex h-10 cursor-pointer items-center rounded-lg bg-red-500 px-4 text-sm font-semibold text-white hover:bg-red-600">
                Cancel
            </button>
            <button type="button" wire:click="saveWeeklyActivity" data-no-global-loading
                wire:loading.attr="disabled" wire:target="saveWeeklyActivity"
                class="inline-flex h-10 cursor-pointer items-center rounded-lg bg-cyan-600 px-4 text-sm font-semibold text-white hover:bg-cyan-700 disabled:cursor-wait disabled:opacity-60">
                <span wire:loading.remove wire:target="saveWeeklyActivity">{{ $activityEditingId ? 'Update activity' : 'Add activity' }}</span>
                <span wire:loading wire:target="saveWeeklyActivity">Saving...</span>
            </button>
        </div>
    </x-slot>
</x-dialog-modal>
