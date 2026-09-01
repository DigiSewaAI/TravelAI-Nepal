// You can import this in app.js or include directly.
document.addEventListener('DOMContentLoaded', function () {
    const shareBtn = document.getElementById('share-journey-btn');
    if (shareBtn) {
        shareBtn.addEventListener('click', function () {
            const url = this.dataset.url;
            if (navigator.share) {
                navigator.share({
                    title: 'My Journey',
                    text: 'Check out my journey with TravelAI Nepal!',
                    url: url
                }).catch(() => {});
            } else {
                // Fallback: copy link
                navigator.clipboard.writeText(url).then(() => {
                    alert('Link copied to clipboard!');
                });
            }
        });
    }
});