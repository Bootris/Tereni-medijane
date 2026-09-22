<?php

namespace App\Filament\Components;

use App\Support\Tereni\GoogleMapsLocation;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

/**
 * Paste-a-Google-Maps-link field: fills the sibling lat/lng inputs from a
 * share link or copied coordinates, so nobody types coordinates by hand.
 * The field itself is never persisted.
 */
class MapsLinkInput
{
    public static function make(): TextInput
    {
        return TextInput::make('maps_link')
            ->label('Google Maps lokacija')
            ->placeholder('Nalepi „Deli" link ili koordinate…')
            ->dehydrated(false)
            ->live(onBlur: true)
            ->helperText('Radi sa linkom iz „Deli/Share" opcije, punim URL-om ili koordinatama „43.3209, 21.9033" — lat/lng se popune sami.')
            ->afterStateUpdated(function (?string $state, callable $set) {
                if (blank($state)) {
                    return;
                }

                if ($coords = GoogleMapsLocation::parse($state)) {
                    $set('lat', (string) $coords['lat']);
                    $set('lng', (string) $coords['lng']);
                    $set('maps_link', null);
                    Notification::make()
                        ->title('Lokacija preuzeta')
                        ->body($coords['lat'] . ', ' . $coords['lng'])
                        ->success()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Ne mogu da pročitam lokaciju')
                    ->body('Nalepi link iz Google Maps „Deli" opcije ili koordinate u formatu 43.3209, 21.9033.')
                    ->danger()
                    ->send();
            });
    }
}
