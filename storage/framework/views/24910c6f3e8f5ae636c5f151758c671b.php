<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(__('messages.login_page_title')); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; }
    </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
            <!-- ===== LOGO & HEADER ===== -->
            <div class="text-center mb-6">
                <img src="<?php echo e(asset('images/logo.png')); ?>"
                     alt="<?php echo e(__('messages.app_name')); ?>"
                     class="h-16 mx-auto mb-2">
                <h1 class="text-2xl font-bold text-gray-800"><?php echo e(__('messages.login_welcome')); ?></h1>
                <p class="text-gray-500"><?php echo e(__('messages.login_subtitle')); ?></p>
            </div>

            <?php if($errors->any()): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded">
                    <ul class="list-disc list-inside text-sm">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('login')); ?>">
                <?php echo csrf_field(); ?>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2"><?php echo e(__('messages.email')); ?></label>
                    <input type="email" name="email" value="<?php echo e(old('email')); ?>" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2"><?php echo e(__('messages.password')); ?></label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center text-sm">
                        <input type="checkbox" name="remember" class="mr-2">
                        <?php echo e(__('messages.remember_me')); ?>

                    </label>
                    <span class="text-sm text-gray-400"><?php echo e(__('messages.forgot_password_contact')); ?></span>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <?php echo e(__('messages.login')); ?>

                </button>
            </form>

            <p class="text-center text-gray-600 text-sm mt-6">
                <?php echo e(__('messages.no_account')); ?>

                <a href="<?php echo e(route('register')); ?>" class="text-blue-600 hover:underline"><?php echo e(__('messages.register_here')); ?></a>
            </p>

            
            <div class="mt-4 text-center text-sm">
                <span class="text-gray-400"><?php echo e(__('messages.or')); ?></span>
                
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
</body>
</html><?php /**PATH C:\laragon\www\TravelAI-Nepal\resources\views/auth/login.blade.php ENDPATH**/ ?>