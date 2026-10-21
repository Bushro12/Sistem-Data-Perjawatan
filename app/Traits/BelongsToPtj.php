<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToPtj
{
    protected static function bootBelongsToPtj(): void
    {
        static::addGlobalScope('ptj', function (Builder $builder) {
            $user = auth()->user();

            if (! $user || $user->isAdmin()) { // adjust to your role check
                return;
            }

            $model = $builder->getModel();

            $relation = method_exists($model, 'ptjRelation')
                ? $model->ptjRelation()
                : null;

            if ($relation) {
                $builder->whereHas($relation, fn ($q) => $q->where('ptj_id', $user->ptj_id));
            } else {
                $builder->where($model->getTable() . '.ptj_id', $user->ptj_id);
            }
        });
    }
}