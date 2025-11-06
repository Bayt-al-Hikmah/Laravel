<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelloController extends Controller
{
    public function showHello()
    {
        return 'Hello, World!';
    }

    public function personalGreeting(Request $request, $name)
    { 
        $greeting = $request->query('greet', 'Hello'); 
        return "{$greeting}, {$name}!";
    }
}