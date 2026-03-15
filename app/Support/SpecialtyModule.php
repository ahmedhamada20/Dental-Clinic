<?php

namespace App\Support;

final class SpecialtyModule
{
    /**
     * @param  array<int, array{label: string, route: string, icon?: string, can?: string}>  $navigation
     */
    public function __construct(
        public readonly string $key,
        public readonly string $name,
        public readonly string $description,
        public readonly array $specialtySlugs = [],
        public readonly bool $enabled = true,
        public readonly ?string $adminRoutesPath = null,
        public readonly array $navigation = [],
    ) {
    }

    public function supportsSpecialty(?string $slug): bool
    {
        if ($slug === null || $slug === '') {
            return false;
        }

        return in_array($slug, $this->specialtySlugs, true);
    }
}

