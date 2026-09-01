

<?php $__env->startSection('title', $incident->title); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3 text-sm text-gray-500">
            <li class="inline-flex items-center">
                <a href="<?php echo e(route('home')); ?>" class="hover:text-blue-600 transition-colors flex items-center gap-1.5">
                    <i class="fas fa-home"></i> <?php echo e(__('messages.home')); ?>

                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                    <a href="<?php echo e(route('safety.index')); ?>" class="hover:text-blue-600 transition-colors"><?php echo e(__('messages.travel_safety')); ?></a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                    <span class="text-gray-800 font-medium truncate max-w-[200px] md:max-w-md"><?php echo e(Str::limit($incident->title, 50)); ?></span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Main Content (Left Column) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Incident Header Card -->
            <div class="glass-card rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 leading-tight"><?php echo e($incident->title); ?></h1>
                    <?php
                        $severityClass = match($incident->severity) {
                            'critical' => 'bg-red-100 text-red-800 border-red-200',
                            'high' => 'bg-orange-100 text-orange-800 border-orange-200',
                            'moderate' => 'bg-amber-100 text-amber-800 border-amber-200',
                            default => 'bg-gray-100 text-gray-800 border-gray-200'
                        };
                    ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold uppercase tracking-wide border <?php echo e($severityClass); ?> whitespace-nowrap">
                        <?php echo e(strtoupper($incident->severity ?? __('messages.unknown'))); ?>

                    </span>
                </div>

                <!-- Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-tag text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold"><?php echo e(__('messages.type')); ?></p>
                                <p class="font-medium text-gray-900"><?php echo e(str_replace('_', ' ', $incident->incident_type ?? __('messages.unknown'))); ?></p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-red-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold"><?php echo e(__('messages.location')); ?></p>
                                <p class="font-medium text-gray-900"><?php echo e($incident->location_name ?? __('messages.unknown')); ?></p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-calendar-alt text-gray-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold"><?php echo e(__('messages.reported')); ?></p>
                                <p class="font-medium text-gray-900"><?php echo e($incident->reported_at?->format('F j, Y, g:i A') ?? __('messages.unknown')); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-check-circle text-emerald-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold"><?php echo e(__('messages.status')); ?></p>
                                <?php
                                    $statusClass = match($incident->status) {
                                        'active' => 'text-emerald-700 bg-emerald-50 border border-emerald-200',
                                        'verified' => 'text-blue-700 bg-blue-50 border border-blue-200',
                                        'under_review' => 'text-amber-700 bg-amber-50 border border-amber-200',
                                        'resolved' => 'text-gray-700 bg-gray-50 border border-gray-200',
                                        default => 'text-gray-700 bg-gray-50 border border-gray-200'
                                    };
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium <?php echo e($statusClass); ?>">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $incident->status ?? 'unknown'))); ?>

                                </span>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-bullseye text-purple-600 text-sm"></i>
                            </div>
                            <div class="w-full">
                                <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold"><?php echo e(__('messages.confidence')); ?></p>
                                <div class="flex items-center gap-3 mt-1">
                                    <div class="flex-grow bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" style="width: <?php echo e(($incident->confidence_score ?? 0) * 100); ?>%"></div>
                                    </div>
                                    <span class="font-bold text-gray-900 text-sm"><?php echo e(number_format(($incident->confidence_score ?? 0) * 100, 0)); ?>%</span>
                                </div>
                            </div>
                        </div>
                        <?php if($incident->travel_impact): ?>
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-car text-amber-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold"><?php echo e(__('messages.travel_impact')); ?></p>
                                    <p class="font-medium text-gray-900"><?php echo e(ucfirst($incident->travel_impact)); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if($incident->description): ?>
                    <div class="border-t border-gray-100 pt-6 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                            <i class="fas fa-file-alt text-blue-600"></i> <?php echo e(__('messages.description')); ?>

                        </h3>
                        <p class="text-gray-700 leading-relaxed text-base"><?php echo e($incident->description); ?></p>
                    </div>
                <?php endif; ?>

                <?php if($incident->recommended_action): ?>
                    <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-lightbulb text-blue-500 text-lg"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-bold text-blue-900 uppercase tracking-wide"><?php echo e(__('messages.recommendation')); ?></p>
                                <p class="text-sm text-blue-800 mt-1 leading-relaxed"><?php echo e($incident->recommended_action); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Affected Areas -->
            <?php if(($affectedWaypoints ?? collect())->count() > 0 || ($affectedRoutes ?? collect())->count() > 0 || ($affectedTreks ?? collect())->count() > 0): ?>
                <div class="glass-card rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 mb-5 flex items-center gap-2">
                        <i class="fas fa-map-marked-alt text-red-500"></i> <?php echo e(__('messages.affected_areas')); ?>

                    </h3>
                    
                    <div class="space-y-6">
                        <?php if(($affectedWaypoints ?? collect())->count() > 0): ?>
                            <div>
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                                    <i class="fas fa-map-pin text-gray-400"></i> <?php echo e(__('messages.waypoints')); ?>

                                </h4>
                                <div class="flex flex-wrap gap-2">
                                    <?php $__currentLoopData = $affectedWaypoints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-gray-100 text-gray-800 border border-gray-200 hover:bg-gray-200 transition-colors">
                                            <?php echo e($wp->name); ?>

                                        </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if(($affectedRoutes ?? collect())->count() > 0): ?>
                            <div>
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                                    <i class="fas fa-road text-gray-400"></i> <?php echo e(__('messages.routes')); ?>

                                </h4>
                                <div class="flex flex-wrap gap-2">
                                    <?php $__currentLoopData = $affectedRoutes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-gray-100 text-gray-800 border border-gray-200 hover:bg-gray-200 transition-colors">
                                            <?php echo e($route->name); ?>

                                        </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if(($affectedTreks ?? collect())->count() > 0): ?>
                            <div>
                                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                                    <i class="fas fa-mountain text-gray-400"></i> <?php echo e(__('messages.treks')); ?>

                                </h4>
                                <div class="flex flex-wrap gap-2">
                                    <?php $__currentLoopData = $affectedTreks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trek): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-gray-100 text-gray-800 border border-gray-200 hover:bg-gray-200 transition-colors">
                                            <?php echo e($trek->name); ?>

                                        </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Sources -->
            <?php if(isset($incident->sources) && $incident->sources->count() > 0): ?>
                <div class="glass-card rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h3 class="text-lg font-bold text-gray-900 mb-5 flex items-center gap-2">
                        <i class="fas fa-newspaper text-blue-600"></i> <?php echo e(__('messages.sources')); ?>

                    </h3>
                    <div class="space-y-3">
                        <?php $__currentLoopData = $incident->sources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $source): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 gap-4 hover:border-blue-200 transition-colors">
                                <div class="flex-1">
                                    <p class="font-bold text-gray-900"><?php echo e($source->name); ?></p>
                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-xs text-gray-500">
                                        <span class="flex items-center gap-1.5">
                                            <i class="fas fa-star text-amber-400"></i> 
                                            <span class="font-medium"><?php echo e(__('messages.reliability')); ?>:</span> <?php echo e(number_format(($source->pivot->source_reliability ?? 0) * 100, 0)); ?>%
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <i class="fas fa-clock text-gray-400"></i> 
                                            <span class="font-medium"><?php echo e(__('messages.published')); ?>:</span> <?php echo e($source->pivot->published_at?->format('M j, Y') ?? __('messages.unknown')); ?>

                                        </span>
                                    </div>
                                </div>
                                <?php if($source->pivot->source_url): ?>
                                    <a href="<?php echo e($source->pivot->source_url); ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-blue-50 hover:border-blue-400 hover:text-blue-600 transition-all shadow-sm whitespace-nowrap">
                                        <i class="fas fa-external-link-alt text-xs"></i> <?php echo e(__('messages.view_source')); ?>

                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar (Right Column) -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Quick Actions -->
            <div class="glass-card rounded-2xl p-6 shadow-sm border border-gray-100 sticky top-24">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-bolt text-amber-500"></i> <?php echo e(__('messages.quick_actions')); ?>

                </h3>
                <div class="space-y-3">
                    <a href="<?php echo e(route('safety.index')); ?>" class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-blue-50 text-blue-700 font-semibold rounded-xl hover:bg-blue-100 transition-colors border border-blue-200">
                        <i class="fas fa-shield-alt"></i> <?php echo e(__('messages.safety_overview')); ?>

                    </a>
                    
                    <?php if(auth()->guard()->check()): ?>
                        <?php if(auth()->user()->isSuperAdmin() || (isset(auth()->user()->role) && auth()->user()->role === 'admin')): ?>
                            <a href="<?php echo e(route('admin.safety.incidents')); ?>" class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-red-50 text-red-700 font-semibold rounded-xl hover:bg-red-100 transition-colors border border-red-200">
                                <i class="fas fa-cog"></i> <?php echo e(__('messages.admin_panel')); ?>

                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Safety Tips -->
            <div class="glass-card rounded-2xl p-6 shadow-sm border border-amber-200 bg-amber-50/40">
                <h3 class="text-lg font-bold text-amber-900 mb-4 flex items-center gap-2">
                    <i class="fas fa-hard-hat text-amber-600"></i> <?php echo e(__('messages.safety_tips')); ?>

                </h3>
                <ul class="space-y-3">
                    <li class="flex items-start gap-3 text-sm text-gray-700">
                        <i class="fas fa-check-circle text-emerald-500 mt-0.5 flex-shrink-0"></i>
                        <span><?php echo e(__('messages.tip_stay_informed') ?? 'Stay informed through official sources'); ?></span>
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-700">
                        <i class="fas fa-check-circle text-emerald-500 mt-0.5 flex-shrink-0"></i>
                        <span><?php echo e(__('messages.tip_follow_authorities') ?? 'Follow local authorities\' guidance'); ?></span>
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-700">
                        <i class="fas fa-check-circle text-emerald-500 mt-0.5 flex-shrink-0"></i>
                        <span><?php echo e(__('messages.tip_emergency_contacts') ?? 'Keep emergency contacts handy'); ?></span>
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-700">
                        <i class="fas fa-check-circle text-emerald-500 mt-0.5 flex-shrink-0"></i>
                        <span><?php echo e(__('messages.tip_avoid_affected') ?? 'Avoid affected areas if possible'); ?></span>
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-700">
                        <i class="fas fa-check-circle text-emerald-500 mt-0.5 flex-shrink-0"></i>
                        <span><?php echo e(__('messages.tip_check_updates') ?? 'Check back for updates regularly'); ?></span>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\TravelAI-Nepal\resources\views/safety/incident.blade.php ENDPATH**/ ?>