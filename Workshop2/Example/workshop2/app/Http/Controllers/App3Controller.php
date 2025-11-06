<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class App3Controller extends Controller
{
    public function index()
    {
        $data = [
            'fruits' => ['Apple', 'Banana', 'Cherry', 'Mango', 'Orange']
        ];
        return view('app3.index', $data);
    }
}