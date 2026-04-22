<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiTrait
{
    /**
     * Return a success response with message.
     */
    public function successResponse(string $message, int $statusCode = 200): JsonResponse
    {
        return response()->json(
            [
                'message' => $message,
                'errors' => (object) [],
                'data' => (object) [],
            ],
            $statusCode
        );
    }

    /**
     * Return an error response with errors array.
     */
    public function errorResponse($errors, string $message = '', int $statusCode = 400): JsonResponse
    {
        return response()->json(
            [
                'message' => $message,
                'errors' => $errors,
                'data' => (object) [],
            ],
            $statusCode
        );
    }

    /**
     * Return a data response with data object.
     */
    public function dataResponse($data, string $message = '', int $statusCode = 200): JsonResponse
    {
        return response()->json(
            [
                'message' => $message,
                'errors' => (object) [],
                'data' => (object) $data,
            ],
            $statusCode
        );
    }

    /**
     * Handle authorization failures and return JSON response.
     *
     * This should be called in exception handler or catch blocks.
     */
    public function unauthorizedResponse(string $message = 'This action is unauthorized.'): JsonResponse
    {
        return $this->errorResponse(
            ['authorization' => [$message]],
            'Unauthorized',
            403
        );
    }
}
