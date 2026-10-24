<?php

namespace App\Filament\Clusters\Reg\Resources\Students\Schemas\Tabs;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Tabs\Tab;

class NotesTab
{
    public static function make(): Tab
    {
        return Tab::make(__('reg.notes'))
            ->schema([
                SpatieMediaLibraryFileUpload::make('attachments')
                    ->multiple()
                    ->label(__('reg.attachments'))
                    ->collection('attachments'),

                MarkdownEditor::make('note'),
            ]);
    }
}
