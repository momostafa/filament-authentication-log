<?php

namespace Tapp\FilamentAuthenticationLog\RelationManagers;

use Rappasoft\LaravelAuthenticationLog\Models\AuthenticationLog;
use Filament\Facades\Filament;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class AuthenticationLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'authentications';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('filament-authentication-log::filament-authentication-log.table.heading');
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->orderBy(config('filament-authentication-log.sort.column'), config('filament-authentication-log.sort.direction')))
            ->columns([
                Tables\Columns\TextColumn::make('authenticatable_id')
                    ->label(trans('filament-authentication-log::filament-authentication-log.column.authenticatable'))
                    ->formatStateUsing(function (?string $state, Model $record) {
                        if (! $record->authenticatable_id) {
                            return new HtmlString('&mdash;');
                        }

                        return new HtmlString('<a href="'
                        .route('filament.'
                        .Filament::getCurrentPanel()->getId()
                        .'.resources.'
                        .Str::plural((Str::lower(class_basename($record->authenticatable::class))))
                        .'.edit', ['record' => $record->authenticatable_id])
                        .'" class="inline-flex items-center justify-center hover:underline focus:outline-none focus:underline filament-tables-link text-primary-600 hover:text-primary-500 text-sm font-medium filament-tables-link-action">'.$record->authenticatable->username.'</a>');
                    })
                    ->sortable(),
                    Tables\Columns\TextColumn::make('ip_address')
                    ->label(trans('filament-authentication-log::filament-authentication-log.column.ip_address'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('country')
                ->sortable()
                ->searchable(),
                Tables\Columns\TextColumn::make('city')
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('state_name')
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('postal_code')
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user_agent')
                ->label(trans('filament-authentication-log::filament-authentication-log.column.user_agent'))
                ->searchable()
                ->sortable()
                ->limit(50)
                ->tooltip(function (TextColumn $column): ?string {
                    $state = $column->getState();

                    if (strlen($state) <= $column->getCharacterLimit()) {
                        return null;
                    }

                    return $state;
                }),
                Tables\Columns\TextColumn::make('timezone')
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('currency')
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true),    
                Tables\Columns\TextColumn::make('location')
                ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('login_at')
                ->label(trans('filament-authentication-log::filament-authentication-log.column.login_at'))
                ->dateTime()
                ->sortable(),
            Tables\Columns\IconColumn::make('login_successful')
                ->label(trans('filament-authentication-log::filament-authentication-log.column.login_successful'))
                ->boolean()
                ->sortable(),
            Tables\Columns\TextColumn::make('logout_at')
                ->label(trans('filament-authentication-log::filament-authentication-log.column.logout_at'))
                ->dateTime()
                ->sortable(),
            Tables\Columns\IconColumn::make('cleared_by_user')
                ->label(trans('filament-authentication-log::filament-authentication-log.column.cleared_by_user'))
                ->boolean()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('country')
                ->label('Country')
                ->options(function () {
                    return AuthenticationLog::get()->pluck('country', 'country');
                })
                ->searchable()
                ->preload()
                ->visible(fn (AuthenticationLog $record): bool => $record->country != null),
                Filter::make('login_successful')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('login_successful', true)),
                Filter::make('login_at')
                    ->form([
                        DatePicker::make('login_from'),
                        DatePicker::make('login_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['login_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('login_at', '>=', $date),
                            )
                            ->when(
                                $data['login_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('login_at', '<=', $date),
                            );
                    }),
                Filter::make('cleared_by_user')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->where('cleared_by_user', true)),
            ])
            ->headerActions([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }

    protected function canCreate(): bool
    {
        return false;
    }

    protected function canEdit(Model $record): bool
    {
        return false;
    }

    protected function canDelete(Model $record): bool
    {
        return false;
    }
}
