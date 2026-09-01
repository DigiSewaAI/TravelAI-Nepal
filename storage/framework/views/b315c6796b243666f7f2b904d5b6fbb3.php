<?php if(isset($ogTitle)): ?>
    <meta property="og:title" content="<?php echo e($ogTitle); ?>" />
    <meta property="og:description" content="<?php echo e($ogDescription ?? 'Journey with TravelAI Nepal'); ?>" />
    <meta property="og:image" content="<?php echo e($ogImage ?? asset('images/default-share.jpg')); ?>" />
    <meta property="og:url" content="<?php echo e($ogUrl ?? url()->current()); ?>" />
    <meta property="og:type" content="website" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo e($ogTitle); ?>" />
    <meta name="twitter:description" content="<?php echo e($ogDescription ?? ''); ?>" />
    <meta name="twitter:image" content="<?php echo e($ogImage ?? asset('images/default-share.jpg')); ?>" />
<?php endif; ?><?php /**PATH C:\laragon\www\TravelAI-Nepal\resources\views/partials/og-meta.blade.php ENDPATH**/ ?>