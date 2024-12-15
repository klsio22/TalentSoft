<?php

namespace App\Controllers;

use Core\Http\Controllers\Controller;
use Core\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request): void
    {
        $title = 'Home';
        $this->render('home/index', compact('title'));
    }

    public function admin(): void
    {
        $this->render('home/admin/index');
    }
}
