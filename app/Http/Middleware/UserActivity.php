<?php

namespace App\Http\Middleware;

use App\Models\User\User;
use Auth;
use Cache;
use Closure;
use Illuminate\Http\Request;

class UserActivity
{
    /**
     * Handle an incoming request.
     *
     * * @param  \Closure  $next
     *
     * * @return mixed
     * */
    public function handle(Request $request, Closure $next)
    {
        // Refresh at most every 2 minutes: the online flag lasts that long, so there's no need
        // to write last_seen to the database on every request.
        if (Auth::check() && !Cache::has('user-is-online-'.Auth::user()->id)) {
            Cache::put('user-is-online-'.Auth::user()->id, true, now()->addMinutes(2));
            User::where('id', Auth::user()->id)->update(['last_seen' => now()]);
        }

        return $next($request);
    }
}
