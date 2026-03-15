<?php

namespace App\Support;

use Illuminate\Pagination\Paginator;

class PaginationHelper
{
    /**
     * Get pagination parameters from request
     */
    public static function getParams(int $defaultPerPage = 15): array
    {
        $perPage = request()->integer('per_page', $defaultPerPage);
        $page = request()->integer('page', 1);

        // Enforce limits
        $perPage = min($perPage, 100);
        $perPage = max($perPage, 1);
        $page = max($page, 1);

        return [
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    /**
     * Get page from request
     */
    public static function page(int $default = 1): int
    {
        return max(request()->integer('page', $default), 1);
    }

    /**
     * Get per_page from request with limit
     */
    public static function perPage(int $default = 15, int $max = 100): int
    {
        $perPage = request()->integer('per_page', $default);
        return min(max($perPage, 1), $max);
    }

    /**
     * Get sort parameters from request
     */
    public static function getSortParams(): array
    {
        $sort = request()->string('sort', 'created_at');
        $direction = request()->string('direction', 'desc');

        // Whitelist direction to prevent injection
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'desc';

        return [
            'sort' => $sort,
            'direction' => $direction,
        ];
    }

    /**
     * Get search parameters from request
     */
    public static function searchParams(): array
    {
        return [
            'search' => request()->string('search', ''),
            'filter' => request()->array('filter', []),
        ];
    }

    /**
     * Format paginator response metadata
     */
    public static function formatMetadata(Paginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'has_more' => $paginator->hasMorePages(),
        ];
    }
}

