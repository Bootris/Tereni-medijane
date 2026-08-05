<?php

namespace App\Enums;

/** What a citizen is reporting about a field. */
enum ReportCategory: string
{
    case Hoop = 'kos';
    case Net = 'mreza';
    case Surface = 'podloga';
    case Lighting = 'osvetljenje';
    case Fence = 'ograda';
    case Litter = 'smece';
    case Other = 'ostalo';

    public function label(): string
    {
        return match ($this) {
            self::Hoop => 'Koš',
            self::Net => 'Mreža',
            self::Surface => 'Podloga',
            self::Lighting => 'Osvetljenje',
            self::Fence => 'Ograda',
            self::Litter => 'Smeće',
            self::Other => 'Ostalo',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return array_reduce(
            self::cases(),
            fn (array $carry, self $c) => $carry + [$c->value => $c->label()],
            [],
        );
    }
}
