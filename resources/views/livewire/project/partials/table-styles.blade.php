            <style>
                .default-columns-button {
                    background-color: #475569;
                    border: 1px solid #334155;
                    color: #ffffff;
                    transition: transform 150ms ease, background-color 150ms ease, box-shadow 150ms ease;
                }

                .default-columns-button:hover {
                    background-color: #64748b;
                    transform: translateY(-1px);
                    box-shadow: 0 6px 14px rgba(15, 23, 42, 0.2);
                }

                .default-columns-button:active {
                    background-color: #334155;
                    transform: translateY(0);
                }

                .upload-document-cancel {
                    background-color: #475569;
                    border: 1px solid #334155;
                    color: #ffffff;
                    transition: transform 150ms ease, background-color 150ms ease, box-shadow 150ms ease;
                }

                .upload-document-cancel:hover {
                    background-color: #64748b;
                    transform: translateY(-1px);
                    box-shadow: 0 6px 14px rgba(15, 23, 42, 0.18);
                }

                .upload-document-cancel:active {
                    background-color: #334155;
                    transform: translateY(0);
                }

                .project-table>thead>tr>th {
                    min-width: 0;
                    overflow: hidden;
                    padding: 0.3rem 0.3rem !important;
                    text-overflow: ellipsis;
                    font-size: 0.68rem !important;
                    line-height: 0.95rem !important;
                }

                .project-table>tbody>tr:not(.project-empty-row)>th,
                .project-table>tbody>tr:not(.project-empty-row)>td {
                    min-width: 0;
                    overflow: hidden;
                    padding: 0.25rem 0.3rem !important;
                    text-overflow: ellipsis;
                    font-size: 0.75rem !important;
                    line-height: 1rem !important;
                }

                .project-table>tbody>tr:not(.project-empty-row) {
                    min-height: 2.75rem;
                }

                .project-table>tbody>tr:not(.project-empty-row) .rounded-full {
                    padding: 0.2rem 0.5rem !important;
                    font-size: 0.68rem !important;
                    line-height: 0.9rem !important;
                }

                .project-table>tbody>tr:not(.project-empty-row)>td:last-child {
                    overflow: visible;
                }

                .project-table {
                    display: block;
                    min-width: max(100%, {{ max($columnCount, 1) * 116 }}px);
                }

                .project-table>thead,
                .project-table>tbody {
                    display: block;
                }

                .project-table>thead>tr,
                .project-table>tbody>tr:not(.project-empty-row) {
                    display: grid;
                    grid-template-columns: repeat({{ max($columnCount, 1) }}, minmax(116px, 1fr));
                }

                @foreach ($physicalColumns as $physicalIndex => $columnKey)
                    @if (!in_array($columnKey, $visibleColumns, true))
                        .project-table>thead>tr> :nth-child({{ $physicalIndex + 1 }}),
                        .project-table>tbody>tr:not(.project-empty-row)> :nth-child({{ $physicalIndex + 1 }}) {
                            display: none;
                        }
                    @else
                        .project-table>thead>tr> :nth-child({{ $physicalIndex + 1 }}),
                        .project-table>tbody>tr:not(.project-empty-row)> :nth-child({{ $physicalIndex + 1 }}) {
                            order: {{ array_search($columnKey, $columnKeys, true) }};
                        }
                    @endif
                @endforeach
            </style>
