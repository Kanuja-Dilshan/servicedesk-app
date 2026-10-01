<?php

namespace App\Http\Controllers;

use App\Models\{Ticket, TicketCategory};
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories.index', ['categories' => TicketCategory::orderBy('name')->get()]);
    }

    public function store(Request $r)
    {
        $data = $r->validate(['name' => 'required|string|max:100|unique:ticket_categories,name']);
        TicketCategory::create($data);

        return back()->with('status', 'Category added.');
    }

    public function destroy(TicketCategory $category)
    {
        if (Ticket::where('category_id', $category->id)->exists()) {
            return back()->withErrors(['name' => "\"{$category->name}\" is used by existing tickets and can't be deleted."]);
        }
        $category->delete();

        return back()->with('status', 'Category deleted.');
    }
}