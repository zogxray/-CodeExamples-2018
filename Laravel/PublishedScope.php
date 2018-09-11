<?php
/**
 * Created by PhpStorm.
 * User: zogxray
 * Date: 5/30/18
 * Time: 11:40 PM
 */

namespace App\Scopes;


use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class PublisshedScope
 * @package App\Scopes
 */
class PublishedScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (false === auth()->check() && false === auth()->user()->isAdmin()) {
            $builder->whereNotNull('published_at');
        }
    }
}