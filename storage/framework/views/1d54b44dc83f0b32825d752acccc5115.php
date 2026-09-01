<?php $__env->startSection('title', __('messages.traveler_dashboard_title')); ?>

<?php $__env->startSection('content'); ?>


<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-10 px-4">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-wrap justify-between items-center">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold">
                    <?php echo e(__('messages.traveler_greeting', ['greeting' => $greeting ?? 'Morning', 'name' => Auth::user()->name ?? 'Traveler'])); ?>

                </h1>
                <p class="text-blue-100 text-lg mt-1"><?php echo e(__('messages.traveler_ready_for_adventure')); ?></p>
            </div>
            <div class="flex flex-wrap gap-3 mt-4 md:mt-0">
                <a href="<?php echo e(route('home')); ?>#ai-planner" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2">
                    <i class="fas fa-robot"></i> <?php echo e(__('messages.plan_with_ai')); ?>

                </a>
                <a href="<?php echo e(route('public.services.index')); ?>" class="bg-white text-blue-600 hover:bg-gray-100 px-5 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2">
                    <i class="fas fa-compass"></i> <?php echo e(__('messages.explore_nepal_btn')); ?>

                </a>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-8">

    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm border p-4 text-center hover:shadow-md transition">
            <p class="text-2xl font-bold text-blue-600"><?php echo e($bookingStats['upcoming'] ?? 0); ?></p>
            <p class="text-xs text-gray-500"><?php echo e(__('messages.traveler_stat_upcoming')); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-4 text-center hover:shadow-md transition">
            <p class="text-2xl font-bold text-green-600"><?php echo e($bookingStats['active'] ?? 0); ?></p>
            <p class="text-xs text-gray-500"><?php echo e(__('messages.traveler_stat_active')); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-4 text-center hover:shadow-md transition">
            <p class="text-2xl font-bold text-gray-800"><?php echo e($bookingStats['completed'] ?? 0); ?></p>
            <p class="text-xs text-gray-500"><?php echo e(__('messages.traveler_stat_completed')); ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-4 text-center hover:shadow-md transition">
            <p class="text-2xl font-bold text-purple-600"><?php echo e($reviews->count()); ?></p>
            <p class="text-xs text-gray-500"><?php echo e(__('messages.traveler_stat_reviews')); ?></p>
        </div>
    </div>

    
<div class="bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-600 rounded-2xl p-6 mb-6 text-white shadow-xl relative overflow-hidden group">
    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10"></div>
    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center">
        <div class="flex items-center gap-4">
            <div class="text-4xl">🎒</div>
            <div>
                <h3 class="text-xl font-bold"><?php echo e(__('messages.passport_card_title')); ?></h3>
                <p class="text-blue-100 text-sm">
                    <?php echo e(Auth::user()->passport_privacy === 'public' ? __('messages.passport_card_public') : __('messages.passport_card_private')); ?>

                </p>
            </div>
        </div>
        <div class="mt-4 md:mt-0 flex items-center gap-3 flex-wrap">
            
            <form action="<?php echo e(route('traveler.passport.toggle')); ?>" method="POST" class="inline">
                <?php echo csrf_field(); ?>
                <button type="submit"
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition-all
                            <?php echo e(Auth::user()->passport_privacy === 'public'
                                ? 'bg-yellow-500 hover:bg-yellow-600 text-white'
                                : 'bg-white/20 hover:bg-white/30 text-white'); ?>">
                    <?php echo e(Auth::user()->passport_privacy === 'public' ? __('messages.passport_card_make_private') : __('messages.passport_card_share_public')); ?>

                </button>
            </form>
            <a href="<?php echo e(route('traveler.passport')); ?>"
               class="bg-white text-blue-600 px-6 py-2.5 rounded-xl font-bold hover:bg-blue-50 transition-all shadow-lg hover:shadow-xl flex items-center gap-2 group-hover:scale-105 transform duration-200">
                <?php echo e(__('messages.passport_card_view_full')); ?>

            </a>
        </div>
    </div>
    <?php if(Auth::user()->passport_privacy === 'public'): ?>
        <div class="relative z-10 mt-3 text-sm text-blue-100">
            <?php echo e(__('messages.passport_card_share_label')); ?>

            <span class="font-mono text-xs bg-black/20 px-2 py-1 rounded"><?php echo e(url('/passport/' . Auth::user()->passport_public_id)); ?></span>
            <button onclick="navigator.clipboard?.writeText('<?php echo e(url('/passport/' . Auth::user()->passport_public_id)); ?>')"
                    class="ml-2 text-xs bg-white/20 px-2 py-1 rounded hover:bg-white/30 transition">
                <?php echo e(__('messages.passport_card_copy')); ?>

            </button>
        </div>
    <?php endif; ?>
</div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        
        <div class="lg:col-span-2 space-y-6">

            
            <?php if($activeTrip): ?>
                <div class="bg-white rounded-xl shadow-sm border p-6 hover:shadow-md transition">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-hiking text-blue-600"></i> <?php echo e(__('messages.traveler_active_trip_title')); ?>

                    </h3>
                    <div class="mt-3">
                        <h4 class="text-xl font-semibold text-gray-900"><?php echo e($activeTrip->service->name ?? __('messages.na')); ?></h4>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="far fa-calendar-alt mr-1"></i> 
                            <?php echo e($activeTrip->start_date ? $activeTrip->start_date->format('M d, Y') : __('messages.tbd')); ?>

                        </p>
                        <div class="flex flex-wrap items-center gap-3 mt-3">
                            <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-circle text-[6px] mr-1 align-middle"></i> <?php echo e(__('messages.traveler_active')); ?>

                            </span>
                            <span class="text-sm text-gray-500">
                                <?php echo e(__('messages.traveler_status_label')); ?>: <span class="font-medium text-gray-700"><?php echo e(ucfirst($activeTrip->status)); ?></span>
                            </span>
                            <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium ml-auto">
                                <?php echo e(__('messages.traveler_view_trek_passport')); ?> <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-xl shadow-sm border p-6 text-center">
                    <i class="fas fa-hiking text-4xl text-gray-300 mb-3"></i>
                    <h3 class="text-lg font-semibold text-gray-700"><?php echo e(__('messages.traveler_no_active_trip')); ?></h3>
                    <p class="text-sm text-gray-400"><?php echo e(__('messages.traveler_no_active_trip_sub')); ?></p>
                    <a href="<?php echo e(route('home')); ?>#ai-planner" class="inline-block mt-3 text-blue-600 hover:underline text-sm"><?php echo e(__('messages.traveler_start_planning')); ?> →</a>
                </div>
            <?php endif; ?>

            
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-calendar-check text-blue-600"></i> <?php echo e(__('messages.traveler_my_bookings')); ?>

                    </h3>
                    <span class="text-sm text-gray-400"><?php echo e($bookings->count()); ?> <?php echo e(__('messages.traveler_total')); ?></span>
                </div>

                <?php if($bookings->count() > 0): ?>
                    <div class="divide-y divide-gray-100">
                        <?php $__currentLoopData = $bookings->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="py-3 flex flex-wrap justify-between items-center gap-2">
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo e($booking->service->name ?? __('messages.na')); ?></p>
                                    <p class="text-xs text-gray-400">
                                        <i class="far fa-calendar-alt mr-1"></i> 
                                        <?php echo e($booking->start_date ? $booking->start_date->format('M d, Y') : __('messages.tbd')); ?>

                                    </p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        <?php if($booking->status === 'pending'): ?> bg-yellow-100 text-yellow-800
                                        <?php elseif($booking->status === 'confirmed'): ?> bg-blue-100 text-blue-800
                                        <?php elseif($booking->status === 'completed'): ?> bg-green-100 text-green-800
                                        <?php else: ?> bg-red-100 text-red-800 <?php endif; ?>">
                                        <?php if($booking->status === 'pending'): ?> <?php echo e(__('messages.pending')); ?>

                                        <?php elseif($booking->status === 'confirmed'): ?> <?php echo e(__('messages.confirmed')); ?>

                                        <?php elseif($booking->status === 'completed'): ?> <?php echo e(__('messages.completed')); ?>

                                        <?php else: ?> <?php echo e(__('messages.cancelled')); ?> <?php endif; ?>
                                    </span>
                                    <?php if($booking->status === 'completed' && !$booking->review): ?>
                                        <a href="<?php echo e(route('traveler.reviews.create', $booking)); ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            <i class="fas fa-star"></i> <?php echo e(__('messages.traveler_write_review')); ?>

                                        </a>
                                    <?php endif; ?>
                                    <?php if($booking->review): ?>
                                        <span class="text-sm text-green-600">✅ <?php echo e(__('messages.traveler_reviewed')); ?></span>
                                    <?php endif; ?>
                                    <a href="<?php echo e(route('traveler.bookings.show', $booking->id)); ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        <?php echo e(__('messages.view')); ?> <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php if($bookings->count() > 5): ?>
                        <div class="mt-3 text-center">
                            <a href="#" class="text-blue-600 hover:underline text-sm"><?php echo e(__('messages.traveler_view_all_bookings')); ?> →</a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-gray-500 text-center py-6"><?php echo e(__('messages.traveler_no_bookings_yet')); ?></p>
                    <div class="text-center">
                        <a href="<?php echo e(route('public.services.index')); ?>" class="text-blue-600 hover:underline text-sm"><?php echo e(__('messages.traveler_explore_services')); ?> →</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="space-y-6">

            
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                    <i class="fas fa-star text-yellow-500"></i> <?php echo e(__('messages.traveler_my_reviews')); ?>

                </h3>
                <?php if($reviews->count() > 0): ?>
                    <div class="space-y-3">
                        <?php $__currentLoopData = $reviews->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="border-b pb-2 last:border-0">
                                <div class="flex justify-between items-start">
                                    <span class="font-medium text-sm text-gray-800"><?php echo e($review->service->name ?? __('messages.na')); ?></span>
                                    <span class="text-yellow-500 text-sm"><?php echo e(str_repeat('⭐', $review->rating)); ?></span>
                                </div>
                                <p class="text-xs text-gray-500 line-clamp-1"><?php echo e($review->comment ?: __('messages.traveler_no_comment')); ?></p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php if($reviews->count() > 3): ?>
                        <div class="mt-3 text-center">
                            <a href="#" class="text-blue-600 hover:underline text-sm"><?php echo e(__('messages.traveler_view_all_reviews')); ?> →</a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-gray-400 text-sm text-center py-4"><?php echo e(__('messages.traveler_no_reviews_yet')); ?></p>
                <?php endif; ?>
            </div>

            
            <div class="bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                    <i class="fas fa-route text-green-600"></i> <?php echo e(__('messages.traveler_trek_history')); ?>

                </h3>
                <?php if($qrScans->count() > 0): ?>
                    <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                        <?php $__currentLoopData = $qrScans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $scan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex justify-between items-center border-b border-gray-100 pb-2 last:border-0">
                                <div>
                                    <p class="font-medium text-sm text-gray-800"><?php echo e($scan->booking->service->name ?? __('messages.na')); ?></p>
                                    <p class="text-xs text-gray-400">
                                        <i class="fas fa-map-pin mr-1 text-blue-500"></i> 
                                        <?php echo e($scan->checkpoint_name ?? __('messages.traveler_checkin_default')); ?>

                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-medium text-green-600">
                                        <i class="fas fa-check-circle mr-1"></i> <?php echo e(__('messages.traveler_checked_in')); ?>

                                    </span>
                                    <p class="text-[10px] text-gray-400"><?php echo e($scan->scanned_at ? $scan->scanned_at->format('M d, Y H:i') : __('messages.na')); ?></p>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php if($qrScans->count() > 10): ?>
                        <div class="mt-3 text-center">
                            <a href="#" class="text-blue-600 hover:underline text-sm"><?php echo e(__('messages.traveler_view_all_history')); ?> →</a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-route text-3xl text-gray-300 mb-2"></i>
                        <p class="text-gray-400 text-sm"><?php echo e(__('messages.traveler_no_trek_history')); ?></p>
                        <p class="text-xs text-gray-400"><?php echo e(__('messages.traveler_no_trek_history_sub')); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl p-6 text-white shadow-lg hover:shadow-xl transition">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-robot text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold"><?php echo e(__('messages.traveler_ai_planner_card_title')); ?></h3>
                        <p class="text-blue-100 text-sm mt-1 max-w-md">
                            <?php echo e(__('messages.traveler_ai_planner_card_desc')); ?>

                        </p>
                        <a href="<?php echo e(route('home')); ?>#ai-planner" class="inline-block mt-4 bg-white text-blue-600 hover:bg-gray-100 px-6 py-2.5 rounded-lg text-sm font-semibold transition shadow-md hover:shadow-lg">
                            <?php echo e(__('messages.traveler_create_ai_itinerary')); ?> <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            
<div class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition">
    <div class="flex items-start gap-3">
        <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-images text-green-500 text-xl"></i>
        </div>
        <div class="flex-1">
            <h4 class="font-semibold text-gray-800">📸 My Travel Memories</h4>
            <p class="text-xs text-gray-500 mt-0.5">Upload photos & videos from your journey checkpoints.</p>
            
            
            <form id="uploadForm" class="mt-3" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="flex flex-wrap gap-2">
                    <select name="checkpoint" id="checkpointSelect" class="flex-1 min-w-[150px] text-sm border border-gray-300 rounded-lg px-3 py-2" required>
                        <option value="">Select a checkpoint...</option>
                        <?php $__currentLoopData = $userWaypoints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($wp->name); ?>"><?php echo e($wp->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <input type="file" name="media" id="fileInput" accept="image/*,video/*" class="flex-1 min-w-[150px] text-sm border border-gray-300 rounded-lg px-3 py-2 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700" required>
                    <button type="submit" id="uploadBtn" class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:shadow-lg transition">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </div>
                <div id="uploadMessage" class="mt-2 text-sm hidden"></div>
            </form>

            
            <?php if(session('upload_success')): ?>
                <div class="mt-2 text-sm text-green-600"><?php echo e(session('upload_success')); ?></div>
            <?php endif; ?>
            <?php if(session('upload_error')): ?>
                <div class="mt-2 text-sm text-red-600"><?php echo e(session('upload_error')); ?></div>
            <?php endif; ?>

            
            <?php if($userMedia->count() > 0): ?>
                <div class="mt-4 grid grid-cols-3 gap-2">
                    <?php $__currentLoopData = $userMedia->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="relative group rounded-lg overflow-hidden border border-gray-200 aspect-square bg-gray-100">
                            <?php if($media->media_type === 'image'): ?>
                                <img src="<?php echo e(asset('storage/' . $media->optimized_path)); ?>" alt="<?php echo e($media->file_name); ?>" class="w-full h-full object-cover">
                            <?php endif; ?>
                            <form action="<?php echo e(route('traveler.memory.delete')); ?>" method="POST" class="absolute top-1 right-1">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <input type="hidden" name="id" value="<?php echo e($media->id); ?>">
                                <button type="submit" onclick="return confirm('Delete this memory?')" class="bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">×</button>
                            </form>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php if($userMedia->count() > 6): ?>
                    <p class="text-xs text-gray-400 mt-2">+<?php echo e($userMedia->count() - 6); ?> more</p>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-xs text-gray-400 mt-3">No memories uploaded yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

            
<div class="bg-gradient-to-r from-purple-500 to-pink-600 rounded-xl p-5 text-white shadow-lg hover:shadow-xl transition group">
    <div class="flex items-start gap-3">
        <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center flex-shrink-0">
            <i class="fas fa-film text-2xl"></i>
        </div>
        <div class="flex-1">
            <h4 class="font-bold text-lg">🎬 My Journey Replay</h4>
            <p class="text-sm text-purple-100 mt-0.5">Turn your TravelAI Nepal experiences into a beautiful travel memory.</p>
            <a href="<?php echo e(route('traveler.journey-replay')); ?>" 
               class="inline-block mt-2 bg-white text-purple-600 hover:bg-gray-100 px-4 py-1.5 rounded-lg text-sm font-semibold transition shadow group-hover:scale-105 transform duration-200">
                Relive Your Journey →
            </a>
        </div>
    </div>
</div>

            
<div class="bg-white rounded-xl shadow-sm border p-5 hover:shadow-md transition">
    <div class="flex items-start gap-3">
        <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-shield-alt text-red-500 text-xl"></i>
        </div>
        <div class="flex-1">
            <div class="flex justify-between items-start">
                <div>
                    <h4 class="font-semibold text-gray-800"><?php echo e(__('messages.traveler_safety_center_title')); ?></h4>
                    <p class="text-xs text-gray-500 mt-0.5"><?php echo e(__('messages.traveler_safety_center_desc')); ?></p>
                </div>
                <?php if(isset($unreadAlerts) && count($unreadAlerts) > 0): ?>
                    <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full"><?php echo e(count($unreadAlerts)); ?></span>
                <?php endif; ?>
            </div>

            
            <?php if(isset($unreadAlerts) && count($unreadAlerts) > 0): ?>
                <div class="mt-3 space-y-2 max-h-60 overflow-y-auto">
                    <?php $__currentLoopData = $unreadAlerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="border-l-4 
                            <?php if($alert['severity'] === 'critical'): ?> border-red-600
                            <?php elseif($alert['severity'] === 'high'): ?> border-orange-500
                            <?php elseif($alert['severity'] === 'moderate'): ?> border-yellow-500
                            <?php else: ?> border-green-500 <?php endif; ?>
                            bg-gray-50 p-3 rounded-r-lg">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-medium text-sm">
                                        <?php if($alert['severity'] === 'critical'): ?> 🔴
                                        <?php elseif($alert['severity'] === 'high'): ?> 🟠
                                        <?php elseif($alert['severity'] === 'moderate'): ?> 🟡
                                        <?php else: ?> 🟢 <?php endif; ?>
                                        <?php echo e($alert['incident']['title'] ?? $alert['message'] ?? 'Safety Alert'); ?>

                                    </p>
                                    <p class="text-xs text-gray-600"><?php echo e($alert['message']); ?></p>
                                    <p class="text-xs text-gray-400 mt-1">
    <?php echo e(isset($alert['sent_at']) ? \Carbon\Carbon::parse($alert['sent_at'])->diffForHumans() : 'Just now'); ?>

</p>
                                </div>
                                <form method="POST" action="<?php echo e(route('traveler.alert.read', $alert['id'])); ?>" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="text-xs text-blue-600 hover:text-blue-800">Mark as Read</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <div class="text-center py-3">
                    <p class="text-gray-500 text-sm">✅ No safety alerts at this time.</p>
                </div>
            <?php endif; ?>

            <div class="mt-3">
                <a href="<?php echo e(route('safety.index')); ?>" class="text-sm text-blue-600 hover:underline">
                    View Safety Map →
                </a>
            </div>
        </div>
    </div>
</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('uploadForm');
    const btn = document.getElementById('uploadBtn');
    const msg = document.getElementById('uploadMessage');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
        msg.classList.add('hidden');

        fetch('<?php echo e(route("traveler.checkpoint.upload")); ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('✅ ' + data.message, 'green');
                setTimeout(() => location.reload(), 1000);
            } else {
                showMessage('❌ ' + data.message, 'red');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-upload"></i> Upload';
            }
        })
        .catch(error => {
            showMessage('❌ Network error', 'red');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-upload"></i> Upload';
        });
    });

    function showMessage(text, color) {
        msg.textContent = text;
        msg.className = 'mt-2 text-sm ' + (color === 'red' ? 'text-red-600' : 'text-green-600');
        msg.classList.remove('hidden');
    }
});
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\TravelAI-Nepal\resources\views/traveler/dashboard.blade.php ENDPATH**/ ?>