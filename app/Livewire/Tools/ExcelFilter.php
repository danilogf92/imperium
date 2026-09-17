<?php

namespace App\Livewire\Tools;

use App\Services\Tools\ExcelFilterService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

final class ExcelFilter extends Component
{
    use WithFileUploads;

    public mixed $upload = null;
    public string $sourceToken = '';
    public string $sourceName = '';
    public array $headers = [];
    public array $sampleRows = [];
    public array $selectedColumns = [];
    public array $columnTypes = [];
    public string $filterColumn = '';
    public string $projectCode = '';
    public bool $previewReady = false;
    public array $previewHeaders = [];
    public array $previewRows = [];
    public int $matchCount = 0;
    public int $previewPage = 1;
    public int $lastPage = 1;

    public function mount(): void
    {
        abort_unless(auth()->check(), 403);
    }

    public function analyze(ExcelFilterService $excel): void
    {
        $this->validate(['upload' => ['required', 'file', 'extensions:xlsx,xls', 'mimes:xlsx,xls', 'max:12288']], [
            'upload.required' => 'Select an Excel workbook first.',
            'upload.extensions' => 'Only .xlsx and .xls files are accepted.',
            'upload.mimes' => 'The selected file is not a valid Excel workbook.',
            'upload.max' => 'The workbook may not be larger than 12 MB.',
        ]);

        $newToken = '';
        try {
            $newToken = $excel->store($this->upload, (int) auth()->id());
            $analysis = $excel->analyze((int) auth()->id(), $newToken);
        } catch (ValidationException $exception) {
            if ($newToken !== '') {
                $excel->removeSource((int) auth()->id(), $newToken);
            }
            throw $exception;
        } catch (Throwable $exception) {
            if ($newToken !== '') {
                $excel->removeSource((int) auth()->id(), $newToken);
            }
            report($exception);
            $this->addError('upload', 'The workbook could not be read. Check that it is a valid one-sheet Excel file.');
            return;
        }

        if ($this->sourceToken !== '') {
            $excel->removeSource((int) auth()->id(), $this->sourceToken);
            session()->forget($this->previewSessionKey());
        }
        $this->sourceToken = $newToken;
        $this->sourceName = $this->upload->getClientOriginalName();
        $this->headers = $analysis['headers'];
        $this->sampleRows = $analysis['sampleRows'];
        $this->selectedColumns = array_keys($this->headers);
        $this->columnTypes = array_fill(0, count($this->headers), 'text');
        $this->filterColumn = '';
        $this->projectCode = '';
        $this->upload = null;
        $this->clearPreview();
        $this->resetValidation();
    }

    public function selectAll(): void
    {
        $this->selectedColumns = array_keys($this->headers);
    }

    public function selectNone(): void
    {
        $this->selectedColumns = [];
    }

    public function preview(ExcelFilterService $excel): void
    {
        $this->validate([
            'sourceToken' => ['required', 'uuid'],
            'selectedColumns' => ['required', 'array', 'min:1'],
            'filterColumn' => ['required', 'integer', 'between:0,'.max(0, count($this->headers) - 1)],
            'projectCode' => ['required', 'string', 'max:150'],
        ], [
            'selectedColumns.required' => 'Select at least one column.',
            'selectedColumns.min' => 'Select at least one column.',
            'filterColumn.required' => 'Choose the column that contains the project code.',
            'projectCode.required' => 'Enter the project code.',
        ]);
        $this->projectCode = trim($this->projectCode);
        if ($this->projectCode === '') {
            throw ValidationException::withMessages(['projectCode' => 'Enter the project code.']);
        }

        $this->previewPage = 1;
        $result = $excel->preview((int) auth()->id(), $this->sourceToken, $this->selectedColumns, (int) $this->filterColumn, $this->projectCode, 1, columnTypes: $this->columnTypes);
        $this->setPreview($result);
        session()->put($this->previewSessionKey(), [
            'selectedColumns' => $this->selectedColumns,
            'columnTypes' => $this->columnTypes,
            'filterColumn' => (int) $this->filterColumn,
            'projectCode' => $this->projectCode,
            'count' => $this->matchCount,
        ]);
        $this->resetValidation();
    }

    public function modify(): void
    {
        session()->forget($this->previewSessionKey());
        $this->clearPreview();
    }

    public function goToPage(int $page, ExcelFilterService $excel): void
    {
        $configuration = session()->get($this->previewSessionKey());
        abort_unless($this->previewReady && is_array($configuration), 403);
        $page = max(1, min($page, $this->lastPage));
        $this->setPreview($excel->preview(
            (int) auth()->id(), $this->sourceToken,
            $configuration['selectedColumns'], $configuration['filterColumn'], $configuration['projectCode'], $page,
            columnTypes: $configuration['columnTypes']
        ));
    }

    #[Renderless]
    public function download(ExcelFilterService $excel): BinaryFileResponse
    {
        $configuration = session()->get($this->previewSessionKey());
        abort_unless($this->previewReady && is_array($configuration) && ($configuration['count'] ?? 0) > 0, 403);
        $path = $excel->export(
            (int) auth()->id(), $this->sourceToken,
            $configuration['selectedColumns'], $configuration['filterColumn'], $configuration['projectCode'],
            $configuration['columnTypes']
        );
        $code = Str::slug($configuration['projectCode']) ?: 'project';

        return response()->download($path, "filtered-{$code}.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function render(): View
    {
        return view('livewire.tools.excel-filter')->layout('layouts.app');
    }

    private function setPreview(array $result): void
    {
        $this->previewHeaders = $result['headers'];
        $this->previewRows = $result['rows'];
        $this->matchCount = $result['count'];
        $this->previewPage = $result['page'];
        $this->lastPage = $result['lastPage'];
        $this->previewReady = true;
    }

    private function clearPreview(): void
    {
        $this->previewReady = false;
        $this->previewHeaders = [];
        $this->previewRows = [];
        $this->matchCount = 0;
        $this->previewPage = 1;
        $this->lastPage = 1;
    }

    private function previewSessionKey(): string
    {
        return 'tools.excel-filter.preview.'.auth()->id().'.'.$this->sourceToken;
    }
}
