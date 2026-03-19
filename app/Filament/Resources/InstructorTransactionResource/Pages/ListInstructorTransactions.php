<?php

namespace App\Filament\Resources\InstructorTransactionResource\Pages;

use App\Filament\Resources\InstructorTransactionResource;
use Filament\Resources\Pages\ListRecords;

class ListInstructorTransactions extends ListRecords
{
    protected static string $resource = InstructorTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
