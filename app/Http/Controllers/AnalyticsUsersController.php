<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Analytics\TwitchApi;

class AnalyticsUsersController extends Controller
{

    public function __invoke(Request $request)
    {
        include_once __DIR__ . '/../../Services/users.php';
    }
}
