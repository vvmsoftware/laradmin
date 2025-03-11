<?php

namespace App\Traits;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait HasRequestSorting
{
    public function getSorting(Request $request, array $allowed = [], $defaultSortBy = 'id', $defaultSortDir = 'desc'): array
    {
        $sortBy = $defaultSortBy;
        $sortDir = $defaultSortDir;

        if ($request->has('sortBy') && $request->sortBy != null) {
            if (in_array($request->sortBy, $allowed)) {
                $sortBy = $request->sortBy;
            }
            if ($request->has('sortDir') && in_array($request->sortDir, ['asc', 'desc'])) {
                $sortDir = $request->sortDir;
            }
        }

        return [$sortBy, $sortDir];
    }

    public function getLimit(Request $request, int $defaultLimit = 20, int $maxLimit = 100): int
    {
        $limit = $request->has('perPage') ? (int)$request->perPage : $defaultLimit;
        if ($limit < 1 || $limit > $maxLimit) {
            $limit = $defaultLimit;
        }
        return $limit;
    }

    public function getSearch(Request $request, Builder $query, array $fields = [] ): Builder
    {
        $like = 'like';
        if (DB::connection() instanceof \Illuminate\Database\PostgresConnection) {
            $like = 'ilike';
        }
        if ($request->has('search') && $request->search != '' && $request->search != null) {
            $query->orWhere(function($query) use($fields, $request, $like) {
                foreach ($fields as $field) {
                    $query->orWhere($field, $like, '%'.$request->search.'%');
                }
            });
        }
        return $query;
    }

    public function getHavingSearch(Request $request, Builder $query, array $fields = []): Builder
    {
        $like = 'like';
        if (DB::connection() instanceof \Illuminate\Database\PostgresConnection) {
            $like = 'ilike';
        }
        if ($request->has('search') && $request->search != '' && $request->search != null) {
            foreach ($fields as $field=>$row) {
                $query->orWhereHas($field, function ($query) use ($request, $row, $like) {
                    $query->where($row, $like, '%'.$request->search.'%');
                });
            }
        }
        return $query;
    }
}