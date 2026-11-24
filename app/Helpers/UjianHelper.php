<?php

namespace App\Helpers;

class UjianHelper
{
    public static function getDisplayNumber(int $ujianSoalId, ?array $soalOrder): int
    {
        if (! $soalOrder) {
            return 0;
        }

        $position = array_search($ujianSoalId, $soalOrder, strict: true);

        return $position !== false ? $position + 1 : 0;
    }
}
