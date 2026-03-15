<?php

namespace App\Support;

use Illuminate\Support\Collection;

final class SpecialtyModuleRegistry
{
    /**
     * @var array<string, SpecialtyModule>
     */
    private array $modules = [];

    public function register(SpecialtyModule $module): void
    {
        $this->modules[$module->key] = $module;
    }

    /**
     * @return Collection<int, SpecialtyModule>
     */
    public function all(): Collection
    {
        return collect($this->modules)
            ->values()
            ->filter(fn (SpecialtyModule $module) => $module->enabled)
            ->values();
    }

    /**
     * @return Collection<int, SpecialtyModule>
     */
    public function forSpecialty(?string $slug): Collection
    {
        return $this->all()
            ->filter(fn (SpecialtyModule $module) => $module->supportsSpecialty($slug))
            ->values();
    }

    public function hasRoute(string $routeName): bool
    {
        return $this->all()->contains(
            fn (SpecialtyModule $module) => collect($module->navigation)
                ->contains(fn (array $item) => ($item['route'] ?? null) === $routeName)
        );
    }
}

