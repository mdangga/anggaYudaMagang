<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReqLocation\StoreReqLocationRequest;
use App\Models\Category;
use App\Models\Location;
use App\Models\LocationRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class RequestLocationController extends Controller
{
    public function index()
    {
     try {
            Log::info('getData function called');

            $locations_request = DB::table('location_request')
                ->join('category', 'location_request.id_category', '=', 'category.id_category')
                ->select(
                    'location_request.student_name',
                    'location_request.nim',
                    'location_request.id_request',
                    'location_request.name_location',
                    'category.name_category AS category_name',
                    'location_request.description',
                    'location_request.latitude',
                    'location_request.longitude',
                    'location_request.image_path',
                    'location_request.created_at',
                    'location_request.updated_at'
                )->where('approved_at', null)
                ->get();

            Log::info('Data fetched:', ['count' => $locations_request->count()]);

            // Debug: tampilkan data langsung
            if ($locations_request->isEmpty()) {
                Log::warning('No data found in database');
            }

            $locations = $locations_request->map(function ($loc) {
                $loc->latitude = (float) $loc->latitude;
                $loc->longitude = (float) $loc->longitude;
                $loc->image_path = asset('storage/' . $loc->image_path);
                return $loc;
            });

            return response()->json($locations_request);
        } catch (\Exception $e) {
            Log::error('Error in getData:', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }   
    }
    
    // * menampilkan form request location
    public function create()
    {
        $category = Category::select('id_category', 'name_category')->get();
        return Inertia::render('RequestLocation/Add', [
            'categories' => $category
        ]);
    }

    // * menyimpan data request location
    public function store(StoreReqLocationRequest $request)
    {
        $validated = $request->validated();

        // Handle file upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('location_images', 'public');
            $validated['image_path'] = $imagePath;
        }

        // Simpan data ke database
        LocationRequest::create($validated);

        return redirect()->back()->with('success', 'Location request submitted successfully.');
    }

    // * approve request location
    public function approve($id)
    {
        $req = LocationRequest::findOrFail($id);

        // Insert ke tabel location
        Location::create([
            'name_location' => $req->name_location,
            'description'   => $req->description,
            'latitude'      => $req->latitude,
            'longitude'     => $req->longitude,
            'image_path'    => $req->image_path,
            'id_category'   => $req->id_category,
        ]);

        // Update approved_at
        $req->approve();

        return redirect()->back()->with([
            'message' => 'Lokasi berhasil di-approve dan dipindahkan ke daftar lokasi.'
        ]);
    }

    // * delete request location
    public function destroy($id)
    {
        $req = LocationRequest::findOrFail($id);

        // Delete image
        if ($req->image_path) {
            Storage::disk('public')->delete($req->image_path);
        }

        $req->delete();

        return response()->json([
            'message' => 'Request berhasil dihapus.'
        ]);
    }
}
