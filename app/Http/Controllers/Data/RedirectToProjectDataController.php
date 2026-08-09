<?php

namespace App\Http\Controllers\Data;

use App\Enums\ProjectPermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectToProjectDataController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $projects = Project::query()
            ->whereIn(
                'company_id',
                $user->companiesForPermissionQuery(ProjectPermissionEnum::View)
                    ->select('companies.id')
                    ->reorder()
            );

        $requestedProjectId = $request->integer('id');
        $project = $requestedProjectId > 0
            ? (clone $projects)->find($requestedProjectId)
            : null;

        $project ??= $projects
            ->latest('created_at')
            ->latest('id')
            ->first();

        if (! $project) {
            return to_route('projects')
                ->with('warning', 'No accessible projects are available yet.');
        }

        return to_route('projects.data', ['project' => $project->slug]);
    }
}
