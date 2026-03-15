<?php

namespace App\Modules\Audit\DTOs;

final class AuditLogFilterDTO
{
    public function __construct(
        public readonly ?string $fromDate,
        public readonly ?string $toDate,
        public readonly ?string $module,
        public readonly ?string $action,
        public readonly ?int $actorId,
        public readonly ?string $entityType,
        public readonly ?int $entityId,
        public readonly ?string $search,
        public readonly int $perPage
    ) {
    }

    public static function fromArray(array $validated): self
    {
        return new self(
            fromDate: $validated['from_date'] ?? null,
            toDate: $validated['to_date'] ?? null,
            module: $validated['module'] ?? null,
            action: $validated['action'] ?? null,
            actorId: isset($validated['actor_id']) ? (int) $validated['actor_id'] : null,
            entityType: $validated['entity_type'] ?? null,
            entityId: isset($validated['entity_id']) ? (int) $validated['entity_id'] : null,
            search: $validated['search'] ?? null,
            perPage: (int) ($validated['per_page'] ?? 20),
        );
    }

    public function toArray(): array
    {
        return [
            'from_date' => $this->fromDate,
            'to_date' => $this->toDate,
            'module' => $this->module,
            'action' => $this->action,
            'actor_id' => $this->actorId,
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'search' => $this->search,
        ];
    }
}
