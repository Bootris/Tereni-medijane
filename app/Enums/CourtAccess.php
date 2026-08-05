<?php

namespace App\Enums;

/** Who may use the field. */
enum CourtAccess: string
{
    case Public = 'javno';
    case StudentsOnly = 'djaci';
    case Locked = 'zakljucano';

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Javno dostupno',
            self::StudentsOnly => 'Samo đaci',
            self::Locked => 'Zaključano',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Public => 'success',
            self::StudentsOnly => 'warning',
            self::Locked => 'gray',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return array_reduce(
            self::cases(),
            fn (array $carry, self $a) => $carry + [$a->value => $a->label()],
            [],
        );
    }
}
