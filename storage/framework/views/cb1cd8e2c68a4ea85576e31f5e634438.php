<div class="bg-white rounded-xl shadow-sm border p-6 mt-6">
    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
        <i class="fas fa-share-alt text-blue-600"></i> Share My Journey
    </h3>

    <div class="mt-4 flex flex-wrap gap-3 items-center">
        <span class="text-sm text-gray-500">Visibility:</span>
        <span class="px-3 py-1 rounded-full text-sm font-medium
            <?php if($booking->visibility === 'private'): ?> bg-gray-200 text-gray-700
            <?php elseif($booking->visibility === 'link'): ?> bg-yellow-100 text-yellow-800
            <?php else: ?> bg-green-100 text-green-800 <?php endif; ?>">
            <?php if($booking->visibility === 'private'): ?> 🔒 Private
            <?php elseif($booking->visibility === 'link'): ?> 🔗 Anyone with link
            <?php else: ?> 🌍 Public <?php endif; ?>
        </span>
    </div>

    <form action="<?php echo e(route('traveler.share.toggle', $booking)); ?>" method="POST" class="mt-3 flex flex-wrap gap-2">
        <?php echo csrf_field(); ?>
        <select name="visibility" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="private" <?php echo e($booking->visibility === 'private' ? 'selected' : ''); ?>>🔒 Private</option>
            <option value="link" <?php echo e($booking->visibility === 'link' ? 'selected' : ''); ?>>🔗 Anyone with link</option>
            <option value="public" <?php echo e($booking->visibility === 'public' ? 'selected' : ''); ?>>🌍 Public</option>
        </select>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Update</button>
    </form>

    <?php if($booking->isShareable()): ?>
        <div class="mt-3 flex flex-wrap gap-2">
            <input type="text" readonly value="<?php echo e($booking->share_url); ?>" class="border border-gray-300 rounded-lg px-3 py-2 text-sm flex-1 min-w-[200px]" id="shareUrlInput">
            <button onclick="navigator.clipboard.writeText(document.getElementById('shareUrlInput').value).then(()=>alert('Link copied!'))" class="bg-gray-700 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-800">📋 Copy</button>
            <form action="<?php echo e(route('traveler.share.revoke', $booking)); ?>" method="POST" class="inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700">Revoke</button>
            </form>
            <form action="<?php echo e(route('traveler.share.regenerate', $booking)); ?>" method="POST" class="inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-yellow-700">Regenerate Link</button>
            </form>
        </div>
        <div class="mt-3 flex flex-wrap gap-2">
            <button onclick="window.open('https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent('<?php echo e($booking->share_url); ?>'), '_blank')" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">📘 Facebook</button>
            <button onclick="window.open('https://wa.me/?text='+encodeURIComponent('Check out my journey! <?php echo e($booking->share_url); ?>'), '_blank')" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">💬 WhatsApp</button>
        </div>
    <?php else: ?>
        <p class="text-sm text-gray-400 mt-3">Enable sharing to get a shareable link.</p>
    <?php endif; ?>
</div><?php /**PATH C:\laragon\www\TravelAI-Nepal\resources\views/traveler/share-management.blade.php ENDPATH**/ ?>