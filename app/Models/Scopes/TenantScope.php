<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // If there's an authenticated user, automatically scope queries to their tenant_id
        // For MVP webhooks (no auth user), we will explicitly set tenant_id when processing.
        if (auth()->hasUser()) {
            $builder->where('tenant_id', auth()->user()->tenant_id);
        }
    }
}
