

<?php $__env->startSection('title', __('messages.travel_safety_nepal')); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    
    <!-- Page Header with Last Updated -->
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
        <div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 flex items-center gap-3">
                <span class="text-4xl">🇳🇵</span>
                <?php echo e(__('messages.travel_safety_nepal')); ?>

            </h1>
            <p class="text-gray-600 mt-2 max-w-2xl">
                <?php echo e(__('messages.safety_subtitle') ?? 'Real-time safety updates, AI-driven risk assessments, and live incident tracking for travelers across Nepal.'); ?>

            </p>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-500 bg-gray-50 px-4 py-2 rounded-full border border-gray-200">
            <i class="fas fa-sync-alt text-blue-500 animate-spin-slow"></i>
            <span>Last updated: <?php echo e(now()->format('M d, Y h:i A')); ?></span>
        </div>
    </div>
    
    <!-- Advanced Summary Cards -->
<?php
    $computedStats = [
        'normal'    => $incidents->where('severity', 'normal')->count(),
        'caution'   => $incidents->where('severity', 'moderate')->count(),
        'high_risk' => $incidents->whereIn('severity', ['high', 'high_risk'])->count(),
        'avoid'     => $incidents->whereIn('severity', ['critical', 'avoid'])->count(),
    ];
?>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
    <?php
        $statsConfig = [
            ['key' => 'normal', 'label' => __('messages.status_normal'), 'color' => 'emerald', 'icon' => 'fa-check-circle'],
            ['key' => 'caution', 'label' => __('messages.status_caution'), 'color' => 'amber', 'icon' => 'fa-exclamation-circle'],
            ['key' => 'high_risk', 'label' => __('messages.status_high_risk'), 'color' => 'orange', 'icon' => 'fa-radiation'],
            ['key' => 'avoid', 'label' => __('messages.status_avoid'), 'color' => 'red', 'icon' => 'fa-ban'],
        ];
    ?>

    <?php $__currentLoopData = $statsConfig; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="glass-card rounded-2xl p-5 border-l-4 border-<?php echo e($stat['color']); ?>-500 bg-<?php echo e($stat['color']); ?>-50/40 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-<?php echo e($stat['color']); ?>-700 uppercase tracking-wider"><?php echo e($stat['label']); ?></p>
                    <h2 class="text-3xl font-extrabold text-<?php echo e($stat['color']); ?>-900 mt-1">
                        <?php echo e($computedStats[$stat['key']] ?? 0); ?>

                    </h2>
                </div>
                <div class="w-12 h-12 rounded-xl bg-<?php echo e($stat['color']); ?>-100 flex items-center justify-center text-<?php echo e($stat['color']); ?>-600 shadow-sm">
                    <i class="fas <?php echo e($stat['icon']); ?> text-xl"></i>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Map Section (Takes 2/3 width on large screens) -->
        <div class="lg:col-span-2">
            <div class="glass-card rounded-2xl p-1 shadow-lg border border-gray-200 overflow-hidden relative">
                <div class="bg-white rounded-xl p-4 md:p-6 relative">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-map-marked-alt text-blue-600"></i> <?php echo e(__('messages.safety_map')); ?>

                        </h2>
                        <span class="text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-full">Live Interactive Map</span>
                    </div>
                    
                    <!-- Map Container -->
                    <div id="safetyMap" class="w-full h-[500px] rounded-xl z-0 border border-gray-200 relative">
                        <!-- Loading State -->
                        <div id="mapLoader" class="absolute inset-0 bg-white/80 backdrop-blur-sm z-[1000] flex items-center justify-center rounded-xl">
                            <div class="flex flex-col items-center gap-3">
                                <i class="fas fa-circle-notch fa-spin text-3xl text-blue-600"></i>
                                <span class="text-sm font-medium text-gray-600">Loading map data...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Map Legend -->
                    <div class="absolute bottom-8 right-8 z-[500] bg-white/95 backdrop-blur-md p-3 rounded-xl shadow-lg border border-gray-200 text-xs hidden md:block">
                        <p class="font-bold text-gray-700 mb-2">Map Legend</p>
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-red-500"></span> Critical / Avoid</div>
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-orange-500"></span> High Risk</div>
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-amber-400"></span> Caution</div>
                            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Normal</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar: Affected Areas -->
        <div class="lg:col-span-1">
            <div class="glass-card rounded-2xl p-6 shadow-sm border border-gray-100 sticky top-24">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-layer-group text-red-500"></i> <?php echo e(__('messages.affected_areas')); ?>

                </h3>

                <div class="space-y-3 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                    <?php $hasAreas = false; ?>
                    
                    <?php $__currentLoopData = ($affectedWaypoints ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $hasAreas = true; ?>
                        <?php 
                            $status = strtolower($wp->safety_status ?? 'normal');
                            $badgeColor = match($status) {
                                'caution' => 'bg-amber-100 text-amber-800 border-amber-200',
                                'high_risk', 'high' => 'bg-orange-100 text-orange-800 border-orange-200',
                                'avoid', 'critical' => 'bg-red-100 text-red-800 border-red-200',
                                default => 'bg-emerald-100 text-emerald-800 border-emerald-200'
                            };
                        ?>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-100 hover:bg-white hover:shadow-md transition-all">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-map-pin text-gray-400"></i>
                                <span class="font-medium text-gray-800 text-sm"><?php echo e($wp->name ?? 'Unknown Location'); ?></span>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border <?php echo e($badgeColor); ?>">
                                <?php echo e(str_replace('_', ' ', $status)); ?>

                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <?php $__currentLoopData = ($affectedTreks ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trek): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $hasAreas = true; ?>
                        <?php 
                            $status = strtolower($trek->safety_status ?? 'normal');
                            $badgeColor = match($status) {
                                'caution' => 'bg-amber-100 text-amber-800 border-amber-200',
                                'high_risk', 'high' => 'bg-orange-100 text-orange-800 border-orange-200',
                                'avoid', 'critical' => 'bg-red-100 text-red-800 border-red-200',
                                default => 'bg-emerald-100 text-emerald-800 border-emerald-200'
                            };
                        ?>
                        <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-100 hover:bg-white hover:shadow-md transition-all">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-mountain text-gray-400"></i>
                                <span class="font-medium text-gray-800 text-sm"><?php echo e($trek->name ?? 'Unknown Trek'); ?></span>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border <?php echo e($badgeColor); ?>">
                                <?php echo e(str_replace('_', ' ', $status)); ?>

                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <?php if(!$hasAreas): ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-shield-alt text-3xl text-emerald-400 mb-2"></i>
                            <p class="text-sm">No areas currently under safety alerts.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Incidents Section -->
    <div class="mt-10">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-2 h-8 bg-red-500 rounded-full animate-pulse"></div>
            <h2 class="text-2xl font-bold text-gray-900"><?php echo e(__('messages.active_incidents')); ?></h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php $__empty_1 = true; $__currentLoopData = $incidents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $incident): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $severityColor = match($incident->severity) {
                        'critical' => 'border-red-500 bg-red-50/50',
                        'high' => 'border-orange-500 bg-orange-50/50',
                        'moderate' => 'border-amber-500 bg-amber-50/50',
                        default => 'border-emerald-500 bg-emerald-50/50'
                    };
                    $icon = match($incident->severity) {
                        'critical' => '🔴',
                        'high' => '🟠',
                        'moderate' => '🟡',
                        default => '🟢'
                    };
                ?>
                <div class="glass-card rounded-2xl p-5 border-l-4 <?php echo e($severityColor); ?> hover:shadow-lg transition-all duration-300 hover:-translate-y-1 flex flex-col h-full">
                    <div class="flex items-start justify-between mb-3">
                        <span class="text-2xl" title="<?php echo e($incident->severity); ?>"><?php echo e($icon); ?></span>
                        <span class="text-xs font-semibold text-gray-500 bg-white px-2 py-1 rounded-md border border-gray-200">
                            <?php echo e($incident->reported_at?->diffForHumans() ?? 'Recently'); ?>

                        </span>
                    </div>
                    
                    <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 leading-tight">
                        <a href="<?php echo e(route('safety.incident', $incident->id)); ?>" class="hover:text-blue-600 transition-colors">
                            <?php echo e($incident->title); ?>

                        </a>
                    </h3>
                    
                    <div class="flex items-center gap-2 text-sm text-gray-600 mb-3">
                        <i class="fas fa-map-marker-alt text-gray-400"></i>
                        <span class="truncate"><?php echo e($incident->location_name ?? 'Unknown Location'); ?></span>
                    </div>

                    <?php if($incident->description): ?>
                        <p class="text-sm text-gray-600 mb-4 line-clamp-3 flex-grow">
                            <?php echo e(Str::limit($incident->description, 120)); ?>

                        </p>
                    <?php endif; ?>

                    <a href="<?php echo e(route('safety.incident', $incident->id)); ?>" class="mt-auto inline-flex items-center justify-center gap-2 w-full py-2.5 bg-white border border-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition-all">
                        <?php echo e(__('messages.view_details')); ?> <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-full glass-card rounded-2xl p-12 text-center border border-dashed border-gray-300">
                    <i class="fas fa-check-circle text-5xl text-emerald-400 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">All Clear!</h3>
                    <p class="text-gray-500"><?php echo e(__('messages.no_active_incidents_reported') ?? 'No active safety incidents reported at this time.'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch('<?php echo e(route("api.safety.markers")); ?>')
            .then(response => response.json())
            .then(data => {
                // Hide loader
                document.getElementById('mapLoader').style.display = 'none';

                const map = L.map('safetyMap').setView([28.3949, 84.1240], 7);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    className: 'rounded-xl'
                }).addTo(map);

                if (data.length === 0) {
                    // Optional: Add a "No incidents" marker or message on map
                }

                data.forEach(incident => {
                    const marker = L.circleMarker([incident.latitude, incident.longitude], {
                        radius: 10,
                        color: '#ffffff',
                        fillColor: incident.color,
                        fillOpacity: 0.9,
                        weight: 3
                    }).addTo(map);

                    const popupContent = `
                        <div class="p-1 min-w-[220px]">
                            <strong class="text-gray-900 text-base block mb-1">${incident.title}</strong>
                            <span class="inline-block px-2 py-0.5 rounded text-xs font-bold text-white mb-2" style="background-color: ${incident.color}">
                                ${incident.severity.toUpperCase()}
                            </span>
                            <p class="text-sm text-gray-600 mb-2">${incident.location || ''}</p>
                            <a href="${incident.url}" class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-800">
                                View Details &rarr;
                            </a>
                        </div>
                    `;
                    marker.bindPopup(popupContent);

                    if (incident.affected_radius) {
                        L.circle([incident.latitude, incident.longitude], {
                            radius: incident.affected_radius,
                            color: incident.color,
                            fillColor: incident.color,
                            fillOpacity: 0.1,
                            weight: 1,
                            opacity: 0.4
                        }).addTo(map);
                    }
                });
            })
            .catch(error => {
                console.error('Error loading safety markers:', error);
                document.getElementById('mapLoader').innerHTML = '<span class="text-red-500 text-sm">Failed to load map data</span>';
            });
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\TravelAI-Nepal\resources\views/safety/index.blade.php ENDPATH**/ ?>