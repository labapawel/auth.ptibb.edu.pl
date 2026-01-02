<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class VerifyVpnUser extends Controller
{
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            "uid"=>["required","email"],
            "password"=>["required"],
        ]);
        $credentials = [
            'mail' => $validated['uid'],
            'password' => $validated['password'],
        ];
        if (Auth::once($credentials)) {
        return response()->json(['status' => 'ok'],200);
        }else{
        return response()->json(['status' => 'not found'],401);
        }

    }
}
