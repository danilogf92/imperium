<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn (Model $model) => $model->writeAuditLog('created'));
        static::updated(fn (Model $model) => $model->writeAuditLog('updated'));
        static::deleted(fn (Model $model) => $model->writeAuditLog('deleted'));
    }

    protected function writeAuditLog(string $event): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        $excluded = array_unique(array_merge(
            ['password', 'remember_token', 'updated_at'],
            property_exists($this, 'auditExclude') ? $this->auditExclude : [],
        ));

        if ($event === 'updated') {
            $changedKeys = array_keys(Arr::except($this->getChanges(), $excluded));

            if ($changedKeys === []) {
                return;
            }

            $oldValues = Arr::only($this->getOriginal(), $changedKeys);
            $newValues = Arr::only($this->getAttributes(), $changedKeys);
        } elseif ($event === 'created') {
            $oldValues = [];
            $newValues = Arr::except($this->getAttributes(), $excluded);
        } else {
            $oldValues = Arr::except($this->getOriginal(), $excluded);
            $newValues = [];
        }

        [$companyId, $projectId] = $this->auditContext();
        $request = app()->runningInConsole() ? null : request();

        AuditLog::query()->create([
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => $this->getMorphClass(),
            'auditable_id' => $this->getKey(),
            'company_id' => $companyId,
            'project_id' => $projectId,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    /** @return array{0: int|null, 1: int|null} */
    protected function auditContext(): array
    {
        $projectId = $this instanceof Project
            ? (int) $this->getKey()
            : ($this->getAttribute('project_id')
                ? (int) $this->getAttribute('project_id')
                : null);
        $companyId = $this->getAttribute('company_id')
            ? (int) $this->getAttribute('company_id')
            : null;

        if (! $companyId && $projectId) {
            $companyId = Project::query()
                ->whereKey($projectId)
                ->value('company_id');
        }

        return [
            $companyId ? (int) $companyId : null,
            $projectId,
        ];
    }
}
