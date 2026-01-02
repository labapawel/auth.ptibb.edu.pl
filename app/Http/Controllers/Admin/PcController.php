<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class PcController extends Controller
{
    public function index()
    {
        return view('admin.pc');
    }
}
