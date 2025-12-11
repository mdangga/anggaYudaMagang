<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    public function getData()
    {
        try {
            Log::info('getData function called');

            $locations = DB::table('location')
                ->join('category', 'location.id_category', '=', 'category.id_category')
                ->select(
                    'location.id_location',
                    'location.name_location',
                    'category.name_category AS category_name',
                    'location.description',
                    'location.latitude',
                    'location.longitude',
                    'location.image_path',
                    'location.created_at',
                    'location.updated_at'
                )
                ->get();

            Log::info('Data fetched:', ['count' => $locations->count()]);

            // Debug: tampilkan data langsung
            if ($locations->isEmpty()) {
                Log::warning('No data found in database');
            }

            $locations = $locations->map(function ($loc) {
                $loc->latitude = (float) $loc->latitude;
                $loc->longitude = (float) $loc->longitude;
                $loc->image_path = asset('storage/' . $loc->image_path);
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
}
