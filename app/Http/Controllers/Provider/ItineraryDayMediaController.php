<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceItineraryDay;
use App\Models\ServiceItineraryDayMedia;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ItineraryDayMediaController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store one or more media files for a day.
     * SL5: files[] array, max 10 files, 25 MB each (C2 binding).
     * thumbnail_path = null (F6 deferred).
     */
    public function store(Request $request, Service $service, ServiceItineraryDay $day)
    {
        $this->authorize('update', $service);

        // SL8 defense in depth
        if ($day->service_id !== $service->id) {
            abort(404);
        }

        $validated = $request->validate([
            'files'      => 'required|array|min:1|max:10',
            'files.*'    => 'required|file|mimes:jpg,jpeg,png,webp,mp4|max:25600',
            'media_type' => 'required|in:image,video',
            'alt_text'   => 'nullable|string|max:255',
        ]);

        $folder = 'itinerary/days/' . $day->id;

        // Determine current max sort_order for this day
        $nextOrder = (int) DB::table('service_itinerary_day_media')
            ->where('day_id', $day->id)
            ->max('sort_order') + 1;

        $uploadedPaths = [];

        try {
            DB::transaction(function () use ($request, $day, $folder, $validated, &$nextOrder, &$uploadedPaths) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store($folder, 'public');
                    $uploadedPaths[] = $path;

                    ServiceItineraryDayMedia::create([
                        'day_id'         => $day->id,
                        'file_path'      => $path,
                        'thumbnail_path' => null, // F6 deferred
                        'media_type'     => $validated['media_type'],
                        'alt_text'       => $validated['alt_text'] ?? null,
                        'sort_order'     => $nextOrder++,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            // Clean up any files already stored on disk
            foreach ($uploadedPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            return back()->withErrors([
                'files' => 'Upload failed. Please try again.',
            ]);
        }

        return redirect()
            ->route('provider.services.itinerary.index', $service)
            ->with('success', count($uploadedPaths) . ' media file(s) uploaded.');
    }

    /**
     * Delete a single media record + its file from disk.
     * Remaining media in the day are renumbered 1..N.
     */
    public function destroy(Service $service, ServiceItineraryDayMedia $media)
    {
        $this->authorize('update', $service);

        // SL8 defense in depth: media -> day -> service chain
        if (!$media->day || $media->day->service_id !== $service->id) {
            abort(404);
        }

        $dayId    = $media->day_id;
        $filePath = $media->file_path;

        DB::transaction(function () use ($media, $dayId) {
            $media->delete();

            // Renumber remaining media in this day
            $remainingIds = DB::table('service_itinerary_day_media')
                ->where('day_id', $dayId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->pluck('id')
                ->toArray();

            foreach ($remainingIds as $index => $id) {
                DB::table('service_itinerary_day_media')
                    ->where('id', $id)
                    ->update(['sort_order' => $index + 1]);
            }
        });

        // Delete file from disk AFTER successful DB transaction
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        return redirect()
            ->route('provider.services.itinerary.index', $service)
            ->with('success', 'Media removed.');
    }
}