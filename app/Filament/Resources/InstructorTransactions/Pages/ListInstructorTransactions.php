<?php

namespace App\Filament\Resources\InstructorTransactions\Pages;

use App\Filament\Resources\InstructorTransactions\InstructorTransactionResource;
use Filament\Resources\Pages\ListRecords;

class ListInstructorTransactions extends ListRecords
{
    protected static string $resource = InstructorTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
