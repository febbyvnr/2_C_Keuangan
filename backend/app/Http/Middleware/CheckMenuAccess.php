<?php

namespace App\Http\Middleware;

use App\Services\RbacService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuAccess
{
    public function __construct(
        protected RbacService $rbacService
    ) {
    }

    public function handle(Request $request, Closure $next, string $menuName): Response
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthenticated();
        }

        if (!$this->rbacService->hasMenuAccess($user, $menuName)) {
            return $this->forbidden("Akses ditolak. User tidak memiliki permission {$menuName}.");
        }

        return $next($request);
    }

    private function unauthenticated(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated.',
        ], 401);
    }

    private function forbidden(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 403);
    }
}