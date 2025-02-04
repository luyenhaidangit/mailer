<?php


namespace App\Filters;

use App\Helpers\Core\Traits\Helpers;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class FilterBuilder
{
    use Helpers;
    public $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
        'like', 'like binary', 'not like', 'ilike',
        '&', '|', '^', '<<', '>>',
        'rlike', 'not rlike', 'regexp', 'not regexp',
        '~', '~*', '!~', '!~*', 'similar to',
        'not similar to', 'not ilike', '~~*', '!~~*',
    ];

    public $attributes = [];

    /**
     * @var Builder
     */
    public $builder;


    public function apply(Builder $query)
    {
        $this->builder = $query;
        foreach (request()->all() as $key => $operator) {
            if (strpos($key, 'operator')) {
                $this->attributes[$key] = sanitize_data($operator);
            }
        }

        foreach (request()->all() as $key => $filter) {
            $method_name = Str::camel($key);
            if (method_exists($this, $method_name) && !strpos($key, 'operator')) {
                call_user_func_array([$this, $method_name], array_filter([sanitize_data($filter)]));
            }
        }

        return $this->builder;
    }

    public function __get($name)
    {
        $name = Str::snake($name);
        if (strpos($name, 'operator')) {
            return (!empty($this->attributes[$name]) && in_array($this->attributes[$name], $this->operators)) ?
                $this->attributes[$name] :
                '=';
        }
    }

    public function whereClause($field, $value, $operator = '=')
    {
        $this->builder->when($value, function (Builder $builder) use ($field, $value, $operator) {
            $builder->where($field, $operator, $value);
        });
    }

    public function groupSearch($value, array $params, Closure $callback = null)
    {
        return $this->builder->when($value, function (Builder $builder) use ($params, $value, $callback) {
            $this->whereLike($params, $value, $builder);

            $callback ? $callback($builder) : false;
        });
    }

    public function whereInClause($field, array $values)
    {
        $this->builder->when($values, function (Builder $builder) use ($field, $values) {
            $builder->whereIn($field, $values);
        });
    }

    public function whereHasLike(array $params, string $term = null)
    {
        if ($term) {
            $builder = $this->getBuilder($term);
            $whereHasClause = 'whereHas';
            foreach ($params as $relation => $attributes) {
                $builder->{$whereHasClause}($relation, function (Builder $builder) use ($attributes, $term) {
                    $this->whereLike($attributes, $term, $builder);
                });
                $whereHasClause = 'orWhereHas';
            }
        }
    }

    private function whereLike($columns, $term, $builder = null)
    {
        $columns = $this->checkMakeArray($columns);
        $builder = $builder ?: $this->builder;
        $whereClause = 'where';
        foreach ($columns as $key) {
            $builder->{$whereClause}($key, 'LIKE', "%{$term}%");
            $whereClause = 'orWhere';
        }
    }

    public function getBuilder($values = null)
    {
        return $this->builder->when($values, function (Builder $builder) {
            return $builder;
        });
    }

}
