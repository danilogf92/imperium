@php
    $oldValues = $record->old_values ?? [];
    $newValues = $record->new_values ?? [];
    $fields = collect(array_keys($oldValues))
        ->merge(array_keys($newValues))
        ->unique()
        ->sort()
        ->values();

    $event = match ($record->event) {
        'created' => [
            'label' => 'Record created',
            'description' => 'A new record was added to the system.',
            'color' => '#059669',
            'soft' => '#ecfdf5',
            'icon' => 'plus',
        ],
        'updated' => [
            'label' => 'Record updated',
            'description' => 'One or more values were changed.',
            'color' => '#d97706',
            'soft' => '#fffbeb',
            'icon' => 'pencil',
        ],
        'deleted' => [
            'label' => 'Record deleted',
            'description' => 'The record was removed from the system.',
            'color' => '#dc2626',
            'soft' => '#fef2f2',
            'icon' => 'trash',
        ],
        default => [
            'label' => str($record->event)->headline(),
            'description' => 'An activity was registered.',
            'color' => '#475569',
            'soft' => '#f8fafc',
            'icon' => 'activity',
        ],
    };

    $formatValue = static function (mixed $value, bool $exists): string {
        if (! $exists) {
            return 'Not available';
        }

        if ($value === null) {
            return 'Empty';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return json_encode(
                $value,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            );
        }

        if ($value === '') {
            return 'Empty';
        }

        return (string) $value;
    };
@endphp

<style>
    .audit-details {
        color: rgb(15 23 42);
    }

    .audit-details__hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.125rem;
        border: 1px solid color-mix(in srgb, {{ $event['color'] }} 24%, transparent);
        border-left: 4px solid {{ $event['color'] }};
        border-radius: .875rem;
        background: {{ $event['soft'] }};
    }

    .audit-details__event {
        display: flex;
        align-items: center;
        gap: .75rem;
    }

    .audit-details__event-icon {
        display: inline-flex;
        width: 2.5rem;
        height: 2.5rem;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        border-radius: .75rem;
        color: white;
        background: {{ $event['color'] }};
        box-shadow: 0 5px 12px color-mix(in srgb, {{ $event['color'] }} 22%, transparent);
    }

    .audit-details__title {
        margin: 0;
        font-size: .95rem;
        font-weight: 700;
        color: {{ $event['color'] }};
    }

    .audit-details__subtitle {
        margin: .15rem 0 0;
        font-size: .78rem;
        color: rgb(100 116 139);
    }

    .audit-details__record {
        flex: 0 0 auto;
        border-radius: 999px;
        padding: .35rem .7rem;
        background: rgba(255, 255, 255, .8);
        font-size: .72rem;
        font-weight: 700;
        color: rgb(71 85 105);
        box-shadow: inset 0 0 0 1px rgba(148, 163, 184, .25);
    }

    .audit-details__meta {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .65rem;
        margin-top: .8rem;
    }

    .audit-details__meta-item {
        min-width: 0;
        padding: .75rem .85rem;
        border: 1px solid rgb(226 232 240);
        border-radius: .75rem;
        background: white;
    }

    .audit-details__meta-label {
        display: block;
        margin-bottom: .25rem;
        font-size: .65rem;
        font-weight: 700;
        letter-spacing: .055em;
        text-transform: uppercase;
        color: rgb(148 163 184);
    }

    .audit-details__meta-value {
        display: block;
        overflow: hidden;
        font-size: .78rem;
        font-weight: 600;
        color: rgb(51 65 85);
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .audit-details__section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin: 1rem 0 .55rem;
    }

    .audit-details__section-title {
        font-size: .82rem;
        font-weight: 700;
        color: rgb(51 65 85);
    }

    .audit-details__count {
        border-radius: 999px;
        padding: .2rem .55rem;
        background: rgb(241 245 249);
        font-size: .68rem;
        font-weight: 700;
        color: rgb(100 116 139);
    }

    .audit-details__changes {
        overflow: hidden;
        border: 1px solid rgb(226 232 240);
        border-radius: .875rem;
        background: white;
    }

    .audit-details__change {
        display: grid;
        grid-template-columns: 11rem minmax(0, 1fr) 2rem minmax(0, 1fr);
        align-items: stretch;
        min-height: 4rem;
    }

    .audit-details__change + .audit-details__change {
        border-top: 1px solid rgb(241 245 249);
    }

    .audit-details__field {
        display: flex;
        align-items: center;
        padding: .75rem 1rem;
        background: rgb(248 250 252);
        font-size: .72rem;
        font-weight: 700;
        color: rgb(71 85 105);
    }

    .audit-details__value {
        display: flex;
        align-items: center;
        min-width: 0;
        padding: .7rem .9rem;
    }

    .audit-details__value pre {
        width: 100%;
        margin: 0;
        overflow-wrap: anywhere;
        white-space: pre-wrap;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: .72rem;
        line-height: 1.45;
        color: rgb(51 65 85);
    }

    .audit-details__value--new {
        background: color-mix(in srgb, {{ $event['soft'] }} 55%, white);
    }

    .audit-details__arrow {
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgb(148 163 184);
    }

    .audit-details__empty {
        padding: 1.5rem;
        border: 1px dashed rgb(203 213 225);
        border-radius: .875rem;
        text-align: center;
        font-size: .78rem;
        color: rgb(100 116 139);
        background: rgb(248 250 252);
    }

    .audit-details__technical {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem 1rem;
        margin-top: .75rem;
        padding: 0 .2rem;
        font-size: .67rem;
        color: rgb(148 163 184);
    }

    @media (max-width: 850px) {
        .audit-details__meta {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .audit-details__change {
            grid-template-columns: 1fr;
        }

        .audit-details__field {
            border-bottom: 1px solid rgb(241 245 249);
        }

        .audit-details__arrow {
            height: 1.5rem;
            transform: rotate(90deg);
        }
    }

    .dark .audit-details {
        color: rgb(226 232 240);
    }

    .dark .audit-details__hero {
        background: color-mix(in srgb, {{ $event['color'] }} 10%, rgb(17 24 39));
    }

    .dark .audit-details__record,
    .dark .audit-details__meta-item,
    .dark .audit-details__changes {
        border-color: rgb(55 65 81);
        background: rgb(17 24 39);
    }

    .dark .audit-details__meta-value,
    .dark .audit-details__section-title,
    .dark .audit-details__field,
    .dark .audit-details__value pre {
        color: rgb(226 232 240);
    }

    .dark .audit-details__field,
    .dark .audit-details__count,
    .dark .audit-details__empty {
        background: rgb(31 41 55);
    }

    .dark .audit-details__change + .audit-details__change,
    .dark .audit-details__field {
        border-color: rgb(55 65 81);
    }

    .dark .audit-details__value--new {
        background: color-mix(in srgb, {{ $event['color'] }} 8%, rgb(17 24 39));
    }
</style>

<div class="audit-details">
    <div class="audit-details__hero">
        <div class="audit-details__event">
            <span class="audit-details__event-icon">
                @if ($event['icon'] === 'plus')
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                    </svg>
                @elseif ($event['icon'] === 'pencil')
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.86 3.49 3.65 3.65M4 20l4.15-.83L19.9 7.42a1.75 1.75 0 0 0 0-2.47l-.85-.85a1.75 1.75 0 0 0-2.47 0L4.83 15.85 4 20Z" />
                    </svg>
                @else
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" d="M4 7h16M9 7V4h6v3m-9 0 1 13h10l1-13M10 11v5m4-5v5" />
                    </svg>
                @endif
            </span>
            <div>
                <p class="audit-details__title">{{ $event['label'] }}</p>
                <p class="audit-details__subtitle">{{ $event['description'] }}</p>
            </div>
        </div>
        <span class="audit-details__record">
            {{ str(class_basename($record->auditable_type))->headline() }} #{{ $record->auditable_id }}
        </span>
    </div>

    <div class="audit-details__meta">
        <div class="audit-details__meta-item">
            <span class="audit-details__meta-label">Performed by</span>
            <span class="audit-details__meta-value" title="{{ $record->user?->email }}">
                {{ $record->user?->name ?? 'System' }}
            </span>
        </div>
        <div class="audit-details__meta-item">
            <span class="audit-details__meta-label">Date and time</span>
            <span class="audit-details__meta-value">
                {{ $record->created_at?->format('M d, Y · H:i:s') }}
            </span>
        </div>
        <div class="audit-details__meta-item">
            <span class="audit-details__meta-label">Company</span>
            <span class="audit-details__meta-value">
                {{ $record->company?->company_name ?? 'Global' }}
            </span>
        </div>
        <div class="audit-details__meta-item">
            <span class="audit-details__meta-label">Project</span>
            <span class="audit-details__meta-value" title="{{ $record->project?->name }}">
                {{ $record->project?->name ?? 'Not related' }}
            </span>
        </div>
    </div>

    <div class="audit-details__section-header">
        <span class="audit-details__section-title">Changed information</span>
        <span class="audit-details__count">
            {{ $fields->count() }} {{ str('field')->plural($fields->count()) }}
        </span>
    </div>

    @if ($fields->isEmpty())
        <div class="audit-details__empty">No field values were recorded for this event.</div>
    @else
        <div class="audit-details__changes">
            @foreach ($fields as $field)
                @php
                    $hasBefore = array_key_exists($field, $oldValues);
                    $hasAfter = array_key_exists($field, $newValues);
                @endphp
                <div class="audit-details__change">
                    <div class="audit-details__field">
                        {{ str($field)->replace('_', ' ')->headline() }}
                    </div>
                    <div class="audit-details__value">
                        <pre>{{ $formatValue($oldValues[$field] ?? null, $hasBefore) }}</pre>
                    </div>
                    <div class="audit-details__arrow">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                        </svg>
                    </div>
                    <div class="audit-details__value audit-details__value--new">
                        <pre>{{ $formatValue($newValues[$field] ?? null, $hasAfter) }}</pre>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="audit-details__technical">
        <span>IP: {{ $record->ip_address ?? 'Not available' }}</span>
        <span title="{{ $record->user_agent }}">
            Browser: {{ $record->user_agent ? str($record->user_agent)->limit(90) : 'Not available' }}
        </span>
    </div>
</div>
