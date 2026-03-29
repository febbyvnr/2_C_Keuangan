<?php

namespace App\Http\Middleware;

use App\Services\RbacService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckJabatan
{
    public function __construct(
        protected RbacService $rbacService
    ) {
    }

    public function handle(Request $request, Closure $next, string $jabatanName): Response
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthenticated();
        }

        if (!$this->rbacService->hasJabatan($user, $jabatanName)) {
            return $this->forbidden("Akses ditolak. User tidak memiliki jabatan {$jabatanName}.");
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