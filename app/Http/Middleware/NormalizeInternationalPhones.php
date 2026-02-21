<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeInternationalPhones
{
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();
        foreach ($input as $key => $value) {
            if (!str_ends_with((string) $key, '_country_code')) {
                continue;
            }

            $phoneField = substr((string) $key, 0, -strlen('_country_code'));
            if (!array_key_exists($phoneField, $input)) {
                continue;
            }

            $phone = trim((string) ($input[$phoneField] ?? ''));
            if ($phone === '') {
                continue;
            }

            $countryCode = $this->normalizeDialCode((string) $value);
            if (str_starts_with($phone, '+')) {
                $input[$phoneField] = $this->normalizeFullPhone($phone);
                continue;
            }

            $digits = preg_replace('/\D+/', '', $phone) ?? '';
            $input[$phoneField] = trim($countryCode . $digits);
        }

        $request->merge($input);

        return $next($request);
    }

    private function normalizeDialCode(string $code): string
    {
        $digits = preg_replace('/\D+/', '', $code) ?? '';
        return $digits === '' ? '+234' : '+' . $digits;
    }

    private function normalizeFullPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        return $digits === '' ? '' : '+' . $digits;
    }
}

