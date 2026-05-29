<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                $value = $this->sanitize($value);
            }
        });

        $request->merge($input);

        return $next($request);
    }

    /**
     * Sanitize a string from disruptive Unicode characters (Zalgo) and normalize it.
     */
    private function sanitize(string $value): string
    {
        // 1. Normalize Unicode to NFC (Canonical Composition)
        if (class_exists('Normalizer')) {
            $value = \Normalizer::normalize($value, \Normalizer::FORM_C) ?: $value;
        }

        // 2. Strip excessive Unicode combiners (Zalgo text protection)
        // This regex targets the Unicode category "Mark" (\p{M}) which are used for accents/combiners.
        // We allow some but prevent hundreds of them. 
        // A simple approach is to remove excessive marks.
        // This regex removes any more than 3 consecutive marks.
        $value = preg_replace('/(\p{M}){3,}/u', '$1$1$1', $value);

        // 3. Optional: Remove non-printable characters except newlines/tabs
        $value = preg_replace('/[^\x20-\x7E\t\n\r\x{00A0}-\x{FFFF}]/u', '', $value);

        return trim($value);
    }
}
