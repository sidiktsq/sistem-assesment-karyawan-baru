<?php

namespace App\Filament\Resources\CandidateAssessments;

use App\Filament\Resources\CandidateAssessments\Pages\CreateCandidateAssessment;
use App\Filament\Resources\CandidateAssessments\Pages\EditCandidateAssessment;
use App\Filament\Resources\CandidateAssessments\Pages\ListCandidateAssessments;
use App\Filament\Resources\CandidateAssessments\Schemas\CandidateAssessmentForm;
use App\Filament\Resources\CandidateAssessments\Tables\CandidateAssessmentsTable;
use App\Models\CandidateAssessment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CandidateAssessmentResource extends Resource
{
    protected static ?string $model = CandidateAssessment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'access_token';

    public static function form(Schema $schema): Schema
    {
        return CandidateAssessmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CandidateAssessmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCandidateAssessments::route('/'),
            'create' => CreateCandidateAssessment::route('/create'),
            'edit' => EditCandidateAssessment::route('/{record}/edit'),
        ];
    }
}
