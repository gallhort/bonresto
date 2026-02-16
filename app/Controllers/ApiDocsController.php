<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;

class ApiDocsController extends Controller
{
    public function index(Request $request): void
    {
        $this->render('api/docs', [
            'title' => 'API Documentation - LeBonResto',
        ]);
    }
}
