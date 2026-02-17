<?php

namespace App\Support;

use NumberFormatter;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Currencies;

class CurrencyCatalog
{
    protected static ?array $options = null;

    public static function options(): array
    {
        if (self::$options !== null) {
            return self::$options;
        }

        $countries = Countries::getNames('en');
        $currencyNames = Currencies::getNames('en');
        $map = [];

        foreach ($countries as $countryCode => $countryName) {
            $formatter = new NumberFormatter('en_' . $countryCode, NumberFormatter::CURRENCY);
            $currencyCode = strtoupper((string) $formatter->getTextAttribute(NumberFormatter::CURRENCY_CODE));

            if (!preg_match('/^[A-Z]{3}$/', $currencyCode)) {
                continue;
            }

            $map[$currencyCode] ??= ['countries' => []];
            $map[$currencyCode]['countries'][] = $countryName;
        }

        $options = [];

        foreach ($currencyNames as $code => $name) {
            $countriesForCurrency = array_values(array_unique($map[$code]['countries'] ?? []));
            sort($countriesForCurrency);

            $countriesPreview = implode(', ', array_slice($countriesForCurrency, 0, 3));
            if (count($countriesForCurrency) > 3) {
                $countriesPreview .= ' +' . (count($countriesForCurrency) - 3) . ' more';
            }

            $symbol = self::symbol($code);
            $label = sprintf('%s (%s %s)', $name, $code, $symbol);
            if ($countriesPreview !== '') {
                $label .= ' - ' . $countriesPreview;
            }

            $options[$code] = $label;
        }

        ksort($options);
        self::$options = $options;

        return self::$options;
    }

    public static function symbol(?string $currencyCode): string
    {
        $currencyCode = strtoupper((string) $currencyCode);
        if (!preg_match('/^[A-Z]{3}$/', $currencyCode)) {
            return (string) $currencyCode;
        }

        try {
            return Currencies::getSymbol($currencyCode, 'en') ?: $currencyCode;
        } catch (\Throwable) {
            return $currencyCode;
        }
    }
}

