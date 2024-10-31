<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\ResponseResource;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();

        return new ResponseResource(true, 'User list', $users, [
            'total_users' => $users->count()
        ]);
    }
}
