<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * FIX-XX: Extend bookings.status enum to include 'rejected'.
     *
     * Context:
     *   - Application code (BookingStatusTransitions + Provider\BookingController)
     *     references 'rejected' but DB enum was missing it.
     *   - Runtime attempt to set status='rejected' fails with:
     *     SQLSTATE[01000] Data truncated for column 'status'
     *   - Pre-existing inconsistency (pre-dates 09B-04).
     *
     * Scope: 1 additive migration, no data changes.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE bookings
            MODIFY COLUMN status
            ENUM('pending','confirmed','completed','cancelled','rejected')
            NOT NULL
        ");
    }

    public function down(): void
    {
        // Safety: refuse rollback if any 'rejected' rows exist
        $rejectedCount = DB::table('bookings')
            ->where('status', 'rejected')
            ->count();

        if ($rejectedCount > 0) {
            throw new \RuntimeException(
                "Cannot rollback: {$rejectedCount} bookings in 'rejected' status. "
                . "Manual review required before rollback."
            );
        }

        DB::statement("
            ALTER TABLE bookings
            MODIFY COLUMN status
            ENUM('pending','confirmed','completed','cancelled')
            NOT NULL
        ");
    }
};