<?php

namespace App\Traits;

trait EnumValues{

    /**
     * Get all values of the enum
     *
     * @return array
     */
    public static function getValues(): array
    {
        $values = [];
        foreach (static::cases() as $case)
            $values[] = $case->value;
        return $values;
    }

    /**
     * Get values by key
     *
     * @param array $keys
     * @return array
     */
    public static function valuesOf(array $keys): array
    {
        foreach ($keys as &$key)
            $key = ucfirst(strtolower($key));

        $values = [];
        foreach (static::cases() as $case){
            if(in_array($case->name , $keys))
                $values[] = $case->value;
        }
        return $values;
    }

}
