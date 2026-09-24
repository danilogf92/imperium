<x-dialog-modal name="weekly-project-activity" maxWidth="lg" close-method="closeActivityModal" :close-button-red="true">
    <x-slot name="title">
        <div>
            <h2 class="text-lg font-bold text-slate-900">{{ __($notesOnly ? 'notes.title' : 'planification_activities.title') }}</h2>
            <p class="mt-1 text-sm font-normal text-slate-500">
                {{ $activityProjectLabel }}<br>
                @if (! $notesOnly){{ $allProjectActivities ? __('planification_activities.all_periods') : sprintf('%04d-W%02d', $activityWeekYear, $activityWeekNumber) }}@endif
            </p>
        </div>
    </x-slot>

    <x-slot name="content">
        @if ($weekActivities !== [])
            <div class="mb-5 space-y-2">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    {{ count($weekActivities) }} {{ __($notesOnly ? 'notes.title' : 'planification_activities.title') }}
                </p>
                @foreach ($weekActivities as $activity)
                    <div wire:key="weekly-activity-{{ $activity['id'] }}"
                        class="flex flex-wrap items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                        @if (! $notesOnly)<button type="button" @if ($canEditActivity) wire:click="toggleWeeklyActivityExecuted({{ $activity['id'] }})" @else disabled @endif
                            data-no-global-loading
                            title="{{ $canEditActivity ? ($activity['executed'] ? 'Executed — click to change' : 'Not executed — click to mark as executed') : ($activity['executed'] ? 'Executed' : 'Not executed') }}"
                            class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border text-base font-bold
                                {{ $canEditActivity ? 'cursor-pointer' : 'cursor-default' }} {{ $activity['executed'] ? 'border-green-600 bg-green-600 text-white' : ($activity['expired'] ? 'border-red-600 bg-red-600 text-white hover:bg-red-500' : 'border-amber-400 bg-amber-50 text-amber-600 hover:bg-amber-100') }}">
                            {{ $activity['executed'] ? '✓' : ($activity['expired'] ? '×' : '○') }}
                        </button>@endif
                        <div class="min-w-0 flex-1 basis-[60%]">
                            <p class="text-xs text-slate-500">{{ __('planification_activities.created_by') }}: {{ $activity['attribution'] }}</p>
                            @if (! $notesOnly)<p class="mt-1 text-xs text-slate-500">{{ $activity['period'] }} · {{ __('planification_activities.assigned_to') }}: {{ $activity['assignee'] ?? __('planification_activities.unassigned') }}</p>@endif
                            <p class="mt-1 whitespace-pre-line break-words text-sm text-slate-700">{{ $activity['activity'] }}</p>
                        </div>
                        @if ($canEditActivity)
                            <button type="button" wire:click="editWeeklyActivity({{ $activity['id'] }})"
                            data-no-global-loading title="Edit activity"
                            class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-blue-600 text-white hover:bg-blue-500">
                            ✎
                            </button>
                        @endif
                        @if ($canDeleteActivity)
                            <button type="button" wire:click="requestDeleteWeeklyActivity({{ $activity['id'] }})"
                            data-no-global-loading title="Delete activity"
                            class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-red-600 text-white hover:bg-red-500">
                            ×
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if ($canEditActivity)
        @if (! $notesOnly)<div class="mb-4 grid gap-4 sm:grid-cols-2">
            <label class="block text-sm font-semibold text-slate-700">
                {{ __('planification_activities.period') }}
                <input type="week" wire:model="activityPeriod" min="2000-W01" max="2099-W53" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                <span class="mt-1 block text-xs font-normal text-slate-500">{{ __('planification_activities.period_hint') }}</span>
                @error('activityPeriod')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
            </label>
            <label class="block text-sm font-semibold text-slate-700">
                {{ __('planification_activities.assigned_to') }}
                <select wire:model="activityAssigneeId" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">
                    <option value="">{{ __('planification_activities.unassigned') }}</option>
                    @foreach ($assigneeOptions as $assignee)<option value="{{ $assignee->id }}">{{ $assignee->name }}</option>@endforeach
                </select>
                <span class="mt-1 block text-xs font-normal text-slate-500">{{ __('planification_activities.assignee_hint') }}</span>
                @error('activityAssigneeId')<span class="text-sm text-red-600">{{ $message }}</span>@enderror
            </label>
        </div>
        @endif
        <label class="block">
            <span class="mb-2 block text-sm font-semibold text-slate-700">
                {{ $notesOnly ? __($activityEditingId ? 'notes.edit' : 'notes.add') : ($activityEditingId ? 'Edit activity' : 'Add another activity') }}
            </span>
            <textarea wire:model="weeklyActivity" rows="7" maxlength="5000"
                placeholder="{{ $notesOnly ? __('notes.placeholder') : 'Describe the activity planned for this project and week...' }}"
                class="block w-full resize-y rounded-xl border-slate-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-cyan-500 focus:ring-cyan-500"></textarea>
            <div class="mt-1.5 flex justify-between gap-3 text-xs text-slate-500">
                <span>Up to 5,000 characters.</span>
                <span>{{ mb_strlen($weeklyActivity) }}/5000</span>
            </div>
            @error('weeklyActivity')
                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
            @enderror
        </label>
        @endif

    </x-slot>

    <x-slot name="footer">
        <div class="flex w-full flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button type="button" wire:click="closeActivityModal" data-no-global-loading
                class="inline-flex h-10 cursor-pointer items-center rounded-lg bg-red-500 px-4 text-sm font-semibold text-white hover:bg-red-600">
                Cancel
            </button>
            @if ($canEditActivity)
            @if ($activityEditingId)<button type="button" wire:click="cancelEditWeeklyActivity" data-no-global-loading class="rounded-lg border border-slate-300 px-4 text-sm font-semibold">{{ __('planification_activities.cancel_edit') }}</button>@endif
            <button type="button" wire:click="saveWeeklyActivity" data-no-global-loading
                wire:loading.attr="disabled" wire:target="saveWeeklyActivity"
                class="inline-flex h-10 cursor-pointer items-center rounded-lg bg-cyan-600 px-4 text-sm font-semibold text-white hover:bg-cyan-700 disabled:cursor-wait disabled:opacity-60">
                <span wire:loading.remove wire:target="saveWeeklyActivity">{{ $notesOnly ? __('notes.save') : ($activityEditingId ? 'Update activity' : 'Add activity') }}</span>
                <span wire:loading wire:target="saveWeeklyActivity">Saving...</span>
            </button>
            @endif
        </div>
    </x-slot>
</x-dialog-modal>
