@once
    <style>
        :root {
            /*
                 |--------------------------------------------------------------------------
                 | Unified table theme
                 |--------------------------------------------------------------------------
                 | Cambia los colores globales de las tablas únicamente aquí.
                 */
            --unified-table-row-bg: #ffffff;
            --unified-table-row-even-bg: #f1f5f9;
            --unified-table-row-hover: #dbeafe;
            --unified-table-hover-border: #3b82f6;

            --unified-table-border: #e2e8f0;
            --unified-table-cell-border: #eef2f7;
            --unified-table-header-bg: #f8fafc;
            --unified-table-header-text: #334155;
            --unified-table-text: #475569;

            --unified-table-header-hover-bg: #dbeafe;
            --unified-table-header-hover-text: #1d4ed8;
        }

        .unified-table-shell {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            overflow: visible;
            border: 1px solid var(--unified-table-border);
            border-radius: 0.75rem;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.07);
        }

        .unified-table-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--unified-table-border);
            border-radius: 0.75rem 0.75rem 0 0;
            background: #ffffff;
        }

        .unified-table-scroll {
            display: block;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            overflow-x: auto;
            overscroll-behavior-x: contain;
            border-radius: 0 0 0.75rem 0.75rem;
        }

        .unified-data-table {
            width: max-content;
            min-width: 100%;
            max-width: none;
            border-collapse: separate;
            border-spacing: 0;
            color: var(--unified-table-text);
            font-size: 0.8125rem;
            line-height: 1.25rem;
        }

        .unified-data-table thead {
            background: var(--unified-table-header-bg);
            color: var(--unified-table-header-text);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.045em;
            text-transform: uppercase;
        }

        .unified-data-table thead th {
            padding: 0.75rem 0.875rem;
            border-bottom: 1px solid #cbd5e1;
            text-align: left;
            vertical-align: middle;
        }

        .unified-data-table thead th.text-center {
            text-align: center;
        }

        .unified-data-table thead th.text-right {
            text-align: right;
        }

        .unified-data-table thead th[wire\:click] {
            transition:
                background-color 150ms ease,
                color 150ms ease;
        }

        .unified-data-table thead th[wire\:click]:hover {
            background: var(--unified-table-header-hover-bg);
            color: var(--unified-table-header-hover-text);
        }

        .unified-data-table tbody tr {
            background-color: var(--unified-table-row-bg);
            transition:
                background-color 140ms ease,
                box-shadow 140ms ease;
        }

        .unified-data-table tbody tr:nth-child(even) {
            background-color: var(--unified-table-row-even-bg);
        }

        .unified-data-table tbody tr:not(.unified-empty-row):hover {
            background-color: var(--unified-table-row-hover) !important;
            box-shadow: inset 3px 0 0 var(--unified-table-hover-border);
        }

        .unified-data-table tbody td,
        .unified-data-table tbody th {
            padding: 0.65rem 0.875rem;
            border-bottom: 1px solid var(--unified-table-cell-border);
            vertical-align: middle;
        }

        .unified-data-table tbody tr:last-child td,
        .unified-data-table tbody tr:last-child th {
            border-bottom: 0;
        }

        /*
             |--------------------------------------------------------------------------
             | Sticky cells
             |--------------------------------------------------------------------------
             | Las celdas sticky necesitan su propio background porque, por estar
             | posicionadas, pueden ocultar el background aplicado directamente al <tr>.
             */
        .unified-data-table tbody tr .planification-sticky-cell {
            background-color: var(--unified-table-row-bg);
            transition: background-color 140ms ease;
        }

        .unified-data-table tbody tr:nth-child(even) .planification-sticky-cell {
            background-color: var(--unified-table-row-even-bg);
        }

        .unified-data-table tbody tr:not(.unified-empty-row):hover .planification-sticky-cell {
            background-color: var(--unified-table-row-hover) !important;
        }

        .unified-empty-row td {
            padding: 4rem 1.5rem !important;
            color: #64748b;
            text-align: center;
        }

        .unified-table-pagination {
            padding: 0.85rem 1rem;
            border-top: 1px solid var(--unified-table-border);
            border-radius: 0 0 0.75rem 0.75rem;
            background: #ffffff;
        }
    </style>
@endonce
