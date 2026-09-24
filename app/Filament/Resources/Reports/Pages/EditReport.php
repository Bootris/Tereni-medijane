<?php

namespace App\Filament\Resources\Reports\Pages;

use App\Enums\ReportStatus;
use App\Filament\Resources\Reports\ReportResource;
use App\Models\Report;
use App\Support\Tereni\ReportNotifier;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EditReport extends EditRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_public')
                ->label('Javna strana')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn (Report $record): string => route('tereni.court', $record->court))
                ->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }

    /**
     * Persist edits, but route any status change through the model so it lands
     * in the public timeline - and notify the reporter afterwards.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Report $record */
        $note = $data['status_note'] ?? null;
        unset($data['status_note']); // not a column

        $newStatus = ReportStatus::from($data['status']);
        $statusChanged = $record->status !== $newStatus;
        unset($data['status']); // applied via changeStatus() below

        $record->fill($data)->save();

        if ($statusChanged) {
            $record->changeStatus($newStatus, $note ?: null, Auth::user());
            app(ReportNotifier::class)->statusChanged($record);
        }

        return $record;
    }
}
