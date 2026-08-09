<?php

namespace App\Livewire\Orders;

use App\Enums\ProjectPermissionEnum;
use App\Models\Data;
use App\Models\ExcelTemplate;
use App\Models\Project;
use App\Livewire\Concerns\InteractsWithPerPagePreference;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Ordenes extends Component
{
    use InteractsWithPerPagePreference;
    use WithPagination;

    public ?Project $project = null;

    #[Url]
    public string $search = '';

    #[Url]
    public int $perPage = 10;

    #[Url]
    public string $sortDir = 'asc';

    #[Url(as: 'plant', except: [])]
    public array $plantFilter = [];

    #[Url(as: 'year', except: [])]
    public array $yearFilter = [];

    #[Url(as: 'order', except: [])]
    public array $orderFilter = [];

    public function mount(?Project $project = null): void
    {
        $user = auth()->user();
        abort_unless($user, 403);
        $this->loadPerPagePreference();

        if ($project?->exists) {
            $this->project = Project::query()
                ->whereIn(
                    'company_id',
                    $user->companiesForPermissionQuery(ProjectPermissionEnum::View)
                        ->select('companies.id')
                        ->reorder()
                )
                ->findOrFail($project->getKey());
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->savePerPagePreference($this->perPage);
        $this->resetPage();
    }

    public function updatedPlantFilter(): void { $this->resetPage(); }
    public function updatedYearFilter(): void { $this->resetPage(); }
    public function updatedOrderFilter(): void { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->reset(['search', 'plantFilter', 'yearFilter', 'orderFilter']);
        $this->resetPage();
    }

    public function toggleSort(): void
    {
        $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        $this->resetPage();
    }

    public function downloadOrder(
        int $projectId,
        string $orderNumber,
        int $orderYear
    ): BinaryFileResponse {
        $user = auth()->user();
        abort_unless($user, 403);

        $project = Project::query()
            ->whereIn(
                'company_id',
                $user->companiesForPermissionQuery(ProjectPermissionEnum::View)
                    ->select('companies.id')
                    ->reorder()
            )
            ->findOrFail($projectId);

        $items = Data::query()
            ->where('project_id', $project->getKey())
            ->where('order_no', $orderNumber)
            ->where('order_year', $orderYear)
            ->orderBy('id')
            ->get(['description', 'qty', 'code']);

        abort_if($items->isEmpty(), 404);
        abort_if($items->count() > 16, 422, 'The order exceeds the template capacity.');

        $template = ExcelTemplate::activeFor('order_export');
        $templatePath = $template
            ? Storage::disk($template->disk)->path($template->file_path)
            : storage_path('app/templateExcel/FormatoODT.xlsx');
        abort_unless(File::isFile($templatePath), 404, 'Order template not found.');

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();
        $date = now()->format('d-m-Y');

        $sheet->setCellValue('G2', $orderNumber);
        $sheet->setCellValue('G29', $orderNumber);
        $sheet->setCellValue('C6', $project->pda_code);
        $sheet->setCellValue('C33', $project->pda_code);
        $sheet->setCellValue('C4', $date);
        $sheet->setCellValue('C31', $date);
        $sheet->setCellValue('F6', $items->first()->code);
        $sheet->setCellValue('F33', $items->first()->code);

        foreach ($items->values() as $index => $item) {
            $firstRow = 8 + $index;
            $secondRow = 35 + $index;

            $sheet->setCellValue("A{$firstRow}", (float) $item->qty);
            $sheet->setCellValue("B{$firstRow}", $item->description);
            $sheet->setCellValue("A{$secondRow}", (float) $item->qty);
            $sheet->setCellValue("B{$secondRow}", $item->description);
        }

        $directory = storage_path('app/exports');
        File::ensureDirectoryExists($directory);

        $safeOrderNumber = Str::slug($orderNumber, '-');
        $filename = "order-{$safeOrderNumber}-{$orderYear}-".now()->format('Y-m-d-His').'.xlsx';
        $outputPath = $directory.DIRECTORY_SEPARATOR.$filename;

        (new Xlsx($spreadsheet))->save($outputPath);
        $spreadsheet->disconnectWorksheets();

        return response()
            ->download($outputPath, $filename)
            ->deleteFileAfterSend(true);
    }

    public function render(): View
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $orders = Data::query()
            ->join('projects', 'projects.id', '=', 'data.project_id')
            ->join('companies', 'companies.id', '=', 'projects.company_id')
            ->whereIn(
                'projects.company_id',
                $user->companiesForPermissionQuery(ProjectPermissionEnum::View)
                    ->select('companies.id')
                    ->reorder()
            )
            ->when(
                $this->project,
                fn (Builder $query): Builder => $query->where(
                    'projects.id',
                    $this->project->getKey()
                )
            )
            ->whereNotNull('data.order_no')
            ->where('data.order_no', '<>', '')
            ->whereNotNull('data.order_year')
            ->when($this->plantFilter !== [], fn (Builder $query): Builder =>
                $query->whereIn('companies.company_code', $this->plantFilter))
            ->when($this->yearFilter !== [], fn (Builder $query): Builder =>
                $query->whereIn('data.order_year', $this->yearFilter))
            ->when($this->orderFilter !== [], fn (Builder $query): Builder =>
                $query->whereIn('data.order_no', $this->orderFilter))
            ->when(
                filled($this->search),
                fn (Builder $query): Builder => $query->where(
                    function (Builder $searchQuery): void {
                        $term = '%'.$this->search.'%';
                        $searchQuery
                            ->where('data.order_no', 'like', $term)
                            ->orWhere('projects.name', 'like', $term)
                            ->orWhere('projects.pda_code', 'like', $term);
                        $searchQuery->orWhere('companies.company_name', 'like', $term)
                            ->orWhere('companies.company_code', 'like', $term)
                            ->orWhere('data.order_year', 'like', $term);
                    }
                )
            )
            ->selectRaw(
                'projects.id AS project_id, projects.name AS project_name, projects.slug AS project_slug, '
                    .'projects.pda_code, companies.company_code, companies.company_name, '
                    .'data.order_no, data.order_year, COUNT(*) AS item_count'
            )
            ->groupBy(
                'projects.id',
                'projects.name',
                'projects.slug',
                'projects.pda_code',
                'companies.company_code',
                'companies.company_name',
                'data.order_year',
                'data.order_no'
            )
            ->orderBy('data.order_year', 'desc')
            ->orderBy('data.order_no', $this->sortDir)
            ->paginate($this->perPage);

        $optionQuery = Data::query()->join('projects', 'projects.id', '=', 'data.project_id')
            ->join('companies', 'companies.id', '=', 'projects.company_id')
            ->whereIn('projects.company_id', $user->companiesForPermissionQuery(ProjectPermissionEnum::View)
                ->select('companies.id')->reorder())
            ->when($this->project, fn (Builder $query): Builder =>
                $query->where('projects.id', $this->project->getKey()))
            ->whereNotNull('data.order_no')->where('data.order_no', '<>', '');

        return view('livewire.orders.ordenes', [
            'orders' => $orders,
            'plantOptions' => (clone $optionQuery)->select('companies.company_code', 'companies.company_name')
                ->distinct()->orderBy('companies.company_name')->get()->map(fn ($company) => [
                    'value' => $company->company_code, 'label' => $company->company_name,
                ]),
            'yearOptions' => (clone $optionQuery)->whereNotNull('data.order_year')->distinct()
                ->orderByDesc('data.order_year')->pluck('data.order_year')->map(fn ($year) => [
                    'value' => (string) $year, 'label' => (string) $year,
                ]),
            'orderOptions' => (clone $optionQuery)->distinct()->orderBy('data.order_no')
                ->pluck('data.order_no')->map(fn ($order) => ['value' => (string) $order, 'label' => (string) $order]),
        ])->layout('layouts.app');
    }
}
