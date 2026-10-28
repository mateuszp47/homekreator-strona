<?php
namespace App\Http\Middleware;
use App\Support\SiteSettings;
use Closure;
use Illuminate\Http\Request;
class ApplySiteSettings {public function handle(Request $request,Closure $next){app(SiteSettings::class)->apply();return $next($request);}}
