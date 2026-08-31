<?php

declare(strict_types=1);

namespace App\Filament\Schemas\Components;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class FaqsSection
{
    public static function make(bool $display = false): Section
    {
        return Section::make('FAQs')
            ->collapsible()
            ->collapsed(fn (string $operation): bool => $operation !== 'create')
            ->schema(function () use ($display) {
                $schema = [
                    Repeater::make('faqs')
                        ->relationship()
                        ->defaultItems(0)
                        ->orderColumn('position')
                        ->schema([
                            TextInput::make('question')
                                ->required(),

                            Textarea::make('answer')
                                ->rows(3)
                                ->required(),
                        ])
                        ->columnSpanFull()
                        ->addActionLabel('Add FAQ')
                        ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                        ->collapsible(),
                ];

                if ($display) {
                    $schema[] = Select::make('faq_display')
                        ->label('FAQ Position')
                        ->options([
                            'top' => 'Above content',
                            'bottom' => 'Below content',
                        ])
                        ->nullable();
                }

                return $schema;
            });
    }
}
