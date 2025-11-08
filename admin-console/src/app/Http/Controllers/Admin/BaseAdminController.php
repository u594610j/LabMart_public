<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

abstract class BaseAdminController extends Controller
{
    protected function admin()
    {
        return Auth::guard('admin')->user();
    }

    protected function isAdminAuthenticated(): bool
    {
        return Auth::guard('admin')->check();
    }
}
