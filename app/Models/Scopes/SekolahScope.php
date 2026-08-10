<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Isolasi data antar sekolah. Selalu memfilter query ke sekolah milik user
 * yang login. Super admin (sekolah_id null) melihat semua. Tanpa user login
 * (console/seeder/queue job) scope tidak diterapkan — job WAJIB set/filter
 * sekolah_id secara eksplisit.
 */
class SekolahScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if ($user && $user->sekolah_id) {
            $builder->where($model->getTable().'.sekolah_id', $user->sekolah_id);
        }
    }
}
