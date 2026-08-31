<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ManualController extends Controller
{
    public function index()
    {
        return view('admin.manual.index');
    }

    public function bangla()
    {
        return view('admin.manual.bangla');
    }
}
