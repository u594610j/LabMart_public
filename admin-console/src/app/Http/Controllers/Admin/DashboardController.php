<?php

namespace App\Http\Controllers\Admin;

class DashboardController extends BaseAdminController
{
    public function index()
    {
        return view('admin.dashboard');
    }
}
