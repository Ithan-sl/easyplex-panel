<?php

namespace App\Http\Controllers;

use App\Certification;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\CertificationRequest;

class CertificationController extends Controller
{
    


    public function data(Request $request)
{
    $perPage = $request->input('per_page', 12);
    $sortBy = $request->input('sort_by', 'country_code');
    $sortOrder = $request->input('sort_order', 'asc');
    $search = $request->input('search');

    $query = Certification::query();

    // Exclude records where id or country_code is null
    $query->whereNotNull('id')->whereNotNull('country_code');

    // Apply search if provided
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('country_code', 'like', "%{$search}%")
              ->orWhere('certification', 'like', "%{$search}%")
              ->orWhere('meaning', 'like', "%{$search}%");
        });
    }

    // Validate and sanitize sort column
    $allowedSortColumns = ['id', 'country_code', 'certification', 'meaning', 'order', 'created_at', 'updated_at'];
    $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'country_code';

    // Validate sort order
    $sortOrder = in_array(strtolower($sortOrder), ['asc', 'desc']) ? $sortOrder : 'asc';

    // Apply sorting
    $query->orderBy($sortBy, $sortOrder);

    $certifications = $query->paginate($perPage);

    return response()->json([
        'data' => $certifications->items(),
        'current_page' => $certifications->currentPage(),
        'last_page' => $certifications->lastPage(),
        'per_page' => $certifications->perPage(),
        'total' => $certifications->total(),
    ], 200);
}

    public function datacertifications()
    {
        return response()->json(Certification::get(), 200);
    }

   
    public function fetch(Request $request)
    {
        try {
            DB::beginTransaction();
    
            $insertedCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;
    
            foreach ($request->certifications as $countryCode => $countryCertifications) {
                // Skip if countryCode is null or empty
                if (empty($countryCode)) {
                    $skippedCount += count($countryCertifications);
                    continue;
                }
    
                foreach ($countryCertifications as $certificationData) {
                    // Ensure all required fields are present and not null
                    if (empty($certificationData['certification']) || 
                        !isset($certificationData['meaning']) || 
                        !isset($certificationData['order'])) {
                        $skippedCount++;
                        continue; // Skip this iteration if any required field is missing or null
                    }
    
                    $certification = Certification::updateOrCreate(
                        [
                            'country_code' => $countryCode,
                            'certification' => $certificationData['certification']
                        ],
                        [
                            'meaning' => $certificationData['meaning'],
                            'order' => $certificationData['order'],
                        ]
                    );
    
                    if ($certification->wasRecentlyCreated) {
                        $insertedCount++;
                    } else {
                        $updatedCount++;
                    }
                }
            }
    
            DB::commit();
            return response()->json([
                'message' => 'Data processed successfully',
                'inserted' => $insertedCount,
                'updated' => $updatedCount,
                'skipped' => $skippedCount
            ], 200);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error processing data: ' . $e->getMessage()], 500);
        }
    }



    public function deleteAllRecords()
    {

        Certification::query()->delete();

        return response()->json(['message' => 'All records deleted successfully']);
    }


     // create a new genre in the database
 public function store(CertificationRequest $request)
 {
     $genre = new Certification();
     $genre->fill($request->all());
     $genre->save();

     $data = [
         'status' => 200,
         'message' => 'successfully created',
         'body' => $genre
     ];

     return response()->json($data, $data['status']);
 }


    public function update($id, Request $request)
    {
        $languageData = $request->validate([
            'country_code' => 'string|max:255',
            'certification' => 'string|max:255',
            'meaning' => 'string|max:255',
        ]);

        // Find the language by ID
        $language = Certification::find($id);

        // Update the language if found, or create a new one if not found
        $language->updateOrCreate(['id' => $id], $languageData);

        return response()->json($language, 200);
    }


    public function destroy($id)
    {
        if ($id != null) {
            Certification::find($id)->delete();
            $data = [
                'status' => 200,
                'message' => 'successfully deleted'
            ];
        } else {
            $data = [
                'status' => 400,
                'message' => 'could not be deleted'
            ];
        }
   
        return response()->json($data, $data['status']);
    }

}
