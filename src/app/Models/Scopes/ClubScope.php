<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ClubScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * Filters records by club_id matching the current session's club.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $clubId = session('current_club_id');

        if ($clubId) {
            $builder->where($model->getTable() . '.club_id', $clubId);
        }
    }
}
