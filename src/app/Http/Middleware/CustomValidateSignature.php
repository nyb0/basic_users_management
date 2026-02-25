<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Middleware\ValidateSignature;

class CustomValidateSignature extends ValidateSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  array|null  $args
     * @return \Illuminate\Http\Response
     */
    public function handle($request, Closure $next, ...$args)
    {
        [$relative, $ignore] = $this->parseArguments($args);

        if ($request->hasValidSignatureWhileIgnoring($ignore, ! $relative)) {
            return $next($request);
        }

        $message = 'Oops! Your link doesn\'t seem to be working or it has been expired.';

        if ($request->expectsJson()) {
            return response()->json([
                'signatureError' => $message,
            ], 401);
        }
        return redirect()->route('welcome')->with([
            'signatureError' => $message,
            'showVerificationModal' => true,
            ]);
    }
}
