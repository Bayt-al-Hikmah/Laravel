<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class App1Controller extends Controller
{
    public function index(){
        $data = [
            'username' => 'Alice',
            'age' => 25
        ];
        
        return view('app1.index', $data);

    }
}
