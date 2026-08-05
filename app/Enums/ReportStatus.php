<?php

namespace App\Enums;

/**
 * Life-cycle of a citizen report. The whole product is this status ladder made
 * public: every field's page shows how long a report has spent in each state,
 * which is the pressure that keeps the system honest.
 *
 * Prijavljeno → Potvrđeno → U planu radova → Rešeno   (happy path)
 *                                          ↘ Odbijeno  (with a public reason)
 */
enum ReportStatus: string
{
    case Reported = 'prijavljeno';
    case Confirmed = 'potvrdjeno';
    case Planned = 'u_planu';
    case Resolved = 'reseno';
    case Rejected = 'odbijeno';

    /** Human label (Serbian — this is a municipal tool for Medijana). */
    public function label(): string
    {
        return match ($this) {
            self::Reported => 'Prijavljeno',
            self::Confirmed => 'Potvrđeno',
            self::Planned => 'U planu radova',
            self::Resolved => 'Rešeno',
            self::Rejected => 'Odbijeno',
        };
    }

    /** Filament / badge colour. */
    public function color(): string
    {
        return match ($this) {
            self::Reported => 'gray',
            self::Confirmed => 'info',
            self::Planned => 'warning',
            self::Resolved => 'success',
            self::Rejected => 'danger',
        };
    }

    /** A terminal status closes the report — no further work is expected. */
    public function isClosed(): bool
    {
        return in_array($this, [self::Resolved, self::Rejected], true);
    }

    /** @return array<string, string> value => label, for selects/filters. */
    public static function options(): array
    {
        return array_reduce(
            self::cases(),
            fn (array $carry, self $s) => $carry + [$s->value => $s->label()],
            [],
        );
    }
}
