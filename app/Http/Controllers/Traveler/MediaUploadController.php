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
            // ✅ Debug: Log incoming request
            Log::info('Upload request received', $request->all());

            $request->validate([
                'checkpoint' => 'required|string',
                'media' => 'required|file|max:51200',
            ]);

            $user = Auth::user();
            $file = $request->file('media');

            // ✅ Debug: Check if file is present
            if (!$file) {
                throw new \Exception('No file received');
            }

            Log::info('File received', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
            ]);

            // Find waypoint
            $waypoint = Waypoint::where('name', $request->checkpoint)
                                ->orWhere('slug', $request->checkpoint)
                                ->first();

            if (!$waypoint) {
                return response()->json([
                    'success' => false,
                    'message' => 'Checkpoint not found.'
                ], 404);
            }

            // ✅ Store file
            $path = $file->store('media/' . $user->id, 'public');

            if (!$path) {
                throw new \Exception('Failed to store file.');
            }

            Log::info('File stored at: ' . $path);

            // ✅ Create DB record
            $media = UserMedia::create([
                'user_id'        => $user->id,
                'waypoint_id'    => $waypoint->id,
                'booking_id'     => null,
                'media_type'     => str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'video',
                'file_name'      => $file->getClientOriginalName(),
                'optimized_path' => $path,
                'thumbnail_path' => null,
                'metadata'       => json_encode(['checkpoint' => $request->checkpoint]),
                'captured_at'    => now(),
                'is_primary'     => false,
                'source'         => 'user',
            ]);

            Log::info('Media record created', ['media_id' => $media->id]);

            return response()->json([
                'success' => true,
                'message' => '✅ Uploaded successfully!',
                'media_id' => $media->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Upload error: ' . $e->getMessage());
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
            Log::error('Delete error: ' . $e->getMessage());
            return redirect()->route('traveler.dashboard')
                             ->with('upload_error', '❌ ' . $e->getMessage());
        }
    }
}