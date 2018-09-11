<?php
/**
 * Created by PhpStorm.
 * User: zogxray
 * Date: 06.07.18
 * Time: 10:06
 */

namespace App\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ProjectScope
 * @package App\Scopes
 */
class ProjectScope  implements Scope
{
    /**
     * @param Builder $builder
     * @param Model $model
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->where('project_id', app('project')->id);
    }
}