<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnalyticsUsersController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        echo "HOLA";
    }
}
