<section class="border-b border-cyan-200 bg-cyan-50 px-5 py-4" wire:poll.60s.visible>
    @php($unreadAssignments = $assignmentNotices->whereNull('read_at')->count())
    <details @if ($unreadAssignments) open @endif>
        <summary class="cursor-pointer font-semibold text-cyan-900">
            {{ __('planification_activities.my_assignments') }} ({{ number_format($assignedActivities->count()) }})
            @if ($unreadAssignments)
                <span class="ml-2 rounded-full bg-cyan-700 px-2 py-1 text-xs text-white">{{ __('planification_activities.unread', ['count' => $unreadAssignments]) }}</span>
            @endif
        </summary>
        <div class="mt-3 max-h-80 space-y-2 overflow-y-auto">
            @forelse ($assignedActivities as $assigned)
                @php($notice = $assignmentNotices->get($assigned->id))
                <button type="button" wire:key="assigned-activity-{{ $assigned->id }}" wire:click="openAssignedActivity({{ $assigned->id }})" data-no-global-loading
                    class="block w-full rounded-lg border border-cyan-200 bg-white p-3 text-left text-sm hover:bg-cyan-100">
                    <span class="block font-bold text-cyan-900">{{ $assigned->project->pda_code }} · {{ $assigned->project->name }}</span>
                    <span class="mt-1 block text-xs text-slate-600">{{ $assigned->periodLabel() }} · {{ $assigned->executed_at ? __('planification_activities.completed') : __('planification_activities.pending') }}</span>
                    <span class="mt-1 block whitespace-pre-line break-words text-slate-800">{{ \Illuminate\Support\Str::limit($assigned->activity, 240) }}</span>
                    <span class="mt-1 block text-xs text-slate-500">{{ __('planification_activities.created_by') }}: {{ $assigned->attribution() }}</span>
                    @if ($notice)<span class="mt-1 block text-xs font-semibold text-cyan-800">{{ __('planification_activities.assigned_by') }}: {{ $notice->data['assigned_by'] }}</span>@endif
                </button>
            @empty
                <p class="py-2 text-sm text-slate-600">{{ __('planification_activities.no_assignments') }}</p>
            @endforelse
        </div>
    </details>
</section>
