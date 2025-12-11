<?php

namespace App\Helpers;

class DataConverter
{
    public static function filterNulls(array $data): array
    {
        return array_filter($data, fn($value) => !is_null($value) && $value !== '');
    }
}
