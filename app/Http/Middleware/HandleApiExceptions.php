<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Symfony\Component\HttpFoundation\Response;

class HandleApiExceptions
{
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Token has expired'], Response::HTTP_UNAUTHORIZED);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'Token is invalid'], Response::HTTP_UNAUTHORIZED);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token error: ' . $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        } catch (RequiredConstraintsViolated $e) {
            return response()->json(['message' => 'Token validation error: ' . $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => 'Invalid argument: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            return response()->json(['message' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
