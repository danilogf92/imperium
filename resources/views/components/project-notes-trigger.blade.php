@props(['project', 'context' => 'name'])
<div class="shrink-0" x-data="{
    tooltipOpen: false, tooltipStyle: '', hideTimer: null,
    hideTooltip() { this.hideTimer = setTimeout(() => { this.tooltipOpen = false; }, 150); },
    showTooltip() {
        clearTimeout(this.hideTimer);
        const rect = this.$refs.trigger.getBoundingClientRect();
        const width = Math.min(360, window.innerWidth - 24);
        const above = rect.top > window.innerHeight / 2;
        const available = Math.max(80, (above ? rect.top : window.innerHeight - rect.bottom) - 20);
        this.tooltipStyle = `position:fixed; width:${width}px; max-height:${Math.min(320, available)}px; overflow:auto; pointer-events:auto; left:${Math.max(12, Math.min(rect.left, window.innerWidth - width - 12))}px; ${above ? `bottom:${window.innerHeight - rect.top + 8}px` : `top:${rect.bottom + 8}px`}`;
        this.tooltipOpen = true;
    }
}" @keydown.escape.window="tooltipOpen = false" @resize.window="tooltipOpen = false" @click.outside="tooltipOpen = false">
    <button x-ref="trigger" type="button" wire:click.stop="openProjectNotes({{ $project->id }})" @click="tooltipOpen = false" data-no-global-loading
        @if (! $project->planification_notes_count) title="{{ __('notes.open') }}" @endif
        @if ($project->planification_notes_count) @mouseenter="showTooltip()" @mouseleave="hideTooltip()" @focus="showTooltip()" @blur="hideTooltip()" aria-describedby="notes-tooltip-{{ $project->id }}-{{ $context }}" @endif
        class="inline-flex h-7 items-center justify-center gap-1 rounded-md bg-cyan-100 px-1.5 text-xs font-semibold text-cyan-800 hover:bg-cyan-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-cyan-600"
        aria-label="{{ __('notes.open') }}: {{ $project->pda_code }} · {{ $project->name }}">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 3h14v18H5zM8 8h8M8 12h8M8 16h5" /></svg>
        @if ($project->planification_notes_count)
            <span>
                {{ number_format($project->planification_notes_count) }}
            </span>
        @endif
    </button>
    @if ($project->planification_notes_count)
        <template x-teleport="body">
            <div x-cloak x-show="tooltipOpen" x-transition.opacity id="notes-tooltip-{{ $project->id }}-{{ $context }}" role="tooltip" @mouseenter="clearTimeout(hideTimer)" @mouseleave="hideTooltip()" :style="tooltipStyle" class="planification-activity-tooltip">
                <p class="border-b border-cyan-400/20 pb-2 font-bold text-cyan-100">{{ __('notes.title') }} ({{ number_format($project->planification_notes_count) }})</p>
                <ol class="mt-2 space-y-3">
                    @foreach ($project->planificationNotes as $note)
                        <li><p class="text-xs text-cyan-100">{{ __('planification_activities.created_by') }}: {{ $note->attribution() }}</p><p class="mt-1 whitespace-pre-line break-words text-slate-100">{{ \Illuminate\Support\Str::limit($note->activity, 200) }}</p></li>
                    @endforeach
                </ol>
                <p class="mt-3 border-t border-cyan-400/20 pt-2 text-xs text-cyan-100">{{ __('notes.open') }}</p>
            </div>
        </template>
    @endif
</div>
