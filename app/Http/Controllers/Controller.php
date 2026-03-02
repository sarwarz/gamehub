<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    protected function success($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    protected function error(string $message = 'Something went wrong', int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'status'  => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
