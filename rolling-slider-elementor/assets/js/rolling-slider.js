/**
 * Rolling Slider for Elementor
 * هر نمونه از ویجت مستقل است (بدون ID ثابت) و با هر رندر دوبارهٔ المنتور از نو ساخته می‌شود.
 */
(function () {
	'use strict';

	var HOOK = 'frontend/element_ready/rolling_slider.default';

	function init(root) {
		if (!root || root.dataset.rslReady === '1') return;

		var slides = [].slice.call(root.querySelectorAll('.rsl__slide'));
		var panels = [].slice.call(root.querySelectorAll('.rsl__panel'));
		var tabs = [].slice.call(root.querySelectorAll('.rsl__tab'));
		var total = slides.length;
		if (!total) return;

		root.dataset.rslReady = '1';

		var rtl = root.getAttribute('dir') === 'rtl';
		var duration = parseInt(root.dataset.duration, 10) || 7000;
		var editing = document.body.classList.contains('elementor-editor-active');
		var reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
		var wide = window.matchMedia('(min-width: 768px)');
		var conn = navigator.connection || {};
		var lightMode = conn.saveData === true || /2g/.test(conn.effectiveType || '');

		var autoplay = root.dataset.autoplay === 'yes' && total > 1 && !editing && !reduced.matches;
		var pauseOnHover = root.dataset.pauseHover === 'yes';
		var swipeOn = root.dataset.swipe === 'yes' && total > 1;

		var current = 0;
		var started = false;
		var visible = true;
		var timer = null;
		var remaining = duration;
		var startedAt = 0;
		var reasons = {};
		var resumeTouch = null;
		var destroyed = false;

		slides.forEach(function (slide, i) {
			if (slide.classList.contains('is-active')) current = i;
		});

		/* ── رسانه ────────────────────────────────────────────────────────
		   تصاویر و ویدیوهای اسلایدهای غیرفعال تا لحظهٔ نیاز دانلود نمی‌شوند. */
		function hydrateImage(i) {
			var img = slides[i] && slides[i].querySelector('img[data-rsl-src]');
			if (!img) return;
			var srcset = img.getAttribute('data-rsl-srcset');
			if (srcset) img.srcset = srcset;
			img.src = img.getAttribute('data-rsl-src');
			img.removeAttribute('data-rsl-src');
			img.removeAttribute('data-rsl-srcset');
		}

		function hydrateVideo(i) {
			if (reduced.matches || lightMode) return;
			var video = slides[i] && slides[i].querySelector('video[data-rsl-src]');
			if (!video || video.dataset.rslLoaded) return;
			var src = (!wide.matches && video.dataset.rslSrcMobile) ? video.dataset.rslSrcMobile : video.dataset.rslSrc;
			var source = document.createElement('source');
			source.src = src;
			source.type = video.dataset.rslType || 'video/mp4';
			video.muted = true;
			video.appendChild(source);
			video.dataset.rslLoaded = '1';
			// play() هم‌زمان با load() اثر ندارد؛ بعد از آماده شدن دوباره هماهنگ می‌کنیم
			video.addEventListener('canplay', syncVideos);
			video.load();
		}

		function hydrateRest() {
			// پیش‌لود بقیه فقط روی دسکتاپ و اینترنت عادی
			if (lightMode || !wide.matches) return;
			var idle = window.requestIdleCallback || function (fn) { return setTimeout(fn, 1200); };
			idle(function () {
				if (destroyed) return;
				slides.forEach(function (_, i) { hydrateImage(i); hydrateVideo(i); });
			});
		}

		// ویدیوی اسلاید فعال همیشه لوپ می‌شود؛ مکث هاور فقط چرخش اسلاید را نگه می‌دارد
		function syncVideos() {
			slides.forEach(function (slide, i) {
				var video = slide.querySelector('video');
				if (!video || !video.dataset.rslLoaded) return;
				if (i === current && visible && !document.hidden) {
					var p = video.play();
					if (p && p.catch) p.catch(function () { /* اتوپلی بسته است؛ پوستر می‌ماند */ });
				} else {
					video.pause();
				}
			});
		}

		/* ── تایمر با «زمان باقی‌مانده»، تا با نوار پیشرفت CSS هم‌گام بماند ── */
		function isPaused() {
			for (var k in reasons) if (reasons[k]) return true;
			return false;
		}

		function schedule(ms) {
			clearTimeout(timer);
			if (!autoplay) return;
			remaining = ms;
			startedAt = Date.now();
			timer = setTimeout(tick, ms);
		}

		function restartTimer() {
			clearTimeout(timer);
			remaining = duration;
			if (autoplay && !isPaused()) schedule(duration);
		}

		function tick() {
			if (!root.isConnected) { destroy(); return; }   // المنتور ویجت را دوباره رندر کرده
			go(current + 1);
		}

		function setPause(reason, on) {
			if (!!reasons[reason] === on) return;
			var was = isPaused();
			reasons[reason] = on;
			var now = isPaused();
			if (was === now) return;
			root.classList.toggle('is-paused', now);
			if (!autoplay) return;
			if (now) {
				clearTimeout(timer);
				remaining = Math.max(0, remaining - (Date.now() - startedAt));
			} else {
				schedule(remaining);
			}
		}

		/* ── ناوبری ───────────────────────────────────────────────────────── */
		function centerTab(el) {
			var strip = el && el.parentNode;
			if (!strip || strip.scrollWidth <= strip.clientWidth + 1) return;
			var r = el.getBoundingClientRect();
			var s = strip.getBoundingClientRect();
			strip.scrollBy({ left: (r.left + r.width / 2) - (s.left + s.width / 2), behavior: 'smooth' });
		}

		function go(i) {
			i = (i + total) % total;
			if (started && i === current) return;
			current = i;

			slides.forEach(function (slide, n) { slide.classList.toggle('is-active', n === i); });
			panels.forEach(function (panel, n) { panel.classList.toggle('is-active', n === i); });
			tabs.forEach(function (tab, n) {
				var on = n === i;
				tab.setAttribute('aria-selected', on ? 'true' : 'false');
				tab.tabIndex = on ? 0 : -1;
			});

			if (started) centerTab(tabs[i]);

			hydrateImage(i);
			if (!lightMode) hydrateImage((i + 1) % total);
			hydrateVideo(i);
			syncVideos();
			restartTimer();
			started = true;
		}

		tabs.forEach(function (tab, i) {
			tab.addEventListener('click', function () { go(i); });
			tab.addEventListener('keydown', function (e) {
				// جهت از دایرکشن واقعی ویجت می‌آید: در راست‌چین چپ = بعدی
				var forward = rtl ? 'ArrowLeft' : 'ArrowRight';
				var backward = rtl ? 'ArrowRight' : 'ArrowLeft';
				var next;
				if (e.key === forward) next = i + 1;
				else if (e.key === backward) next = i - 1;
				else if (e.key === 'Home') next = 0;
				else if (e.key === 'End') next = tabs.length - 1;
				else return;
				e.preventDefault();
				next = (next + tabs.length) % tabs.length;
				tabs[next].focus();
				go(next);
			});
		});

		/* ── مکث ─────────────────────────────────────────────────────────── */
		if (pauseOnHover) {
			root.addEventListener('mouseenter', function () { setPause('hover', true); });
			root.addEventListener('mouseleave', function () { setPause('hover', false); });
		}

		// فقط فوکوس کیبورد؛ کلیک روی تب نباید اسلایدر را تا ابد متوقف کند
		root.addEventListener('focusin', function (e) {
			if (e.target.matches && e.target.matches(':focus-visible')) setPause('focus', true);
		});
		root.addEventListener('focusout', function (e) {
			if (!root.contains(e.relatedTarget)) setPause('focus', false);
		});

		function onVisibility() {
			setPause('hidden', document.hidden);
			syncVideos();
		}
		document.addEventListener('visibilitychange', onVisibility);

		// وقتی اسلایدر از دید خارج است نه تایمر می‌چرخد و نه ویدیو پخش می‌شود
		var io = null;
		if ('IntersectionObserver' in window) {
			io = new IntersectionObserver(function (entries) {
				visible = entries[entries.length - 1].isIntersecting;
				setPause('offscreen', !visible);
				syncVideos();
			}, { threshold: 0.05 });
			io.observe(root);
		}

		/* ── سوایپ (کشیدن به چپ = اسلاید بعدی) ───────────────────────────── */
		var startX = null, startY = null;
		root.addEventListener('touchstart', function (e) {
			setPause('touch', true);
			clearTimeout(resumeTouch);
			resumeTouch = setTimeout(function () { setPause('touch', false); }, 4000);
			if (!swipeOn) return;
			startX = e.touches[0].clientX;
			startY = e.touches[0].clientY;
		}, { passive: true });

		root.addEventListener('touchend', function (e) {
			if (!swipeOn || startX === null) return;
			var dx = e.changedTouches[0].clientX - startX;
			var dy = e.changedTouches[0].clientY - startY;
			startX = startY = null;
			if (Math.abs(dx) < 48 || Math.abs(dx) < Math.abs(dy)) return;
			go(dx < 0 ? current + 1 : current - 1);
		}, { passive: true });

		function onWide() { hydrateVideo(current); syncVideos(); }
		if (wide.addEventListener) wide.addEventListener('change', onWide);

		function destroy() {
			if (destroyed) return;
			destroyed = true;
			clearTimeout(timer);
			clearTimeout(resumeTouch);
			document.removeEventListener('visibilitychange', onVisibility);
			if (wide.removeEventListener) wide.removeEventListener('change', onWide);
			if (io) io.disconnect();
		}

		go(current);

		if (document.readyState === 'complete') hydrateRest();
		else window.addEventListener('load', hydrateRest, { once: true });
	}

	function boot() {
		[].forEach.call(document.querySelectorAll('.rsl'), init);
	}

	var hooked = false;
	function register() {
		if (hooked || !window.elementorFrontend || !window.elementorFrontend.hooks) return;
		hooked = true;
		window.elementorFrontend.hooks.addAction(HOOK, function ($scope) {
			var el = $scope && $scope[0];
			init(el && el.querySelector('.rsl'));
		});
	}

	// المنتور: هم فرانت‌اند و هم ادیتور (هر بار که ویجت دوباره رندر می‌شود)
	if (window.jQuery) window.jQuery(window).on('elementor/frontend/init', register);
	register();

	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
	else boot();
})();
