<?php

namespace App\Support;

class AcademicYear
{
    public const START_YEAR = 2018;

    /**
     * @return list<string>
     */
    public static function options(): array
    {
        return array_map(
            static fn (int $year): string => (string) $year,
            range(self::START_YEAR, now()->year + 1)
        );
    }
}
