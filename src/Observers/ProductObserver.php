<?php

namespace Viviniko\Catalog\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ProductObserver
{
    public function saving(Model $entity)
    {
        if (!$entity->exists() || empty($entity->created_by)) {
            $entity->created_by = Auth::check() ? Auth::user()->name : '';
        }

        if (empty($entity->updated_by)) {
            $entity->updated_by = Auth::check() ? Auth::user()->name : '';
        }
    }

    public function saved(Model $entity)
    {
        if (!$entity->is_active) {
            $entity->unsearchable();
        }
    }

    public function deleted(Model $entity)
    {
        $entity->unsearchable();
    }
}