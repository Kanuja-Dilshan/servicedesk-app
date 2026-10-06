<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class AdminDepartmentController extends Controller
{
    public function index()
    {
        return view('admin.departments.index', [
            'departments' => Department::withCount('users')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate(['name' => 'required|string|max:100|unique:departments,name']);
        Department::create($data);

        return back()->with('status', 'Department added.');
    }

    public function destroy(Department $department)
    {
        if ($department->users()->exists()) {
            return back()->withErrors(['name' => "\"{$department->name}\" still has users and can't be deleted."]);
        }
        $department->delete();

        return back()->with('status', 'Department deleted.');
    }
}