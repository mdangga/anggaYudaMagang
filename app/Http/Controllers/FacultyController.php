<?php

namespace App\Http\Controllers;

use App\Http\Requests\Faculty\storeFacultyRequest;
use App\Models\Faculties;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FacultyController extends Controller
{
    public function index()
    {
        $faculties = Faculties::paginate(10);

        return Inertia::render('Faculties/Index', [
            'faculties' => $faculties,
        ]);
    }

    public function create()
    {
        return Inertia::render('Faculties/Add');
    }

    public function store(storeFacultyRequest $request)
    {
        $validated = $request->validated();

        Faculties::create($validated);

        return redirect()->route('faculty.index')->with('success', 'Fakultas berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $faculty = Faculties::findOrFail($id);

        return Inertia::render('Faculties/Edit', [
            'faculty' => $faculty,
        ]);
    }

    public function update(Request $request, $id)
    {
        $faculty = Faculties::findOrFail($id);
        $faculty->name_faculty = $request->name_faculty;
        $faculty->save();

        return redirect()->route('faculty.index')->with('success', 'Fakultas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $faculty = Faculties::findOrFail($id);
        $faculty->delete();

        return redirect()->route('faculty.index')->with('success', 'Fakultas berhasil dihapus.');
    }
}
