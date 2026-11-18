import './bootstrap';
// ========== TIMEZONE UTILITIES ==========
window.timeAgo = function (iso) {
    if (!iso) return '';
    const past = new Date(iso).getTime();
    if (isNaN(past)) return '';
    const diff = Math.floor((Date.now() - past) / 1000);
    if (diff < 0)     return 'just now';
    if (diff < 60)    return diff + ' sec ago';
    if (diff < 3600)  return Math.floor(diff / 60) + ' min ago';
    if (diff < 86400) return Math.floor(diff / 3600) + ' hr ago';
    if (diff < 604800) return Math.floor(diff / 86400) + ' days ago';
    return new Date(iso).toLocaleDateString();
};

window.localTime = function (iso, opts) {
    if (!iso) return '';
    const d = new Date(iso);
    if (isNaN(d.getTime())) return '';
    return d.toLocaleString('en-US', opts || {
        month: 'short', day: '2-digit', year: 'numeric',
        hour: 'numeric', minute: '2-digit', hour12: true
    });
};

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-ago]').forEach(function (el) {
        el.textContent = window.timeAgo(el.dataset.ago);
    });
    document.querySelectorAll('[data-local-time]').forEach(function (el) {
        el.textContent = window.localTime(el.dataset.localTime);
    });
});
