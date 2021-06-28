<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class Lang
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = $_GET['lang'] ?? 'NONE';

        if ($locale == "NONE") {
            if (session()->has('lang')) {
                App::setLocale(session()->get('lang'));
            } else {
                session()->put('lang', app()->getLocale());
                App::setLocale(app()->getLocale());
            }
            return $next($request);
        }

        session()->put('lang', $locale);
        App::setLocale($locale);
        return $next($request);
    }
}
