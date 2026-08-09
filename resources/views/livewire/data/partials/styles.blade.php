<style>
        .data-action-button {
            transition: transform 150ms ease, box-shadow 150ms ease, filter 150ms ease;
        }

        .data-action-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 7px 16px rgba(15, 23, 42, 0.16);
            filter: brightness(1.06);
        }

        .data-default-columns {
            background-color: #475569;
            border: 1px solid #334155;
            color: #fff;
        }

        .data-default-columns:hover {
            background-color: #64748b;
        }

        .data-back-to-projects {
            background-color: #eab308;
            border-color: #ca8a04;
            color: #422006;
        }

        .data-back-to-projects:hover {
            background-color: #facc15;
            border-color: #eab308;
            color: #422006;
        }

        .data-back-to-projects:active {
            background-color: #ca8a04;
        }

        .data-orders-disabled {
            cursor: not-allowed;
            opacity: 0.55;
        }

        .data-orders-disabled:hover {
            background-color: #eab308;
            border-color: #ca8a04;
            box-shadow: none;
            filter: none;
            transform: none;
        }

        .data-orders-tooltip-wrapper {
            position: relative;
        }

        .data-orders-tooltip {
            position: absolute;
            bottom: calc(100% + 10px);
            left: 50%;
            z-index: 40;
            width: max-content;
            max-width: 220px;
            padding: 8px 11px;
            border-radius: 8px;
            background-color: #0f172a;
            color: #f8fafc;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.35;
            text-align: center;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.24);
            opacity: 0;
            pointer-events: none;
            transform: translate(-50%, 5px);
            transition: opacity 150ms ease, transform 150ms ease;
        }

        .data-orders-tooltip::after {
            position: absolute;
            top: 100%;
            left: 50%;
            width: 8px;
            height: 8px;
            background-color: #0f172a;
            content: '';
            transform: translate(-50%, -4px) rotate(45deg);
        }

        .data-orders-tooltip-wrapper:hover .data-orders-tooltip,
        .data-orders-tooltip-wrapper:focus-within .data-orders-tooltip {
            opacity: 1;
            transform: translate(-50%, 0);
        }

        .data-modal-cancel {
            background-color: #475569;
            border: 1px solid #334155;
            color: #fff;
            transition: transform 150ms ease, background-color 150ms ease, box-shadow 150ms ease;
        }

        .data-modal-cancel:hover {
            background-color: #64748b;
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.18);
        }

        .data-edit-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            column-gap: 1rem;
            row-gap: 0.8rem;
        }

        .data-edit-grid-wide {
            grid-column: 1 / -1;
        }

        @media (max-width: 1023px) {
            .data-edit-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 639px) {
            .data-edit-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }
    </style>
