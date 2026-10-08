<?php

namespace App\Http\Controllers;

use App\Models\{Department, User};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index()
    {
        return view('admin.users.index', ['users' => User::with('department')->orderBy('name')->paginate(20)]);
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User, 'departments' => Department::all()]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:employee,support,technician,admin',
            'department_id' => 'nullable|exists:departments,id',
        ]);
        User::create($data);

        return redirect()->route('admin.users.index')->with('status', 'User created.');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', ['user' => $user, 'departments' => Department::all()]);
    }

    public function update(Request $r, User $user)
    {
        $data = $r->validate([
            'name' => 'required|string|max:100',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:employee,support,technician,admin',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        if ($user->id === $r->user()->id && $data['role'] !== 'admin') {
            return back()->withErrors(['role' => 'You cannot remove your own admin role.'])->withInput();
        }
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $user->update($data);

        return redirect()->route('admin.users.index')->with('status', 'User updated.');
    }
}