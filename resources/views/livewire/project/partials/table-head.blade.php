<thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-700">
    <tr>

        {{-- ID --}}
        <th wire:click="setSortBy('id')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
            <div class="flex items-center gap-1">
                ID

                @if ($sortBy === 'id')
                    <span>
                        {{ $sortDir === 'ASC' ? '↑' : '↓' }}
                    </span>
                @endif
            </div>
        </th>

        {{-- Nombre --}}
        <th wire:click="setSortBy('name')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
            <div class="flex items-center gap-1">
                Name

                @if ($sortBy === 'name')
                    <span>
                        {{ $sortDir === 'ASC' ? '↑' : '↓' }}
                    </span>
                @endif
            </div>
        </th>

        {{-- Enlaces --}}
        <th scope="col" class="whitespace-nowrap px-2 py-2">
            Links
        </th>

        {{-- PDA --}}
        <th wire:click="setSortBy('pda_code')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
            <div class="flex items-center gap-1">
                PDA code

                @if ($sortBy === 'pda_code')
                    <span>
                        {{ $sortDir === 'ASC' ? '↑' : '↓' }}
                    </span>
                @endif
            </div>
        </th>

        {{-- Upload PDA --}}
        <th scope="col" class="whitespace-nowrap px-2 py-2">
            Upload PDA
        </th>

        <th scope="col" class="whitespace-nowrap px-2 py-2">
            Project ideas
        </th>

        <th scope="col" class="whitespace-nowrap px-2 py-2">
            Project Handover Certificate
        </th>

        {{-- Rate --}}
        <th wire:click="setSortBy('rate')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
            <div class="flex items-center gap-1">
                Rate

                @if ($sortBy === 'rate')
                    <span>
                        {{ $sortDir === 'ASC' ? '↑' : '↓' }}
                    </span>
                @endif
            </div>
        </th>

        {{-- Estado --}}
        <th wire:click="setSortBy('state')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
            <div class="flex items-center gap-1">
                State

                @if ($sortBy === 'state')
                    <span>
                        {{ $sortDir === 'ASC' ? '↑' : '↓' }}
                    </span>
                @endif
            </div>
        </th>

        {{-- Inversión --}}
        <th wire:click="setSortBy('investments')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
            <div class="flex items-center gap-1">
                Investments

                @if ($sortBy === 'investments')
                    <span>
                        {{ $sortDir === 'ASC' ? '↑' : '↓' }}
                    </span>
                @endif
            </div>
        </th>

        {{-- Clasificación --}}
        <th wire:click="setSortBy('classification_of_investments')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
            <div class="flex items-center gap-1">
                Classification

                @if ($sortBy === 'classification_of_investments')
                    <span>
                        {{ $sortDir === 'ASC' ? '↑' : '↓' }}
                    </span>
                @endif
            </div>
        </th>

        {{-- Justificación --}}
        <th wire:click="setSortBy('justification')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
            <div class="flex items-center gap-1">
                Justification

                @if ($sortBy === 'justification')
                    <span>
                        {{ $sortDir === 'ASC' ? '↑' : '↓' }}
                    </span>
                @endif
            </div>
        </th>

        {{-- Fecha de inicio --}}
        <th wire:click="setSortBy('forecast_start_date')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
            <div class="flex items-center gap-1">
                Forecast Start Year

                @if ($sortBy === 'forecast_start_date')
                    <span>
                        {{ $sortDir === 'ASC' ? '↑' : '↓' }}
                    </span>
                @endif
            </div>
        </th>

        {{-- Fecha de inicio completa --}}
        <th wire:click="setSortBy('forecast_start_date')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
            <div class="flex items-center gap-1">
                Forecast Start Date

                @if ($sortBy === 'forecast_start_date')
                    <span>{{ $sortDir === 'ASC' ? '↑' : '↓' }}</span>
                @endif
            </div>
        </th>

        {{-- Fecha de finalización --}}
        <th wire:click="setSortBy('forecast_end_date')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
            <div class="flex items-center gap-1">
                Forecast End Date

                @if ($sortBy === 'forecast_end_date')
                    <span>
                        {{ $sortDir === 'ASC' ? '↑' : '↓' }}
                    </span>
                @endif
            </div>
        </th>

        <th wire:click="setSortBy('order')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
            <div class="flex items-center gap-1">
                Order
                @if ($sortBy === 'order')
                    <span>{{ $sortDir === 'ASC' ? '↑' : '↓' }}</span>
                @endif
            </div>
        </th>
        <th scope="col" class="whitespace-nowrap px-2 py-2">Plant</th>
        <th scope="col" class="whitespace-nowrap px-2 py-2">Created By</th>
        <th scope="col" class="whitespace-nowrap px-2 py-2">Responsible</th>

        <th wire:click="setSortBy('data_uploaded')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
            Data Uploaded
        </th>
        <th wire:click="setSortBy('quartile_date')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
            Quartile Date
        </th>
        <th wire:click="setSortBy('approve_date')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
            Approved Date
        </th>
        <th wire:click="setSortBy('close_date')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
            Close Date
        </th>
        <th wire:click="setSortBy('file_name')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
            Document Name
        </th>
        <th wire:click="setSortBy('created_at')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
            Created At
        </th>
        <th wire:click="setSortBy('updated_at')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 hover:bg-gray-200">
            Updated At
        </th>

        <th wire:click="setSortBy('budgeted_euros')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 text-right hover:bg-gray-200">
            Budgeted Euros
        </th>
        <th wire:click="setSortBy('real_euros')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 text-right hover:bg-gray-200">
            Real Euros
        </th>
        <th wire:click="setSortBy('executed_euros')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 text-right hover:bg-gray-200">
            Executed Euros
        </th>
        <th wire:click="setSortBy('booked_euros')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 text-right hover:bg-gray-200">
            Booked Euros
        </th>
        <th wire:click="setSortBy('budgeted_dollars')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 text-right hover:bg-gray-200">
            Budgeted Dollars
        </th>
        <th wire:click="setSortBy('real_dollars')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 text-right hover:bg-gray-200">
            Real Dollars
        </th>

        {{-- Acciones --}}
        <th wire:click="setSortBy('executed_dollars')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 text-right hover:bg-gray-200">
            Executed Dollars
        </th>
        <th wire:click="setSortBy('booked')" scope="col"
            class="cursor-pointer whitespace-nowrap px-2 py-2 text-right hover:bg-gray-200">
            Booked Dollars
        </th>

        <th scope="col" class="whitespace-nowrap px-2 py-2 text-center">
            Actions
        </th>
    </tr>
</thead>
