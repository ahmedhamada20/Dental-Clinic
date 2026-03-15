<?php

namespace App\Modules\Billing\DTOs;

/**
 * DTO for creating a promotion
 */
class CreatePromotionData
{
    public function __construct(
        public string $title_ar,
        public string $title_en,
        public string $code,
        public string $promotion_type, // fixed, percentage
        public float $value,
        public bool $applies_once = false,
        public string $starts_at = '',
        public string $ends_at = '',
        public bool $is_active = true,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title_ar: $data['title_ar'],
            title_en: $data['title_en'],
            code: $data['code'],
            promotion_type: $data['promotion_type'],
            value: (float)$data['value'],
            applies_once: (bool)($data['applies_once'] ?? false),
            starts_at: $data['starts_at'],
            ends_at: $data['ends_at'],
            is_active: (bool)($data['is_active'] ?? true),
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'title_ar' => $this->title_ar,
            'title_en' => $this->title_en,
            'code' => $this->code,
            'promotion_type' => $this->promotion_type,
            'value' => $this->value,
            'applies_once' => $this->applies_once,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
        ];
    }
}

