<?php

declare(strict_types=1);

namespace ITHilbert\Sitemap\Enums;

enum Changefreq: string
{
    case Always  = 'always';
    case Hourly  = 'hourly';
    case Daily   = 'daily';
    case Weekly  = 'weekly';
    case Monthly = 'monthly';
    case Yearly  = 'yearly';
    case Never   = 'never';

    public static function fromInput(string $value): self
    {
        $normalized = strtolower(trim($value));

        return self::tryFrom($normalized)
            ?? throw new \InvalidArgumentException(
                "Ungültiger changefreq-Wert: '{$value}'. Erlaubt: "
                . implode(', ', array_column(self::cases(), 'value'))
            );
    }
}
