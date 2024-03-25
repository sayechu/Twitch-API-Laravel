<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnalyticsTopsOfTheTopsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        include_once __DIR__ . '/../../Services/topsofthetops.php';
    }
}
