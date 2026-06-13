<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponds
{
    // ─────────────────────────────────────────
    // 200 OK with data
    // ─────────────────────────────────────────

    protected function ok(
        mixed  $data    = null,
        string $message = 'OK'
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], 200);
    }

    // ─────────────────────────────────────────
    // 201 Created
    // ─────────────────────────────────────────

    protected function created(
        mixed  $data,
        string $message = 'Created successfully'
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], 201);
    }

    // ─────────────────────────────────────────
    // 400 / 422 Error
    // ─────────────────────────────────────────

    protected function error(
        string $message,
        int    $status = 422,
        array  $errors = []
    ): JsonResponse {
        $body = ['success' => false, 'message' => $message];
        if (!empty($errors)) $body['errors'] = $errors;
        return response()->json($body, $status);
    }

    // ─────────────────────────────────────────
    // 401 Unauthorized
    // ─────────────────────────────────────────

    protected function unauthorized(
        string $message = 'Unauthenticated'
    ): JsonResponse {
        return response()->json(['success' => false, 'message' => $message], 401);
    }

    // ─────────────────────────────────────────
    // 403 Forbidden
    // ─────────────────────────────────────────

    protected function forbidden(
        string $message = 'Forbidden'
    ): JsonResponse {
        return response()->json(['success' => false, 'message' => $message], 403);
    }

    // ─────────────────────────────────────────
    // 404 Not Found
    // ─────────────────────────────────────────

    protected function notFound(
        string $message = 'Resource not found'
    ): JsonResponse {
        return response()->json(['success' => false, 'message' => $message], 404);
    }
}