<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyticsUsersRequest;
use Illuminate\Http\Request;
use App\Services\TwitchApi;

class AnalyticsUsersController extends Controller
{
    public function __invoke(AnalyticsUsersRequest $request)
    {
        if ($request->has('id')) {
            $userId = $request->input('id');

            $client_id = '970almy6xw98ruyojcwqpop0p0o5a2';
            $client_secret = 'yl0nqzjjnadd8wl7zilpr9pzuh979j';

            $twitchApi = new TwitchApi($client_id, $client_secret);
            $userInfo = $twitchApi->getInfoUser($userId);

            return response()->json($userInfo);
        } else {
            return response()->json(['error' => 'No user ID provided in the request.'], 400);
        }
    }
}
