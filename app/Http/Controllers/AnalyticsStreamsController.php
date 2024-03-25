<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnalyticsStreamsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        include_once __DIR__ . '/../../Services/streams.php';
    }
}
