<?php

namespace App\Filament\Resources\Reports\Schemas;

use App\Enums\ReportStatus;
use App\Models\Report;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Prijava')
                    ->columnSpan(2)
                    ->columns(2)
                    ->components([
                        Placeholder::make('court')
                            ->label('Teren')
                            ->content(fn (?Report $record): string => $record?->court
                                ? $record->court->name.' — '.$record->court->facility?->name
                                : '—'),
                        Placeholder::make('category_label')
                            ->label('Kategorija')
                            ->content(fn (?Report $record): string => $record?->category?->label() ?? '—'),
                        Placeholder::make('reporter')
                            ->label('Prijavio')
                            ->content(fn (?Report $record): string => trim(
                                ($record?->reporter_name ?: 'Anoniman')
                                .($record?->reporter_contact ? ' · '.$record->reporter_contact : '')
                            )),
                        Placeholder::make('submitted')
                            ->label('Vreme prijave')
                            ->content(fn (?Report $record): string => $record?->created_at?->format('d.m.Y H:i') ?? '—'),
                        Placeholder::make('description')
                            ->label('Opis')
                            ->columnSpanFull()
                            ->content(fn (?Report $record): string => $record?->description ?: '—'),
                        Placeholder::make('photo')
                            ->label('Fotografija')
                            ->columnSpanFull()
                            ->content(fn (?Report $record): HtmlString => new HtmlString(
                                $record?->photoUrl()
                                    ? '<a href="'.e($record->photoUrl()).'" target="_blank" rel="noopener">'
                                        .'<img src="'.e($record->photoUrl()).'" style="max-height:16rem;border-radius:0.5rem" alt=""></a>'
                                    : '—'
                            )),
                    ]),

                Section::make('Status i moderacija')
                    ->columnSpan(1)
                    ->components([
                        Select::make('status')
                            ->label('Status')
                            ->options(ReportStatus::options())
                            ->default(ReportStatus::Reported->value)
                            ->required()
                            ->native(false)
                            ->helperText('Promena beleži korak u javnoj istoriji.'),
                        Textarea::make('status_note')
                            ->label('Komentar uz ovaj korak')
                            ->rows(2)
                            // Not a column — EditReport pulls it out and logs it into
                            // the timeline when the status actually changes.
                            ->helperText('Javno vidljivo objašnjenje promene statusa.'),
                        Toggle::make('is_public')
                            ->label('Javno vidljivo (moderisano)'),
                        Toggle::make('is_flagged')
                            ->label('Prijavljeno kao neprikladno'),
                        Textarea::make('resolution_note')
                            ->label('Javni komentar zaduženog')
                            ->rows(3),
                    ]),

                Section::make('Istorija statusa')
                    ->columnSpanFull()
                    ->visible(fn (?Report $record): bool => (bool) $record?->exists)
                    ->components([
                        Placeholder::make('timeline')
                            ->hiddenLabel()
                            ->content(fn (?Report $record): HtmlString => new HtmlString(
                                static::timelineHtml($record)
                            )),
                    ]),
            ]);
    }

    private static function timelineHtml(?Report $record): string
    {
        if (! $record) {
            return '—';
        }

        $changes = $record->statusChanges()->with('changedBy')->get();

        if ($changes->isEmpty()) {
            return '<em>Još nema zabeleženih promena statusa.</em>';
        }

        $rows = $changes->map(function ($change) {
            $when = $change->created_at?->format('d.m.Y H:i') ?? '';
            $who = $change->changedBy?->name ? ' · '.e($change->changedBy->name) : '';
            $note = $change->note ? '<div style="color:#6b7280">'.e($change->note).'</div>' : '';

            return '<li style="margin-bottom:0.5rem">'
                .'<strong>'.e($change->status->label()).'</strong> '
                .'<span style="color:#9ca3af">'.e($when).$who.'</span>'
                .$note.'</li>';
        })->implode('');

        return '<ol style="padding-left:1rem;list-style:decimal">'.$rows.'</ol>';
    }
}
