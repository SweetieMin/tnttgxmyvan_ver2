<?php

namespace App\Filament\Exports;

use App\Models\AcademicYear;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Number;

class AcademicYearExporter extends Exporter
{
    protected static ?string $model = AcademicYear::class;

    /**
     * @return array<int, Component>
     */
    public static function getOptionsFormComponents(): array
    {
        return [
            Select::make('format')
                ->label(__('File format'))
                ->options([
                    ExportFormat::Csv->value => 'CSV',
                    ExportFormat::Xlsx->value => 'XLSX',
                ])
                ->default(ExportFormat::Csv->value)
                ->native(false)
                ->required(),
        ];
    }

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label(__('Academic year')),
            ExportColumn::make('catechism_period')
                ->label(__('Catechism period'))
                ->formatStateUsing(fn (?string $state): string => $state ?: __('N/A')),
            ExportColumn::make('catechism_avg_score')
                ->label(__('Catechism average score'))
                ->formatStateUsing(fn (mixed $state): string => self::formatDecimal($state)),
            ExportColumn::make('catechism_training_score')
                ->label(__('Catechism training score'))
                ->formatStateUsing(fn (mixed $state): string => self::formatDecimal($state)),
            ExportColumn::make('activity_period')
                ->label(__('Activity period'))
                ->formatStateUsing(fn (?string $state): string => $state ?: __('N/A')),
            ExportColumn::make('activity_score')
                ->label(__('Activity score')),
            ExportColumn::make('status_academic_label')
                ->label(__('Status'))
                ->formatStateUsing(fn (?string $state): string => __($state ?: 'Upcoming')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your academic year export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }

    public function getFormats(): array
    {
        return [
            ($this->getOptions()['format'] ?? ExportFormat::Csv->value) === ExportFormat::Xlsx->value
                ? ExportFormat::Xlsx
                : ExportFormat::Csv,
        ];
    }

    public function getJobConnection(): ?string
    {
        return 'sync';
    }

    protected static function formatDecimal(mixed $value): string
    {
        return number_format((float) $value, 2, ',', '.');
    }
}
