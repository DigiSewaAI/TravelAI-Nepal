

<?php $__env->startSection('title', 'Journey Replay - TravelAI Nepal'); ?>

<?php $__env->startSection('og_meta'); ?>
    <?php echo $__env->make('partials.og-meta', [
        'ogTitle' => 'My Journey with TravelAI Nepal',
        'ogDescription' => $replayData['stats']['total_places'] . ' places • ' . $replayData['stats']['total_moments'] . ' moments • ' . $replayData['stats']['highest_altitude'] . 'm',
        'ogImage' => asset('images/default-share.jpg'),
        'ogUrl' => url()->current()
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-10 max-w-6xl">
    <div class="bg-white rounded-3xl shadow-lg overflow-hidden border border-gray-100">
        <div class="bg-gradient-to-r from-blue-600 to-purple-700 text-white p-8 md:p-12">
            <h1 class="text-3xl md:text-5xl font-bold">My Journey</h1>
            <p class="text-blue-100 mt-2 text-lg"><?php echo e($replayData['stats']['total_places']); ?> Places • <?php echo e($replayData['stats']['total_moments']); ?> Moments • <?php echo e($replayData['stats']['highest_altitude']); ?>m</p>
            <div class="mt-4 flex flex-wrap gap-2 text-sm">
                <span class="bg-white/20 px-3 py-1 rounded-full"><?php echo e($replayData['stats']['journey_start']->format('M d, Y')); ?></span>
                <?php if($replayData['stats']['journey_end']): ?>
                <span class="bg-white/20 px-3 py-1 rounded-full">→ <?php echo e($replayData['stats']['journey_end']->format('M d, Y')); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="p-6 md:p-8">
            <!-- Checkpoints Timeline -->
            <h2 class="text-2xl font-bold text-gray-800 mb-4">📍 Checkpoints</h2>
            <div class="space-y-4">
                <?php $__currentLoopData = $replayData['checkpoints']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-start gap-4 border-b border-gray-100 pb-4">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-sm flex-shrink-0"><?php echo e($loop->iteration); ?></div>
                    <div>
                        <h3 class="font-semibold text-gray-800"><?php echo e($cp->name); ?></h3>
                        <p class="text-sm text-gray-500">Altitude: <?php echo e($cp->altitude ?? 'N/A'); ?>m</p>
                        <?php
                            $cpMedia = $replayData['media']->where('waypoint_id', $cp->id);
                        ?>
                        <?php if($cpMedia->count()): ?>
                        <div class="flex gap-2 mt-2 flex-wrap">
                            <?php $__currentLoopData = $cpMedia; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <img src="<?php echo e(route('public.journey.media', ['token' => $replayData['share_token'], 'filename' => $m->file_name])); ?>" class="w-16 h-16 object-cover rounded-lg border" alt="Memory">
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- Map (optional) -->
            <div class="mt-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-3">🗺️ Journey Path</h2>
                <div id="public-map" style="height: 300px; background: #eef2f6; border-radius: 12px;"></div>
            </div>

            <!-- Cinematic Button -->
            <div class="mt-8 text-center">
                <a href="<?php echo e(route('public.journey.cinematic', ['token' => $replayData['share_token']])); ?>" class="inline-block bg-gradient-to-r from-pink-500 to-purple-600 text-white px-8 py-3 rounded-full font-semibold shadow-lg hover:shadow-xl transition hover:scale-105">
                    🎬 Watch Cinematic Replay
                </a>
            </div>

            <!-- Share Buttons -->
            <div class="mt-8 border-t border-gray-200 pt-6">
                <h3 class="font-semibold text-gray-700 mb-3">Share this journey</h3>
                <div class="flex flex-wrap gap-3">
                    <button onclick="window.open('https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent('<?php echo e(url()->current()); ?>'), '_blank')" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">📘 Facebook</button>
                    <button onclick="window.open('https://wa.me/?text='+encodeURIComponent('Check out my journey! <?php echo e(url()->current()); ?>'), '_blank')" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">💬 WhatsApp</button>
                    <button onclick="navigator.clipboard.writeText('<?php echo e(url()->current()); ?>').then(()=>alert('Link copied!'))" class="bg-gray-700 text-white px-4 py-2 rounded-lg hover:bg-gray-800">🔗 Copy Link</button>
                    <?php if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false): ?>
                    <button onclick="navigator.share({title:'My Journey', text:'Check out my journey!', url:'<?php echo e(url()->current()); ?>'})" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">📱 Share</button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- CTA -->
            <div class="mt-8 text-center bg-gray-50 rounded-xl p-6">
                <p class="text-gray-600">Inspired by this journey? Plan your own Nepal adventure.</p>
                <a href="<?php echo e(route('home')); ?>" class="inline-block mt-2 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Explore Nepal →</a>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const points = <?php echo json_encode($replayData['checkpoints']->map(fn($c) => ['lat' => $c->latitude, 'lng' => $c->longitude, 'name' => $c->name])) ?>;
        if (points.length) {
            const map = L.map('public-map').setView([points[0].lat, points[0].lng], 11);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);
            points.forEach(p => {
                L.marker([p.lat, p.lng]).addTo(map).bindPopup(p.name);
            });
            if (points.length > 1) {
                const latlngs = points.map(p => [p.lat, p.lng]);
                L.polyline(latlngs, { color: 'blue', weight: 4 }).addTo(map);
                map.fitBounds(latlngs);
            }
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\TravelAI-Nepal\resources\views/public/journey-replay.blade.php ENDPATH**/ ?>