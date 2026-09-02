<?php

namespace App\Support;

use Filament\Actions\Action;

class Money
{
    /**
     * Level 1: evaluate a math expression ("15000*2+5000" -> "35000") typed into a money input.
     * Passthrough when there's no operator — normal numbers keep their usual validation path.
     */
    public static function evaluate(?string $value): ?string
    {
        if ($value === null || $value === '' || ! preg_match('/[+\-*\/]/', $value)) {
            return $value;
        }

        $expr = preg_replace('/\s+/', '', $value);

        // ponytail: eval is safe ONLY because of this strict whitelist (digits + arithmetic only);
        // anything else falls through untouched so Filament's numeric validation rejects it
        if (! preg_match('/^[0-9+\-*\/().]+$/', $expr)) {
            return $value;
        }

        try {
            $result = eval("return {$expr};");
        } catch (\Throwable) {
            return $value;
        }

        return is_numeric($result) ? (string) $result : $value;
    }

    /**
     * Level 2: calculator keypad as a suffix action (the small icon inside a money input).
     */
    public static function calculatorAction(): Action
    {
        return Action::make('calculator')
            ->icon('heroicon-m-calculator')
            ->modalHeading('Calculator')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->modalContent(fn ($component) => view('filament.calculator', [
                'statePath' => $component->getStatePath(),
            ]));
    }
}
