<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnerHeader
{
    private const HEADER_NAME = 'X-Owner';

    public function handle(Request $request, Closure $next): Response
    {
        $owner = $request->header(self::HEADER_NAME);

        if (! is_string($owner) || ! Str::isUuid($owner)) {
            return new JsonResponse([
                'message' => sprintf('The %s header must contain a valid UUID.', self::HEADER_NAME),
            ], Response::HTTP_BAD_REQUEST);
        }

        return $next($request);
    }
}
