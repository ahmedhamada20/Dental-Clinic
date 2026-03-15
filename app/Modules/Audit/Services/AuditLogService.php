<?php

namespace App\Modules\Audit\Services;

use App\Models\System\AuditLog;
use App\Models\User;
use App\Modules\Audit\DTOs\AuditLogFilterDTO;
use App\Modules\Audit\Support\AuditLogger;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AuditLogService
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function list(AuditLogFilterDTO $dto): LengthAwarePaginator
    {
        return AuditLog::query()
            ->with('actor:id,first_name,last_name,full_name,email')
            ->filter($dto->toArray())
            ->latest('created_at')
            ->paginate($dto->perPage)
            ->withQueryString();
    }

    public function find(int $id): AuditLog
    {
        return AuditLog::query()->with('actor:id,first_name,last_name,full_name,email')->findOrFail($id);
    }

    public function log(string $module, string $action, Model|string|null $entity = null, ?array $oldValues = null, ?array $newValues = null): AuditLog
    {
        return $this->auditLogger->log($module, $action, $entity, $oldValues, $newValues);
    }

    public function modules(): Collection
    {
        return AuditLog::query()->select('module')->distinct()->orderBy('module')->pluck('module');
    }

    public function actions(): Collection
    {
        return AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action');
    }

    public function actors(): Collection
    {
        return User::query()->orderBy('full_name')->get(['id', 'first_name', 'last_name', 'full_name', 'email']);
    }
}
