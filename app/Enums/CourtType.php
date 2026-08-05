<?php

namespace App\Enums;

/** Kind of sports field. */
enum CourtType: string
{
    case Basketball = 'kosarka';
    case Football = 'mali_fudbal';
    case Volleyball = 'odbojka';
    case Athletics = 'atletika';
    case Tennis = 'tenis';
    case Multipurpose = 'visenamenski';

    public function label(): string
    {
        return match ($this) {
            self::Basketball => 'Košarka',
            self::Football => 'Mali fudbal',
            self::Volleyball => 'Odbojka',
            self::Athletics => 'Atletika',
            self::Tennis => 'Tenis',
            self::Multipurpose => 'Višenamenski',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return array_reduce(
            self::cases(),
            fn (array $carry, self $t) => $carry + [$t->value => $t->label()],
            [],
        );
    }
}
