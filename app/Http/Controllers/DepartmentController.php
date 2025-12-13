<?php

namespace App\Http\Controllers;

use App\Http\Requests\Department\storeDepartmentRequest;
use App\Http\Requests\Department\updateDepartmentRequest;
use App\Models\Departments;
use App\Models\Faculties;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Departments::with(['Faculty:id_faculty,name_faculty'])->paginate(10);

        return Inertia::render('Departments/Index', [
            'departments' => $departments,
        ]);
    }

    public function create()
    {
        $faculties = Faculties::all();
        return Inertia::render('Departments/Add',
            [
                'faculties' => $faculties,
            ]
        );
    }

    public function store(storeDepartmentRequest $request)
    {
        $validated = $request->validated();

        Departments::create($validated);

        return redirect()->route('department.index')->with('success', 'Department created successfully.');
    }

    public function edit($id)
    {
        $department = Departments::findOrFail($id);
        $faculties = Faculties::all();

        return Inertia::render('Departments/Edit', [
            'department' => $department,
            'faculties' => $faculties,
        ]);
    }

    public function update(updateDepartmentRequest $request, $id)
    {
        $validated = $request->validated();

        $department = Departments::findOrFail($id);
        $department->update($validated);

        return redirect()->route('department.index')->with('success', 'Department updated successfully.');
    }

    public function destroy($id)
    {
        $department = Departments::findOrFail($id);
        $department->delete();

        return redirect()->route('department.index')->with('success', 'Department deleted successfully.');
    }
}
