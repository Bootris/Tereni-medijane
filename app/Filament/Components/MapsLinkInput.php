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
            ->helperText('Najtačnije: na telefonu zadrži prst na terenu i kopiraj koordinate sa vrha ekrana; na računaru desni klik na teren → koordinate. Radi i sa „Deli" linkom (share.google daje približnu tačku sa ulice).')
            ->afterStateUpdated(function (?string $state, callable $set) {
                if (blank($state)) {
                    return;
                }

                if ($coords = GoogleMapsLocation::parse($state)) {
                    $set('lat', (string) $coords['lat']);
                    $set('lng', (string) $coords['lng']);
                    $set('maps_link', null);

                    $approx = ! empty($coords['approx']);
                    Notification::make()
                        ->title($approx ? 'Lokacija preuzeta (približno)' : 'Lokacija preuzeta')
                        ->body($approx
                            ? 'Ovaj link ne nosi tačne koordinate - uzeta je tačka sa ulice ispred mesta (' . $coords['lat'] . ', ' . $coords['lng'] . '). Proveri na mapi i po potrebi ispravi lat/lng.'
                            : $coords['lat'] . ', ' . $coords['lng'])
                        ->{$approx ? 'warning' : 'success'}()
                        ->persistent()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Ne mogu da pročitam lokaciju iz ovog linka')
                    ->body('Najsigurnije je kopirati koordinate: na telefonu zadrži prst na terenu u Google Maps i tapni koordinate na vrhu ekrana; na računaru desni klik na teren → prva stavka. Nalepi ih ovde u obliku 43.3209, 21.9033.')
                    ->danger()
                    ->persistent()
                    ->send();
            });
    }
}
