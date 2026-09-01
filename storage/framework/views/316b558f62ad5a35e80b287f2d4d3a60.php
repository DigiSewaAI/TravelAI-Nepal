<?php $__env->startSection('title', __('messages.pricing_page_title')); ?>
<?php $__env->startSection('content'); ?>


<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16 px-4 text-center">
    <h1 class="text-4xl md:text-5xl font-bold"><?php echo e(__('messages.pricing_hero_title')); ?></h1>
    <p class="text-blue-100 text-lg mt-2 max-w-2xl mx-auto">
        <?php echo e(__('messages.pricing_hero_subtitle')); ?>

    </p>
</div>

<div class="max-w-7xl mx-auto px-4 py-12">

    
    <div class="text-center mb-12">
        <div class="inline-flex items-center bg-gray-100 rounded-full p-1 shadow-sm" role="group" aria-label="<?php echo e(__('messages.billing_interval')); ?>">
            <button type="button" class="billing-toggle px-6 py-2 rounded-full text-sm font-semibold transition bg-blue-600 text-white" data-interval="monthly" aria-pressed="true"><?php echo e(__('messages.monthly')); ?></button>
            <button type="button" class="billing-toggle px-6 py-2 rounded-full text-sm font-semibold transition text-gray-600 hover:bg-gray-200" data-interval="yearly" aria-pressed="false"><?php echo e(__('messages.yearly')); ?></button>
        </div>
        <div id="billing-badge" class="mt-2 text-green-600 text-sm font-medium hidden">🎁 <?php echo e(__('messages.two_months_free')); ?></div>
    </div>

    
    <div class="grid md:grid-cols-4 gap-6">
        <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-white rounded-2xl shadow-lg border p-6 hover:shadow-xl transition group
                    <?php echo e($plan->slug === 'free' ? 'border-gray-200' : ''); ?>

                    <?php echo e($plan->slug === 'professional' ? 'border-blue-500 shadow-blue-100 relative' : ''); ?>

                    <?php echo e($plan->slug === 'business' ? 'border-purple-500 shadow-purple-100' : ''); ?>

                    <?php echo e($plan->slug === 'enterprise' ? 'border-amber-500 shadow-amber-100' : ''); ?>">
            
            <?php if($plan->slug === 'professional'): ?>
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-blue-600 text-white text-xs font-bold px-4 py-1 rounded-full"><?php echo e(__('messages.most_popular')); ?></span>
            <?php endif; ?>
            <?php if($plan->slug === 'enterprise'): ?>
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-amber-600 text-white text-xs font-bold px-4 py-1 rounded-full"><?php echo e(__('messages.best_value')); ?></span>
            <?php endif; ?>

            <div class="mt-2">
                <h3 class="text-2xl font-bold text-gray-900"><?php echo e($plan->name); ?></h3>
                
                <p class="text-gray-500 text-sm mt-1"><?php echo e(__('messages.plans.' . $plan->slug . '.description')); ?></p>
            </div>

            
<div class="mt-4">
    
    <div class="price-amount" data-interval="monthly">
        <?php if($plan->price_monthly === null): ?>
            <span class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.custom')); ?></span>
        <?php elseif($plan->price_monthly == 0): ?>
            <span class="text-3xl font-bold text-green-600"><?php echo e(__('messages.free')); ?></span>
        <?php else: ?>
            <span class="text-3xl font-bold text-gray-900">Rs. <?php echo e(number_format($plan->price_monthly, 0)); ?></span>
            <span class="text-gray-500 text-sm">/ <?php echo e(__('messages.month')); ?></span>
        <?php endif; ?>
    </div>

    
    <div class="price-amount hidden" data-interval="yearly">
        <?php if($plan->price_yearly === null): ?>
            <span class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.custom')); ?></span>
        <?php elseif($plan->price_yearly == 0): ?>
            <span class="text-3xl font-bold text-green-600"><?php echo e(__('messages.free')); ?></span>
        <?php else: ?>
            <span class="text-3xl font-bold text-gray-900">Rs. <?php echo e(number_format($plan->price_yearly, 0)); ?></span>
            <span class="text-gray-500 text-sm">/ <?php echo e(__('messages.year')); ?></span>
            <?php if($plan->price_monthly !== null && $plan->price_monthly > 0): ?>
                <div class="text-sm text-gray-400 mt-1">≈ Rs. <?php echo e(number_format($plan->price_yearly / 12, 0)); ?>/<?php echo e(__('messages.month')); ?></div>
                <div class="text-xs text-green-600 font-medium">🟢 <?php echo e(__('messages.save_two_months')); ?></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

            
            <ul class="mt-6 space-y-2 text-sm">
                <?php $features = $plan->features ?? []; ?>
                <?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        // Generate translation key from feature string
                        $featureKey = 'plans.features.' . \Illuminate\Support\Str::slug($feature, '_');
                    ?>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                        <span><?php echo e(__('messages.' . $featureKey)); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>

            
            <?php if($plan->limits): ?>
                <div class="mt-4 pt-4 border-t text-xs text-gray-500">
                    <?php $limits = $plan->limits; ?>
                    <?php if(isset($limits['max_listings'])): ?>
                        <div>📦 <?php echo e($limits['max_listings'] == -1 ? __('messages.unlimited') : $limits['max_listings']); ?> <?php echo e(__('messages.services')); ?></div>
                    <?php endif; ?>
                    <?php if(isset($limits['max_staff'])): ?>
                        <div>👥 <?php echo e($limits['max_staff'] == -1 ? __('messages.unlimited') : $limits['max_staff']); ?> <?php echo e(__('messages.staff')); ?></div>
                    <?php endif; ?>
                    <?php if(isset($limits['max_ai_requests'])): ?>
                        <div>🤖 <?php echo e($limits['max_ai_requests'] == -1 ? __('messages.unlimited') : $limits['max_ai_requests']); ?> <?php echo e(__('messages.ai_requests_mo')); ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            
            <div class="mt-6">
                <?php if($plan->slug === 'free'): ?>
                    <a href="<?php echo e(route('register')); ?>" class="w-full block text-center bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold py-3 rounded-xl transition"><?php echo e(__('messages.get_started_free')); ?></a>
                <?php elseif($plan->slug === 'enterprise'): ?>
                    <a href="mailto:sales@travelai.com?subject=Enterprise%20Plan%20Inquiry" class="w-full block text-center bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 rounded-xl transition shadow-md"><?php echo e(__('messages.contact_for_pricing')); ?></a>
                <?php else: ?>
                    <a href="<?php echo e(route('register', ['plan' => $plan->slug, 'billing_interval' => 'monthly'])); ?>" class="cta-button w-full block text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition shadow-md" data-interval="monthly"><?php echo e(__('messages.choose_plan_btn', ['name' => $plan->name])); ?></a>
                    <a href="<?php echo e(route('register', ['plan' => $plan->slug, 'billing_interval' => 'yearly'])); ?>" class="cta-button hidden w-full block text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition shadow-md" data-interval="yearly"><?php echo e(__('messages.choose_plan_btn', ['name' => $plan->name])); ?></a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="mt-16 bg-white rounded-2xl shadow-lg border overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b">
            <h3 class="text-xl font-bold text-gray-800">📊 <?php echo e(__('messages.plan_comparison')); ?></h3>
        </div>
        <div class="overflow-x-auto p-6">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 font-semibold text-gray-700"><?php echo e(__('messages.feature')); ?></th>
                        <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th class="text-center py-3 font-semibold text-gray-700"><?php echo e($plan->name); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b">
                        <td class="py-3 text-gray-600"><?php echo e(__('messages.price')); ?></td>
                        <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td class="text-center py-3 font-medium">
                                <span class="price-amount" data-interval="monthly">
                                    <?php if($plan->price_monthly === null): ?> <?php echo e(__('messages.custom')); ?>

                                    <?php elseif($plan->price_monthly == 0): ?> <?php echo e(__('messages.free')); ?>

                                    <?php else: ?> Rs. <?php echo e(number_format($plan->price_monthly, 0)); ?>/mo
                                    <?php endif; ?>
                                </span>
                                <span class="price-amount hidden" data-interval="yearly">
                                    <?php if($plan->price_yearly === null): ?> <?php echo e(__('messages.custom')); ?>

                                    <?php elseif($plan->price_yearly == 0): ?> <?php echo e(__('messages.free')); ?>

                                    <?php else: ?> Rs. <?php echo e(number_format($plan->price_yearly, 0)); ?>/yr
                                    <?php endif; ?>
                                </span>
                            </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>

                    
                    <tr class="border-b">
                        <td class="py-3 text-gray-600"><?php echo e(__('messages.services')); ?></td>
                        <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $value = $plan->limits['max_listings'] ?? null; ?>
                            <td class="text-center py-3">
                                <?php echo e($value == -1 ? __('messages.unlimited') : ($value ?? '∞')); ?>

                            </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>

                    
                    <tr class="border-b">
                        <td class="py-3 text-gray-600"><?php echo e(__('messages.staff_users')); ?></td>
                        <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $value = $plan->limits['max_staff'] ?? null; ?>
                            <td class="text-center py-3">
                                <?php echo e($value == -1 ? __('messages.unlimited') : ($value ?? '∞')); ?>

                            </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>

                    
                    <tr class="border-b">
                        <td class="py-3 text-gray-600"><?php echo e(__('messages.ai_requests')); ?></td>
                        <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $value = $plan->limits['max_ai_requests'] ?? null; ?>
                            <td class="text-center py-3">
                                <?php echo e($value == -1 ? __('messages.unlimited') : ($value ?? '∞')); ?>

                            </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>

                    
                    <tr>
                        <td class="py-3 text-gray-600"><?php echo e(__('messages.custom_logo')); ?></td>
                        <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td class="text-center py-3">
                                <?php if(in_array('Custom Logo', $plan->features ?? [])): ?>
                                    <i class="fas fa-check-circle text-green-500"></i>
                                <?php else: ?>
                                    <i class="fas fa-times-circle text-gray-300"></i>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    
    <div class="max-w-3xl mx-auto mt-16">
        <h2 class="text-2xl font-bold text-center text-gray-900 mb-8"><?php echo e(__('messages.faq_title')); ?></h2>
        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-md transition">
                <h3 class="font-semibold text-gray-800"><?php echo e(__('messages.faq_q1')); ?></h3>
                <p class="text-gray-600 mt-1"><?php echo e(__('messages.faq_a1')); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-md transition">
                <h3 class="font-semibold text-gray-800"><?php echo e(__('messages.faq_q2')); ?></h3>
                <p class="text-gray-600 mt-1"><?php echo e(__('messages.faq_a2')); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-md transition">
                <h3 class="font-semibold text-gray-800"><?php echo e(__('messages.faq_q3')); ?></h3>
                <p class="text-gray-600 mt-1"><?php echo e(__('messages.faq_a3')); ?></p>
            </div>
            <div class="bg-white rounded-xl shadow p-6 hover:shadow-md transition">
                <h3 class="font-semibold text-gray-800"><?php echo e(__('messages.faq_q4')); ?></h3>
                <p class="text-gray-600 mt-1"><?php echo e(__('messages.faq_a4')); ?></p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtns = document.querySelectorAll('.billing-toggle');
        const badge = document.getElementById('billing-badge');

        function setInterval(interval) {
            toggleBtns.forEach(btn => {
                const isActive = btn.dataset.interval === interval;
                btn.classList.toggle('bg-blue-600', isActive);
                btn.classList.toggle('text-white', isActive);
                btn.classList.toggle('text-gray-600', !isActive);
                btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });

            document.querySelectorAll('.price-amount').forEach(el => {
                el.classList.toggle('hidden', el.dataset.interval !== interval);
            });
            document.querySelectorAll('.cta-button').forEach(el => {
                el.classList.toggle('hidden', el.dataset.interval !== interval);
            });

            badge.classList.toggle('hidden', interval !== 'yearly');
        }

        toggleBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                setInterval(this.dataset.interval);
            });
        });

        setInterval('monthly');
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\TravelAI-Nepal\resources\views/public/pricing.blade.php ENDPATH**/ ?>