<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::orderBy('name')->paginate(10);

        return view('admin.positions.index', compact('positions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:positions,name'],
        ]);

        Position::create($data);

        return back()->with('success', 'Position berhasil ditambahkan.');
    }

    public function update(Request $request, Position $position)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('positions', 'name')->ignore($position->id)],
        ]);

        $position->update($data);

        return back()->with('success', 'Position berhasil diperbarui.');
    }

    public function destroy(Position $position)
    {
        $position->delete();

        return back()->with('success', 'Position berhasil dihapus.');
    }
}
