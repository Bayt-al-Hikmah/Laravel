<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class App2Controller extends Controller
{
    public function index($role = "user"){
        
        
        return view('app2.index', [
            "role" => $role
        ]);

    }
}
