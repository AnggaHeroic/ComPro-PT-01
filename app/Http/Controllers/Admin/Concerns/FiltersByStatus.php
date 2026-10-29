<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait FiltersByStatus
{
    /**
     * Filter status dari query string: "published", "draft", atau null (semua).
     */
    protected function statusFilter(Request $request): ?string
    {
        $status = $request->query('status');

        return in_array($status, ['published', 'draft'], true) ? $status : null;
    }

    protected function applyStatus(Builder $query, ?string $status): Builder
    {
        return match ($status) {
            'published' => $query->where('is_published', true),
            'draft' => $query->where('is_published', false),
            default => $query,
        };
    }

    /**
     * Jumlah per status pada lingkup yang diberikan (tanpa filter status/pencarian).
     *
     * @return array{total: int, published: int, draft: int}
     */
    protected function statusCounts(Builder $scope): array
    {
        $row = $scope->clone()
            ->toBase()
            ->selectRaw('count(*) as total, coalesce(sum(case when is_published then 1 else 0 end), 0) as published')
            ->first();

        $total = (int) ($row->total ?? 0);
        $published = (int) ($row->published ?? 0);

        return ['total' => $total, 'published' => $published, 'draft' => $total - $published];
    }
}
