<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([]);
    }
}
