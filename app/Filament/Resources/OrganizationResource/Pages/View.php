<?php

namespace App\Filament\Resources\OrganizationResource\Pages;

use App\Filament\Resources\OrganizationResource;
use Filament\Resources\Pages\ViewRecord;

class View extends ViewRecord
{
    protected static string $resource = OrganizationResource::class;

    public static function getNavigationLabel(): string
    {
        return '详情';
    }
}
