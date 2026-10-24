<?php

namespace App\Filament\Clusters\Reg\Resources\Students\Pages;

use App\Exports\StudentExport;
use App\Filament\Clusters\Reg\Resources\Students\StudentsResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            // Action::make('xls')
            //     ->label(__('XLS'))
            //     ->icon('heroicon-o-arrow-down-tray')
            //     ->color('success')
            //     ->action(function () {
            //         $filteredQuery = $this->getFilteredTableQuery();

            //         return Excel::download(
            //             new StudentExport($filteredQuery),
            //             'Students_' . now()->format('Y-m-d') . '.xlsx'
            //         );
            //     }),
            // Action::make('html')
            //     ->label(__('HTML'))
            //     ->icon('heroicon-o-arrow-down-tray')
            //     ->color('success')
            //     ->action(function () {
            //         $filteredQuery = $this->getFilteredTableQuery();

            //         return Excel::download(
            //             new StudentExport($filteredQuery),
            //             'Students_' . now()->format('Y-m-d') . '.xlsx'
            //         );
            //     }),
        ];
    }
}
