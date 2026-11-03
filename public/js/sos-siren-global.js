/**
 * SOS Global Siren — plays on ANY provider page when new alert arrives.
 * - First visit: one click enables audio (browser policy)
 * - After enable: auto-rings when polling detects active SOS
 * - Loops every 5s until user acknowledges
 * - New alert arriving later re-triggers
 */
(function() {
    if (window.__sosSirenLoaded) return;
    window.__sosSirenLoaded = true;

    var POLL_URL = '/provider/sos-alerts/active-count';
    var POLL_MS = 15000;
    var enabled = localStorage.getItem('sos_audio_enabled') === 'true';
    var ackKey = 'sos_ack_latest_id';
    var ackedLatestId = parseInt(sessionStorage.getItem(ackKey) || '0', 10);
    var audioCtx = null;
    var ringing = false;
    var ringInterval = null;
    var lastLatestId = null;

    function playSiren() {
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (audioCtx.state === 'suspended') audioCtx.resume();
            var osc = audioCtx.createOscillator();
            var gain = audioCtx.createGain();
            osc.connect(gain); gain.connect(audioCtx.destination);
            osc.type = 'sawtooth';
            var t = audioCtx.currentTime;
            osc.frequency.setValueAtTime(600, t);
            gain.gain.setValueAtTime(0.35, t);
            for (var i = 0; i < 4; i++) {
                osc.frequency.linearRampToValueAtTime(1100, t + 0.3 + i*0.6);
                osc.frequency.linearRampToValueAtTime(600, t + 0.6 + i*0.6);
            }
            gain.gain.linearRampToValueAtTime(0.01, t + 2.4);
            osc.start(t); osc.stop(t + 2.5);
        } catch (e) { console.warn('SOS siren:', e); }
    }

    function startRinging() {
        if (ringing) return;
        ringing = true;
        playSiren();
        ringInterval = setInterval(playSiren, 5000);
        showBar();
    }

    function stopRinging() {
        ringing = false;
        if (ringInterval) { clearInterval(ringInterval); ringInterval = null; }
        hideBar();
    }

    function showBar() {
        if (document.getElementById('sosGlobalBar')) return;
        var bar = document.createElement('div');
        bar.id = 'sosGlobalBar';
        bar.style.cssText = 'position:fixed;top:20px;left:50%;transform:translateX(-50%);z-index:999999;background:#dc2626;color:#fff;padding:14px 22px;border-radius:14px;font-weight:800;box-shadow:0 10px 40px rgba(220,38,38,.6);display:flex;align-items:center;gap:14px;font-size:14px;max-width:90vw;';
        bar.innerHTML = '<span style="font-size:20px;">🔔</span><span>Active SOS Alert — Click Silence when reviewed</span><button id="sosGlobalAck" style="background:#fff;color:#dc2626;border:0;padding:8px 16px;border-radius:10px;font-weight:900;cursor:pointer;font-size:13px;">🔕 Silence</button>';
        document.body.appendChild(bar);
        document.getElementById('sosGlobalAck').addEventListener('click', function(e) {
            e.stopPropagation();
            var latest = lastLatestId || 0;
            sessionStorage.setItem(ackKey, String(latest));
            ackedLatestId = latest;
            stopRinging();
        });
    }

    function hideBar() {
        var b = document.getElementById('sosGlobalBar');
        if (b) b.remove();
    }

    function enableAudio() {
        if (enabled) return Promise.resolve();
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            return audioCtx.resume().then(function() {
                enabled = true;
                localStorage.setItem('sos_audio_enabled', 'true');
                playSiren();
            });
        } catch (e) { return Promise.resolve(); }
    }

    // First interaction anywhere = enable audio
    ['click', 'keydown', 'touchstart'].forEach(function(evt) {
        document.addEventListener(evt, enableAudio, { once: true, passive: true });
    });

    function poll() {
        fetch(POLL_URL, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(function(r) { return r.ok ? r.json() : null; })
            .then(function(data) {
                if (!data || typeof data.count !== 'number') return;
                var latest = data.latest_id || 0;
                if (data.count > 0 && latest > ackedLatestId && enabled) {
                    lastLatestId = latest;
                    startRinging();
                } else if (data.count === 0) {
                    stopRinging();
                }
                if (lastLatestId === null) lastLatestId = latest;
            }).catch(function() {});
    }

    // Initial poll after page load
    setTimeout(poll, 3000);
    setInterval(poll, POLL_MS);
})();