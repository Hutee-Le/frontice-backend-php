<?php

namespace App\Http\Response;

use Illuminate\Http\JsonResponse;
use Response;

class ApiResponse
{
    private int $code = 200;
    private mixed $message;
    private mixed $data;
    public function __construct($message = null, $data = null, $code = 200)
    {
        $this->message = $message ?? '';
        $this->data = $data ?? null;
        $this->code = $code;
    }
    public function __invoke(): JsonResponse
    {
        if ($this->data)
            return response()->json([
                'code' => $this->code,
                'message' => $this->message,
                'data' => $this->data,
            ], $this->code);
        return response()->json([
            'code' => $this->code,
            'message' => $this->message,
        ], $this->code);
    }
    public static function OK($data = [], $message = 'success', $isExpired = 'a')
    {
        if ($data) {
            if (is_bool($isExpired)) {
                return response()->json([
                    'code' => 200,
                    'message' => $message,
                    'data' => $data,
                    'isExpired' => $isExpired
                ], 200);
            }
            return response()->json([
                'code' => 200,
                'message' => $message,
                'data' => $data,
            ], 200);
        }
        return response()->json([
            'code' => 200,
            'message' => $message,
            'data' => [],
        ], 200);
    }
    public static function ERROR($message = null)
    {
        return response()->json([
            'code' => 400,
            'message' => $message ?? 'ERROR',
        ], 400);
    }
    public static function NOT_FOUND(string $detail = null): JsonResponse
    {
        return response()->json([
            'code' => 404,
            'message' => 'NOT FOUND' . ($detail !== null ? ': ' . $detail : ''),
        ], 404);
    }
    public static function BAD_REQUEST(string|array $detail = null): JsonResponse
    {
        if (is_array($detail)) {
            return response()->json(array_merge([
                'code' => 400,
            ], $detail), 400);
        } else
            return response()->json([
                'code' => 400,
                'message' => 'BAD REQUEST' . ($detail !== null ? ': ' . $detail : ""),
            ], 400);
    }
    public static function UNAUTHORIZED(string|array $detail = null): JsonResponse
    {
        if (is_array($detail)) {
            return response()->json(array_merge([
                'code' => 401,
            ], $detail), 401);
        } else
            return response()->json([
                'code' => 401,
                'message' => 'UNAUTHORIZED' . ($detail !== null ? ': ' . $detail : ""),
            ], 401);
    }
    public static function FORBIDDEN(string|array $detail = null): JsonResponse
    {
        if (is_array($detail)) {
            return response()->json(array_merge([
                'code' => 403,
            ], $detail), 403);
        } else
            return response()->json([
                'code' => 403,
                'message' => 'FORBIDDEN' . ($detail !== null ? ': ' . $detail : ""),
            ], 403);
    }

    public function setResponse($message = null, $data = null, $code = 200)
    {
        $this->message = $message ?? '';
        $this->data = $data ?? null;
        $this->code = $code;
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }
}
