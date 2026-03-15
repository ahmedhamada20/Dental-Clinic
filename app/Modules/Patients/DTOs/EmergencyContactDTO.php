<?php

namespace App\Modules\Patients\DTOs;

class EmergencyContactDTO
{
    public function __construct(
        public string $name,
        public string $relation,
        public string $phone,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            relation: $data['relation'],
            phone: $data['phone'],
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'relation' => $this->relation,
            'phone' => $this->phone,
            'notes' => $this->notes,
        ];
    }
}

