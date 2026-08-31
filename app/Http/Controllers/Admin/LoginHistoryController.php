<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;

class LoginHistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage users');
    }

    public function index()
    {
        $logins = LoginHistory::with('user')->latest('logged_in_at')->paginate(30);

        return view('admin.login-history.index', compact('logins'));
    }
}
