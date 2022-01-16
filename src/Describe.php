<?php

namespace Wilby\Describable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

trait Describe
{
    public static function describe()
    {
        $table = (new static)->getTable();

        return [
            'name' => static::class,
            'table' => $table,
            'columns' => static::describeColumns($table),
            'relationships' => static::describeRelationships($table)
        ];
    }

    public static function describeColumns($table)
    {
        $table = $table ?: (new static)->getTable();

        $guarded = config('describable.guarded', []);

        /** @var Illuminate\Database\Connection */
        $connection = DB::connection();

        return collect(Schema::getColumnListing($table))
            ->filter(function ($name) use ($guarded) {
                return !Str::contains($name, $guarded);
            })
            ->map(function ($name) use ($connection, $table) {
                return array_merge(
                    [
                        'label' => Str::of($name)->replace('_', ' ')->ucwords()
                    ],
                    $connection->getDoctrineColumn($table, $name)->toArray()
                );
            })
            ->sortBy('label')
            ->toArray();
    }

    public static function describeRelationships($columns)
    {
        $instance = new static;

        // Get public methods declared without parameters and non inherited
        $class = get_class($instance);
        $allMethods = (new \ReflectionClass($class))->getMethods(\ReflectionMethod::IS_PUBLIC);
        $methods = array_filter(
            $allMethods,
            function ($method) use ($class) {
                return $method->class === $class
                    && !$method->getParameters()                  // relationships have no parameters
                    && $method->getName() !== 'getRelationships'; // prevent infinite recursion
            }
        );


        return $relations;
    }
}
