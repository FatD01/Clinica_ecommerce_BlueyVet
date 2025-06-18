<?php

namespace App\Filament\Resources\ScheduleBlockResource\Pages;

use App\Filament\Resources\ScheduleBlockResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScheduleBlocks extends ListRecords
{
    protected static string $resource = ScheduleBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
