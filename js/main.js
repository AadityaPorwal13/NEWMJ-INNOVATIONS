/* ═══════════════════════════════════════════════
   MJ INNOVATIONS FZ LLC — main.js v5
   Shared across all pages
═══════════════════════════════════════════════ */

'use strict';

/* ── Scroll-triggered animations ── */
const motionObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('vis');
      motionObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

document.querySelectorAll('.fade-up, .fade-in, .stagger, .scale-in, .reveal-text').forEach(el => {
  motionObserver.observe(el);
});

/* ── Sticky nav ── */
const nav = document.getElementById('main-nav');
if (nav) {
  window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 30);
  }, { passive: true });
}

/* ── Mobile hamburger ── */
const hamburger = document.getElementById('hamburger');
const drawer    = document.getElementById('drawer');
if (hamburger && drawer) {
  hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('open');
    drawer.classList.toggle('open');
  });
  document.addEventListener('click', (e) => {
    if (nav && !nav.contains(e.target) && !drawer.contains(e.target)) {
      hamburger.classList.remove('open');
      drawer.classList.remove('open');
    }
  });
}

/* ── Live clock — Gulf Standard Time (UTC+4) ── */
function updateClock() {
  const el = document.getElementById('live-time');
  if (!el) return;
  const now = new Date();
  // GST = UTC + 4 hours
  const gstOffset = 4 * 60; // minutes
  const utcMs = now.getTime() + now.getTimezoneOffset() * 60000;
  const gst = new Date(utcMs + gstOffset * 60000);
  const hh = String(gst.getHours()).padStart(2,'0');
  const mm = String(gst.getMinutes()).padStart(2,'0');
  const ss = String(gst.getSeconds()).padStart(2,'0');
  el.textContent = `${hh}:${mm}:${ss} GST`;
}
updateClock();
setInterval(updateClock, 1000);

/* ── Simulated alert counter ── */
(function simulateAlerts() {
  const el = document.getElementById('alert-count');
  if (!el) return;
  setInterval(() => {
    if (Math.random() > 0.75) {
      const count = Math.floor(Math.random() * 6) + 1;
      el.textContent = count + ' Alert' + (count !== 1 ? 's' : '');
    }
  }, 4000);
})();

/* ── Tech tabs (technology.html) ── */
document.querySelectorAll('.tech-tab').forEach(tab => {
  tab.addEventListener('click', () => {
    const target = tab.dataset.tab;
    document.querySelectorAll('.tech-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.tech-panel').forEach(p => p.classList.remove('active'));
    tab.classList.add('active');
    const panel = document.getElementById('panel-' + target);
    if (panel) panel.classList.add('active');
  });
});

/* ── X Device tab switcher ── */
document.querySelectorAll('.xdev-tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.xdev-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.xdev-panel').forEach(p => p.classList.remove('active'));
    tab.classList.add('active');
    const panel = document.getElementById(tab.dataset.panel);
    if (panel) panel.classList.add('active');
  });
});

/* ── Animate detection box confidence labels ── */
(function animateDetBoxes() {
  document.querySelectorAll('.dbox').forEach((box, i) => {
    const label = box.querySelector('.dbox-label');
    if (!label) return;
    const parts = label.textContent.split('·');
    if (parts.length < 2) return;
    setInterval(() => {
      const conf = Math.floor(Math.random() * 5) + 93;
      label.textContent = parts[0].trim() + ' · ' + conf + '%';
    }, 2500 + i * 700);
  });
})();

/* ── Smooth scroll for anchor links ── */
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if (target) {
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
});

/* ── Illustration tab switcher ── */
window.switchIllus = function(idx) {
  document.querySelectorAll('.illus-tab').forEach((t, i) => {
    t.classList.toggle('active', i === idx);
  });
  document.querySelectorAll('.illus-panel').forEach((p, i) => {
    const isActive = i === idx;
    p.classList.toggle('active', isActive);
    if (isActive) {
      const si = p.querySelector('.scale-in');
      if (si) {
        si.classList.remove('vis');
        requestAnimationFrame(() => setTimeout(() => si.classList.add('vis'), 30));
      }
    }
  });
};

/* ── Solution card video — play on hover ── */
document.querySelectorAll('.sol-card').forEach(card => {
  const video = card.querySelector('video');
  if (!video) return;
  video.muted = true;
  video.loop  = true;
  video.playsInline = true;
  // Autoplay all solution videos silently
  video.play().catch(() => {});
});

/* ── Lazy load videos when visible ── */
const vidObserver = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      const v = e.target;
      v.play().catch(() => {});
      vidObserver.unobserve(v);
    }
  });
}, { threshold: 0.2 });
document.querySelectorAll('video[data-lazy]').forEach(v => vidObserver.observe(v));

/* ══════════════════════════════════════════════
   ILLUSTRATION SLIDER — unified desktop + mobile
═════════════════════════════════════════════ */
(function initIllustrationSlider() {
  const track     = document.getElementById('islider-track');
  if (!track) return;

  const slides    = Array.from(track.querySelectorAll('.islide'));
  const trackWrap = track.parentElement;
  const btnPrev   = document.getElementById('islider-prev');
  const btnNext   = document.getElementById('islider-next');
  const MOBILE_BP = 768;

  let current   = 0;
  let isDragging = false;
  let startX    = 0;
  let dragDelta = 0;

  /* ── Build progress dots ── */
  const dotsWrap = document.createElement('div');
  dotsWrap.className = 'islider-dots';
  slides.forEach((_, i) => {
    const dot = document.createElement('button');
    dot.className = 'islider-dot' + (i === 0 ? ' active' : '');
    dot.setAttribute('aria-label', 'Go to slide ' + (i + 1));
    dot.addEventListener('click', () => goTo(i));
    dotsWrap.appendChild(dot);
  });
  const navRow = document.querySelector('.islider-nav');
  if (navRow) navRow.appendChild(dotsWrap);

  /* ── Helpers ── */
  function isMobile() { return window.innerWidth <= MOBILE_BP; }

  function getGap() {
    return isMobile() ? 0 : (parseInt(getComputedStyle(track).gap) || 16);
  }

  /* Desktop: pixel offset so active slide is centred in viewport */
  function desktopOffset(idx) {
    const gap  = getGap();
    let total  = 0;
    slides.forEach((s, i) => {
      if (i < idx) total += s.getBoundingClientRect().width + gap;
    });
    const wrapW   = trackWrap.getBoundingClientRect().width;
    const activeW = slides[idx] ? slides[idx].getBoundingClientRect().width : 0;
    const centre  = (wrapW - activeW) / 2;
    return Math.max(0, total - centre);
  }

  /* Mobile: each slide is 100vw wide (set in CSS via width:100% + padding)
     We step by the actual rendered width of one slide. */
  function mobileOffset(idx) {
    const slideW = slides[0] ? slides[0].getBoundingClientRect().width : trackWrap.getBoundingClientRect().width;
    return idx * slideW;
  }

  /* ── Core: go to slide idx ── */
  function goTo(idx) {
    if (idx < 0 || idx >= slides.length) return;
    current = idx;

    /* Update active class */
    slides.forEach((s, i) => s.classList.toggle('islide-active', i === idx));

    /* Update dots */
    dotsWrap.querySelectorAll('.islider-dot')
      .forEach((d, i) => d.classList.toggle('active', i === idx));

    /* Disable nav at edges */
    if (btnPrev) btnPrev.disabled = idx === 0;
    if (btnNext) btnNext.disabled = idx === slides.length - 1;

    if (isMobile()) {
      /* Mobile: instant pixel step — no CSS width transition needed */
      track.style.transform = `translateX(-${mobileOffset(idx)}px)`;
    } else {
      /* Desktop: wait one frame for CSS width transition to start,
         then recalc pixel offset after transition settles (450ms) */
      requestAnimationFrame(() => {
        setTimeout(() => {
          track.style.transform = `translateX(-${desktopOffset(idx)}px)`;
        }, 460);
      });
    }
  }

  /* ── Arrow buttons ── */
  if (btnPrev) btnPrev.addEventListener('click', () => goTo(current - 1));
  if (btnNext) btnNext.addEventListener('click', () => goTo(current + 1));

  /* ── Click collapsed slide (desktop) ── */
  slides.forEach((s, i) => {
    s.addEventListener('click', () => { if (i !== current) goTo(i); });
  });

  /* ── Drag / touch swipe ── */
  function dragStart(x) {
    isDragging = true;
    startX     = x;
    dragDelta  = 0;
    trackWrap.classList.add('is-dragging');
  }
  function dragMove(x) {
    if (!isDragging) return;
    dragDelta = x - startX;
  }
  function dragEnd() {
    if (!isDragging) return;
    isDragging = false;
    trackWrap.classList.remove('is-dragging');
    const threshold = isMobile() ? 35 : 55;
    if      (dragDelta < -threshold) goTo(current + 1);
    else if (dragDelta >  threshold) goTo(current - 1);
    else                             goTo(current);     /* snap back */
  }

  trackWrap.addEventListener('mousedown',  e => dragStart(e.clientX));
  window   .addEventListener('mousemove',  e => dragMove(e.clientX));
  window   .addEventListener('mouseup',    ()  => dragEnd());
  trackWrap.addEventListener('touchstart', e => dragStart(e.touches[0].clientX), { passive: true });
  trackWrap.addEventListener('touchmove',  e => dragMove(e.touches[0].clientX),  { passive: true });
  trackWrap.addEventListener('touchend',   ()  => dragEnd());

  /* ── Keyboard ── */
  document.addEventListener('keydown', e => {
    if (e.key === 'ArrowLeft')  goTo(current - 1);
    if (e.key === 'ArrowRight') goTo(current + 1);
  });

  /* ── Resize: recalculate without animation ── */
  let resizeTimer;
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      track.style.transition = 'none';
      if (isMobile()) {
        track.style.transform = `translateX(-${mobileOffset(current)}px)`;
      } else {
        track.style.transform = `translateX(-${desktopOffset(current)}px)`;
      }
      requestAnimationFrame(() => { track.style.transition = ''; });
    }, 100);
  }, { passive: true });

  /* ── Init ── */
  slides[0] && slides[0].classList.add('islide-active');
  if (btnPrev) btnPrev.disabled = true;
  goTo(0);
})();


/* ── Typing animation for hero detect bar ── */
(function initTypingBar() {
  const el = document.getElementById('typing-text');
  if (!el) return;
  const messages = [
    'Intrusion · Zone B',
    'Loitering · CAM-02',
    'Crowd density alert',
    'Perimeter breach · North',
    'Unknown vehicle · Lot A',
    'Object left · Gate 3',
  ];
  let idx = 0;
  function typeNext() {
    const msg = messages[idx % messages.length];
    idx++;
    el.textContent = '';
    let i = 0;
    const interval = setInterval(() => {
      el.textContent += msg[i];
      i++;
      if (i >= msg.length) {
        clearInterval(interval);
        setTimeout(typeNext, 2800);
      }
    }, 55);
  }
  setTimeout(typeNext, 1500);
})();

/* ── FAQ accordion ── */
window.toggleFaq = function(btn) {
  const item = btn.parentElement;
  const ans  = item.querySelector('.faq-a');
  const isOpen = btn.classList.contains('open');
  // Close all
  document.querySelectorAll('.faq-q.open').forEach(q => {
    q.classList.remove('open');
    q.parentElement.querySelector('.faq-a').classList.remove('open');
  });
  if (!isOpen) {
    btn.classList.add('open');
    ans.classList.add('open');
  }
};

/* ── Stat bar + count-up animation ── */
(function initStats() {
  const statBoxes = document.querySelectorAll('.stat-box');
  if (!statBoxes.length) return;
  const io = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const box = entry.target;
      box.classList.add('bar-animated');
      const numEl = box.querySelector('.stat-num');
      if (!numEl) return;
      const target = parseFloat(numEl.dataset.count);
      if (isNaN(target)) return;
      const prefix = numEl.dataset.prefix || '';
      const suffix = numEl.dataset.suffix || '';
      const duration = 1400;
      const start = performance.now();
      const isDecimal = String(target).includes('.');
      function step(now) {
        const t = Math.min((now - start) / duration, 1);
        const ease = 1 - Math.pow(1 - t, 3);
        const val = target * ease;
        numEl.textContent = prefix + (isDecimal ? val.toFixed(1) : Math.floor(val)) + suffix;
        if (t < 1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
      io.unobserve(box);
    });
  }, { threshold: 0.3 });
  statBoxes.forEach(b => io.observe(b));
})();

/* ── Hero particle canvas ── */
(function initParticles() {
  const hero = document.querySelector('.hero');
  if (!hero) return;
  const canvas = document.createElement('canvas');
  canvas.id = 'hero-particles';
  hero.insertBefore(canvas, hero.firstChild);
  const ctx = canvas.getContext('2d');
  let W, H, particles = [];

  function resize() {
    W = canvas.width  = hero.offsetWidth;
    H = canvas.height = hero.offsetHeight;
  }
  resize();
  window.addEventListener('resize', resize, { passive: true });

  class Particle {
    constructor() { this.reset(); }
    reset() {
      this.x = Math.random() * W;
      this.y = Math.random() * H;
      this.r = Math.random() * 1.5 + 0.3;
      this.vx = (Math.random() - 0.5) * 0.35;
      this.vy = (Math.random() - 0.5) * 0.35;
      this.alpha = Math.random() * 0.45 + 0.1;
    }
    update() {
      this.x += this.vx; this.y += this.vy;
      if (this.x < 0 || this.x > W || this.y < 0 || this.y > H) this.reset();
    }
    draw() {
      ctx.beginPath();
      ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(66,165,245,${this.alpha})`;
      ctx.fill();
    }
  }

  for (let i = 0; i < 80; i++) particles.push(new Particle());

  function drawConnections() {
    for (let i = 0; i < particles.length; i++) {
      for (let j = i + 1; j < particles.length; j++) {
        const dx = particles[i].x - particles[j].x;
        const dy = particles[i].y - particles[j].y;
        const dist = Math.sqrt(dx * dx + dy * dy);
        if (dist < 90) {
          ctx.beginPath();
          ctx.moveTo(particles[i].x, particles[i].y);
          ctx.lineTo(particles[j].x, particles[j].y);
          ctx.strokeStyle = `rgba(66,165,245,${0.12 * (1 - dist / 90)})`;
          ctx.lineWidth = 0.5;
          ctx.stroke();
        }
      }
    }
  }

  let rafId;
  function animate() {
    ctx.clearRect(0, 0, W, H);
    particles.forEach(p => { p.update(); p.draw(); });
    drawConnections();
    rafId = requestAnimationFrame(animate);
  }
  animate();

  // Pause when tab not visible
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) cancelAnimationFrame(rafId);
    else animate();
  });
})();

/* ── Magnetic button micro-interaction ── */
document.querySelectorAll('.btn-primary, .btn-nav-fill').forEach(btn => {
  btn.addEventListener('mousemove', e => {
    const r = btn.getBoundingClientRect();
    const x = e.clientX - r.left - r.width / 2;
    const y = e.clientY - r.top - r.height / 2;
    btn.style.transform = `translate(${x * 0.12}px, ${y * 0.12}px) translateY(-2px)`;
  });
  btn.addEventListener('mouseleave', () => {
    btn.style.transform = '';
  });
});

/* ── Tilt effect on cards ── */
document.querySelectorAll('.trust-card, .hiw-step, .cust-card, .tilt-card').forEach(card => {
  card.addEventListener('mousemove', e => {
    const r = card.getBoundingClientRect();
    const x = (e.clientX - r.left) / r.width  - 0.5;
    const y = (e.clientY - r.top)  / r.height - 0.5;
    card.style.transform = `perspective(600px) rotateX(${-y * 5}deg) rotateY(${x * 5}deg) translateY(-4px)`;
  });
  card.addEventListener('mouseleave', () => {
    card.style.transform = '';
    card.style.transition = 'transform 0.4s ease';
    setTimeout(() => { card.style.transition = ''; }, 400);
  });
});

/* ── Scroll progress bar (thin top indicator) ── */
(function initScrollBar() {
  const bar = document.createElement('div');
  bar.style.cssText = 'position:fixed;top:0;left:0;height:2px;z-index:9999;background:linear-gradient(90deg,#1565C0,#42A5F5);width:0%;transition:width 0.1s;pointer-events:none;';
  document.body.prepend(bar);
  window.addEventListener('scroll', () => {
    const pct = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
    bar.style.width = pct + '%';
  }, { passive: true });
})();

/* ── Highlight active nav link on scroll ── */
(function initActiveNav() {
  const sections = document.querySelectorAll('section[id], div[id]');
  if (!sections.length) return;
  const navLinks = document.querySelectorAll('.nav-links a');
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        navLinks.forEach(a => {
          a.classList.toggle('active', a.getAttribute('href') === '#' + e.target.id);
        });
      }
    });
  }, { threshold: 0.4 });
  sections.forEach(s => io.observe(s));
})();
