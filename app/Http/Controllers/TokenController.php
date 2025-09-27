<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Ldap\User;
class TokenController extends Controller
{
    
    #creating Token and storing in db
    public static function store(User $ldapUser)
    {
        $token = Str::random(80);
        DB::table('password_reset_tokens')->insert([
            'token' => $token,
                'email' => $ldapUser->getAttributes()['uid'][0],
                'created_at'=> now(),
           ]);
            $resetLink = route('password.reset', [
                'token' => $token,
                'email' => $ldapUser->getAttributes()['uid'][0],
                ]);


        return $resetLink;
    }
    #finding token in db
    public static function findToken(Request $request){

        $tokenData = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();
        return $tokenData;
    }

    #deleting token from db
    public static function deleteToken($email){
        DB::table('password_reset_tokens')->where('email', $email)->delete();
    }

    #deleting expired tokens from db
    public static function deleteExpiredTokens(){
        $expiredTokens = DB::table('password_reset_tokens')
            ->where('created_at', '<', now()->subMinutes(60))
            ->delete();
        return $expiredTokens;
    }

    #checking if token is still valid
    public static function isTokenValid($tokenData){
         // Czas ważności tokena to 60 minut.
        if (now()->diffInMinutes($tokenData->created_at) > 60) {
            return false;
        }
        return true;
    }

}
