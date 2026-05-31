<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('locale')) {
            app()->setLocale(session('locale'));
        }

        \Carbon\Carbon::setLocale(app()->getLocale());

        $response = $next($request);

        if (
            app()->getLocale() === 'en'
            && method_exists($response, 'getContent')
            && str_contains((string) $response->headers->get('Content-Type'), 'text/html')
        ) {
            $phrases = trans('static');

            if (is_array($phrases) && $phrases !== []) {
                $response->setContent(strtr($response->getContent(), $phrases));
            }
        }

        return $response;
    }
}
