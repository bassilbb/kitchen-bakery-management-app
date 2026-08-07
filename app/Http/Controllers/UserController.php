<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withCount('orders')->orderBy('role')->orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', 'in:admin,staff'],
            'department' => ['nullable', 'in:kitchen,bakery'],
        ]);

        if ($data['role'] === User::ROLE_ADMIN) {
            $user->update([
                'role' => User::ROLE_ADMIN,
                'department' => null,
            ]);
        } else {
            // Staff always belong to a department.
            $data = $request->validate([
                'department' => ['required', 'in:kitchen,bakery'],
            ]);

            $user->update([
                'role' => User::ROLE_STAFF,
                'department' => $data['department'],
            ]);
        }

        return back()->with('success', 'User permissions updated.');
    }
}
