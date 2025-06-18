<?php

namespace App\Filament\Resources\BlockedDayResource\Pages;

use App\Filament\Resources\BlockedDayResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBlockedDays extends ListRecords
{
    protected static string $resource = BlockedDayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
