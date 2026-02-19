<?php

namespace App\Filament\Resources\AllocationResource\Pages;

use App\Filament\Resources\AllocationResource;
use Filament\Actions;
use App\Filament\Resources\Pages\BaseCreateRecord as CreateRecord;

class CreateAllocation extends CreateRecord
{
    protected static string $resource = AllocationResource::class;
}
