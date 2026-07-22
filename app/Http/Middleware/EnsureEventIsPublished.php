<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\EventStatus;
use App\Models\Event;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EnsureEventIsPublished
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $event = $request->route('event');

        if (! $event instanceof Event || $event->status !== EventStatus::Published) {
            throw new NotFoundHttpException;
        }

        return $next($request);
    }
}
