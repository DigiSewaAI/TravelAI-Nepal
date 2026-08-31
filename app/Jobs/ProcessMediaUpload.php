<?php

namespace App\Jobs;

use App\Models\UserMedia;
use App\Services\Media\MediaOptimizationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProcessMediaUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;
    protected int $waypointId;
    protected ?int $bookingId;
    protected ?int $qrScanId;
    protected UploadedFile $file;
    protected string $mediaType;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $userId,
        int $waypointId,
        ?int $bookingId,
        ?int $qrScanId,
        UploadedFile $file,
        string $mediaType
    ) {
        $this->userId = $userId;
        $this->waypointId = $waypointId;
        $this->bookingId = $bookingId;
        $this->qrScanId = $qrScanId;
        $this->file = $file;
        $this->mediaType = $mediaType;
    }

    /**
     * Execute the job.
     */
    public function handle(MediaOptimizationService $optimizer): void
    {
        // 🔍 Debug: First line of handle method
        Log::info('🚀 Job handle started', ['user_id' => $this->userId]);

        Log::info('🎬 [ProcessMediaUpload] Job started', [
            'user_id'      => $this->userId,
            'waypoint_id'  => $this->waypointId,
            'booking_id'   => $this->bookingId,
            'qr_scan_id'   => $this->qrScanId,
            'media_type'   => $this->mediaType,
            'file_name'    => $this->file->getClientOriginalName(),
        ]);

        try {
            // 1. Validate file
            if (!$this->file instanceof UploadedFile) {
                throw new \Exception('Invalid file object in job (not an UploadedFile).');
            }

            if (!$this->file->isValid()) {
                throw new \Exception('Uploaded file is not valid.');
            }

            // 2. Process the media
            if ($this->mediaType === 'image') {
                $result = $optimizer->optimizeImage($this->file);
            } elseif ($this->mediaType === 'video') {
                $result = $optimizer->optimizeVideo($this->file);
            } else {
                throw new \Exception('Unsupported media type: ' . $this->mediaType);
            }

            // 3. Create UserMedia record
            $media = UserMedia::create([
                'user_id'        => $this->userId,
                'waypoint_id'    => $this->waypointId,
                'booking_id'     => $this->bookingId,
                'qr_scan_id'     => $this->qrScanId,
                'media_type'     => $this->mediaType,
                'file_name'      => $this->file->getClientOriginalName(),
                'optimized_path' => $result['optimized'],
                'thumbnail_path' => $result['thumbnail'] ?? null,
                'metadata'       => $result['metadata'] ?? [],
                'source'         => 'user',
            ]);

            // 4. Mark as primary if it's the first media for this checkpoint/user
            $existingCount = UserMedia::where('waypoint_id', $this->waypointId)
                                      ->where('user_id', $this->userId)
                                      ->count();

            if ($existingCount === 1) {
                $media->is_primary = true;
                $media->save();
            }

            Log::info('✅ [ProcessMediaUpload] Job completed successfully', [
                'media_id' => $media->id,
            ]);

        } catch (\Exception $e) {
            Log::error('❌ [ProcessMediaUpload] Job failed', [
                'user_id'    => $this->userId,
                'waypoint_id'=> $this->waypointId,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            // Re-throw the exception to mark the job as failed
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('💀 [ProcessMediaUpload] Job failed permanently after retries', [
            'user_id'    => $this->userId,
            'waypoint_id'=> $this->waypointId,
            'error'      => $exception->getMessage(),
            'trace'      => $exception->getTraceAsString(),
        ]);
    }
}