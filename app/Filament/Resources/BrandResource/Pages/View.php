<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use Filament\Resources\Pages\ViewRecord;

class View extends ViewRecord
{
    protected static string $resource = BrandResource::class;

    public static function getNavigationLabel(): string
    {
        return '详情';
    }
}