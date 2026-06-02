<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SchedulebullService;
use App\Services\CarInfoTokenService;
use Illuminate\Http\Request;

class CarInfoController extends Controller
{
    public function show(Request $request, SchedulebullService $schedulebull, CarInfoTokenService $tokenService)
    {
        $vnr = trim((string) $request->input('vnr', $request->query('vnr')));
        if ($vnr === '') {
            return $this->withRotatedToken(
                $request,
                $tokenService,
                response()->json(['message' => 'Missing vnr'], 422)
            );
        }

        $result = $schedulebull->carInfo($vnr);
        if (!$result['ok']) {
            return $this->withRotatedToken(
                $request,
                $tokenService,
                response()->json(['message' => $result['message']], $result['status'])
            );
        }

        return $this->withRotatedToken(
            $request,
            $tokenService,
            response()->json($result['data'])
        );
    }

    private function withRotatedToken(
        Request $request,
        CarInfoTokenService $tokenService,
        \Illuminate\Http\JsonResponse $response
    ): \Illuminate\Http\JsonResponse
    {
        $token = $tokenService->issue($request);
        return $response->header('X-Car-Info-Token', $token);
    }
}
