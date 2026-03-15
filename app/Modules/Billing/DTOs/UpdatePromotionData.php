<?php

namespace App\Modules\Billing\DTOs;

/**
 * DTO for updating a promotion
 */
class UpdatePromotionData
{
    public function __construct(
        public ?string $title_ar = null,
        public ?string $title_en = null,
        public ?string $code = null,
        public ?string $promotion_type = null,
        public ?float $value = null,
        public ?bool $applies_once = null,
        public ?string $starts_at = null,
        public ?string $ends_at = null,
        public ?bool $is_active = null,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title_ar: $data['title_ar'] ?? null,
            title_en: $data['title_en'] ?? null,
            code: $data['code'] ?? null,
            promotion_type: $data['promotion_type'] ?? null,
            value: isset($data['value']) ? (float)$data['value'] : null,
            applies_once: isset($data['applies_once']) ? (bool)$data['applies_once'] : null,
            starts_at: $data['starts_at'] ?? null,
            ends_at: $data['ends_at'] ?? null,
            is_active: isset($data['is_active']) ? (bool)$data['is_active'] : null,
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
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
        ], fn($value) => $value !== null);
    }
}

