<?php

namespace App\Http\Controllers;

use App\Http\Requests\Location\storeLocationRequest;
use App\Models\Categories;
use App\Models\Faculties;
use App\Models\Images;
use App\Models\Locations;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Locations::with([
            'category:id_category,name_category',
            'department:id_department,name_department,degree_level,id_faculty',
            'department.faculty:id_faculty,name_faculty',
            'images:id_image,id_location,image_path,alt_text'
        ])
            ->latest()
            ->paginate(10);

        return Inertia::render('Locations/Index', [
            'locations' => $locations
        ]);
    }


    //? method to get location data json
    public function getData()
    {
        try {
            Log::info('getData function called');

            $locations = Locations::select(
                'id_location',
                'id_category',
                'name_location',
                'description',
                'latitude',
                'longitude',
                'created_at'
            )
                ->with([
                    'category:id_category,name_category',
                    'images:id_location,image_path'
                ])
                ->whereNotNull('approved_at')
                ->get();

            Log::info('Data fetched:', ['count' => $locations->count()]);

            // Debug: tampilkan data langsung
            if ($locations->isEmpty()) {
                Log::warning('No data found in database');
            }

            $locations = $locations->map(function ($loc) {
                $loc->latitude = (float) $loc->latitude;
                $loc->longitude = (float) $loc->longitude;
                return $loc;
            });

            return response()->json($locations);
        } catch (\Exception $e) {
            Log::error('Error in getData:', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //? method to get location data json
    public function getDataById($id)
    {
        try {
            Log::info('getData function called');

            $location = Locations::select('id_location', 'id_category', 'id_department', 'student_name', 'nim', 'name_location', 'description', 'contact', 'longitude', 'latitude', 'created_at', 'approved_at')->with([
                'category:id_category,name_category',
                'department:id_department,name_department,degree_level,id_faculty',
                'department.faculty:id_faculty,name_faculty',
                'images:id_image,id_location,image_path,alt_text'
            ])->findOrFail($id);

            $location->latitude = (float) $location->latitude;
            $location->longitude = (float) $location->longitude;

            return response()->json($location);
        } catch (\Exception $e) {
            Log::error('Error in getData:', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //? method to generate signed URL for location submission
    public function generate()
    {
        $link = URL::temporarySignedRoute(
            'locations.create',
            now()->addHours(24)
        );

        return response()->json([
            'link' => $link
        ]);
    }

    //? method to show the location submission form
    public function create()
    {
        $categories = Categories::select('id_category', 'name_category')->get();
        $faculties = Faculties::with(
            'departments:id_department,name_department,id_faculty'
        )->get();

        return Inertia::render('Locations/Add', [
            'categories' => $categories,
            'faculties' => $faculties
        ]);
    }

    //? method to store the submitted location data
    public function store(storeLocationRequest $request)
    {
        $validated = $request->validated();

        $location = Locations::create($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('location_images', 'public');

                Images::create([
                    'id_location' => $location->id_location,
                    'image_path'  => $path,
                    'alt_text'    => $location->name_location ?? 'Location Image',
                ]);
            }
        }

        return redirect()->back()->with('success', 'Location request submitted successfully.');
    }

    public function approve($id)
    {
        $location = Locations::findOrFail($id);
        $location->approved_at = now();
        $location->save();

        return redirect()->back()->with('success', 'Location approved successfully.');
    }

    public function destroy($id)
    {
        $location = Locations::findOrFail($id);
        $location->delete();

        return redirect()->back()->with('success', 'Location deleted successfully.');
    }
}
