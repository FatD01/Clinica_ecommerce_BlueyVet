<?php

namespace App\Filament\Resources\VeterinarianResource\Pages;

use App\Filament\Resources\VeterinarianResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVeterinarian extends EditRecord
{
    protected static string $resource = VeterinarianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
