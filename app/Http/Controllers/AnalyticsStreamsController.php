<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TwitchApi;

class AnalyticsStreamsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $client_id = '970almy6xw98ruyojcwqpop0p0o5a2';
        $client_secret = 'yl0nqzjjnadd8wl7zilpr9pzuh979j';

        $twitchApi = new TwitchApi($client_id, $client_secret);
        $streams = $twitchApi->getStreams();

        return response()->json($streams);
    }
}
