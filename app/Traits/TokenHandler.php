<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

trait TokenHandler{

    private int $seconds = 120;
    private string $table = 'password_resets';
    private string $indexKey = 'email';

    /**
     * Generate a token
     *
     * @return string
     */
    protected function generateToken(): string
    {
        return Str::random(rand(8 , 16));
    }


    /**
     * Store a token in the DB
     *
     * @param string $table
     * @param string $indexKey
     * @param mixed $indexValue
     * @return string
     */
    protected function storeToken(string $table, string $indexKey , mixed $indexValue): string
    {
        DB::table($table)->where($indexKey , $indexValue)->delete();
        $token = $this->generateToken();
        DB::table($table)->insert([$indexKey => $indexValue, 'token' => Hash::make($token)]);
        return $token;
    }


    /**
     * Try to get the valid token
     *
     * @param int $seconds
     * @param string $table
     * @param string $indexKey
     * @param mixed $indexValue
     * @param string $requestToken
     * @return void
     */
    private function getToken(int $seconds, string $table, string $indexKey, mixed $indexValue , string $requestToken)
    {
        $timeBeforeExpire = Carbon::now()->subSeconds($seconds);

        $query = DB::table($table)
            ->where($indexKey , $indexValue)
            ->where('created_at' , '>' , $timeBeforeExpire)
            ->latest();
        $reset = $query->first();

        if(!$reset || !Hash::check($requestToken , $reset->token))
            abort(401 , 'Invalid or expired token');

        $query->delete();
    }

}
