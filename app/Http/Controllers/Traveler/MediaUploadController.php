<?php

namespace App\Http\Controllers\Traveler;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Waypoint;
use App\Models\UserMedia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MediaUploadController extends Controller
{
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'checkpoint' => 'required|string',
                'media' => 'required|file|max:51200', // 50MB
            ]);

            $user = Auth::user();

            // Find waypoint by name
            $waypoint = Waypoint::where('name', $request->checkpoint)
                                ->orWhere('slug', $request->checkpoint)
                                ->first();

            if (!$waypoint) {
                return response()->json([
                    'success' => false,
                    'message' => 'Checkpoint not found.'
                ], 404);
            }

            // ✅ Store file directly (no processing)
            $path = $request->file('media')->store('media', 'public');

            if (!$path) {
                throw new \Exception('Failed to store file.');
            }

            // ✅ Create DB record
            $media = UserMedia::create([
                'user_id'        => $user->id,
                'waypoint_id'    => $waypoint->id,
                'media_type'     => 'image',
                'file_name'      => $request->file('media')->getClientOriginalName(),
                'optimized_path' => $path,
                'thumbnail_path' => null,
                'source'         => 'user',
                'is_primary'     => false,
            ]);

            Log::info('✅ Upload success', ['media_id' => $media->id, 'path' => $path]);

            return response()->json([
                'success' => true,
                'message' => '✅ Uploaded successfully!',
                'media_id' => $media->id,
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Upload error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ ' . $e->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $request->validate(['id' => 'required|exists:user_media,id']);

            $media = UserMedia::where('id', $request->id)
                              ->where('user_id', Auth::id())
                              ->firstOrFail();

            // Delete file from storage
            if (Storage::disk('public')->exists($media->optimized_path)) {
                Storage::disk('public')->delete($media->optimized_path);
            }
            if ($media->thumbnail_path && Storage::disk('public')->exists($media->thumbnail_path)) {
                Storage::disk('public')->delete($media->thumbnail_path);
            }

            $media->delete();

            return redirect()->route('traveler.dashboard')
                             ->with('upload_success', '✅ Memory deleted successfully!');

        } catch (\Exception $e) {
            Log::error('❌ Delete error: ' . $e->getMessage());
            return redirect()->route('traveler.dashboard')
                             ->with('upload_error', '❌ ' . $e->getMessage());
        }
    }
}