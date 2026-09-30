/**
 * Theme Posts — front-end behaviour.
 *
 * One instance per module on the page, constructed by includes/frontend.js.php
 * with that instance's settings. Handles the Swiper carousel, the jQuery
 * Masonry wall, the taxonomy filter bar and the Load More button. A grid or
 * list with no filter and no Load More needs nothing from this file.
 */
(function () {

	function ThemePosts(config) {
		this.config = config || {};
		this.node = document.querySelector('.fl-node-' + this.config.id);

		if (!this.node) {
			return;
		}

		this.root = this.node.querySelector('.theme-posts');
		this.items = this.node.querySelector('.theme-posts__items');

		if (!this.root || !this.items) {
			return;
		}

		this.activeFilter = 'all';

		if ('carousel' === this.config.layout) {
			this.initCarousel();
		} else if ('masonry' === this.config.layout) {
			this.initMasonry();
		}

		this.initFilters();

		if (this.config.loadMore) {
			this.initLoadMore();
		}
	}

	/**
	 * Boots the Swiper carousel.
	 *
	 * Loop mode duplicates slides, so it is switched off when there are fewer
	 * cards than the carousel shows at once -- Swiper cannot make a coherent
	 * loop out of a partial set and leaves blank slides behind if asked to.
	 */
	ThemePosts.prototype.initCarousel = function () {
		var el = this.node.querySelector('.theme-posts__swiper');
		var opts = this.config.carousel || {};

		if (!el || 'undefined' === typeof window.Swiper) {
			return;
		}

		var slides = el.querySelectorAll('.theme-posts__item').length;
		var perView = (opts.base && opts.base.slidesPerView) || 1;

		Object.keys(opts.breakpoints || {}).forEach(function (key) {
			perView = Math.max(perView, opts.breakpoints[key].slidesPerView || 1);
		});

		var args = {
			speed: opts.speed || 400,
			loop: !!opts.loop && slides > perView,
			centeredSlides: !!opts.centeredSlides,
			grabCursor: !!opts.grabCursor,
			slidesPerView: (opts.base && opts.base.slidesPerView) || 1,
			spaceBetween: (opts.base && opts.base.spaceBetween) || 0,
			breakpoints: opts.breakpoints || {},
			watchOverflow: true
		};

		if (opts.freeMode) {
			args.freeMode = true;
		}

		if (opts.keyboard) {
			args.keyboard = { enabled: true };
		}

		if (opts.mousewheel) {
			args.mousewheel = { forceToAxis: true };
		}

		if (opts.autoplay) {
			args.autoplay = {
				delay: opts.autoplayDelay || 3000,
				disableOnInteraction: !!opts.disableOnInteraction,
				pauseOnMouseEnter: !!opts.pauseOnHover
			};
		}

		if (opts.arrows) {
			args.navigation = {
				prevEl: this.node.querySelector('.theme-posts__arrow--prev'),
				nextEl: this.node.querySelector('.theme-posts__arrow--next')
			};
		}

		if ('none' !== opts.pagination) {
			args.pagination = {
				el: this.node.querySelector('.swiper-pagination'),
				type: opts.pagination,
				clickable: true,
				dynamicBullets: !!opts.dynamicBullets
			};
		}

		if (opts.scrollbar) {
			args.scrollbar = {
				el: el.querySelector('.swiper-scrollbar'),
				hide: false
			};
		}

		this.swiper = new window.Swiper(el, args);
	};

	/**
	 * Boots the jQuery Masonry wall.
	 *
	 * The gutter comes from the same custom property the CSS sizes the cards
	 * with, read back off the element, so the responsive column gap and the
	 * gutter can never drift apart. Layout is deferred until the images have
	 * loaded, since an unloaded image measures as zero height.
	 */
	ThemePosts.prototype.initMasonry = function () {
		var $ = window.jQuery;

		if (!$ || !$.fn.masonry) {
			return;
		}

		var $items = $(this.items);
		var self = this;

		var gutter = function () {
			var value = window.getComputedStyle(self.items).getPropertyValue('--theme-posts-col-gap');
			return parseInt(value, 10) || 0;
		};

		var start = function () {
			$items.masonry({
				itemSelector: '.theme-posts__item',
				columnWidth: '.theme-posts__sizer',
				gutter: gutter(),
				percentPosition: true,
				transitionDuration: 0
			});
		};

		if ($.fn.imagesLoaded) {
			$items.imagesLoaded(start);
		} else {
			start();
		}

		this.masonry = $items;

		// The gutter is a fixed number once Masonry has it, so a resize that
		// crosses a breakpoint has to hand it the new one.
		window.addEventListener('resize', this.debounce(function () {
			$items.masonry('option', { gutter: gutter() });
			$items.masonry('layout');
		}, 150));
	};

	/**
	 * Relays out the masonry wall, after a filter or a Load More append.
	 */
	ThemePosts.prototype.relayout = function () {
		if (!this.masonry) {
			return;
		}

		this.masonry.masonry('reloadItems');
		this.masonry.masonry('layout');
	};

	/**
	 * Wires up the taxonomy filter bar.
	 */
	ThemePosts.prototype.initFilters = function () {
		var bar = this.node.querySelector('.theme-posts__filters');
		var self = this;

		if (!bar) {
			return;
		}

		bar.addEventListener('click', function (event) {
			var button = event.target.closest('.theme-posts__filter');

			if (!button) {
				return;
			}

			self.activeFilter = button.getAttribute('data-filter') || 'all';

			bar.querySelectorAll('.theme-posts__filter').forEach(function (other) {
				var active = other === button;
				other.classList.toggle('is-active', active);
				other.setAttribute('aria-pressed', active ? 'true' : 'false');
			});

			self.applyFilter();
		});
	};

	/**
	 * Shows only the cards carrying the active term.
	 */
	ThemePosts.prototype.applyFilter = function () {
		var filter = this.activeFilter;

		this.items.querySelectorAll('.theme-posts__item').forEach(function (item) {
			var terms = (item.getAttribute('data-terms') || '').split(' ');
			var show = 'all' === filter || -1 !== terms.indexOf(filter);

			if (show) {
				item.removeAttribute('hidden');
			} else {
				item.setAttribute('hidden', '');
			}
		});

		this.relayout();
	};

	/**
	 * Wires up the Load More button.
	 *
	 * The button is a real link to the next page, so it keeps working without
	 * JS; this only intercepts the click to fetch that page and lift this
	 * node's cards out of it instead of navigating.
	 */
	ThemePosts.prototype.initLoadMore = function () {
		var self = this;
		var button = this.node.querySelector('.theme-posts__load-more');

		if (!button) {
			return;
		}

		button.addEventListener('click', function (event) {
			event.preventDefault();

			var next = button.getAttribute('data-next');

			if (!next || button.classList.contains('is-loading')) {
				return;
			}

			button.classList.add('is-loading');

			fetch(next, { credentials: 'same-origin' })
				.then(function (response) {
					if (!response.ok) {
						throw new Error('Theme Posts: could not load page ' + next);
					}
					return response.text();
				})
				.then(function (html) {
					self.appendPage(html, button);
				})
				.catch(function () {
					// Fall back to a normal navigation, which is what the
					// button does without JS anyway.
					window.location.href = next;
				});
		});
	};

	/**
	 * Appends the cards from a fetched page and moves the button on.
	 *
	 * @param {string} html   The fetched page's markup.
	 * @param {Element} button The Load More button.
	 */
	ThemePosts.prototype.appendPage = function (html, button) {
		var doc = new DOMParser().parseFromString(html, 'text/html');
		var scope = doc.querySelector('.fl-node-' + this.config.id);
		var added = [];

		button.classList.remove('is-loading');

		if (!scope) {
			button.remove();
			return;
		}

		var incoming = scope.querySelectorAll('.theme-posts__items .theme-posts__item');

		for (var i = 0; i < incoming.length; i++) {
			var item = document.importNode(incoming[i], true);
			this.items.appendChild(item);
			added.push(item);
		}

		// The fetched page carries its own button, whose data-next points one
		// page further on. No button there means that was the last page.
		var nextButton = scope.querySelector('.theme-posts__load-more');

		if (nextButton && added.length) {
			button.setAttribute('data-next', nextButton.getAttribute('data-next'));
			button.setAttribute('href', nextButton.getAttribute('href'));
		} else {
			button.remove();
		}

		this.applyFilter();

		if (this.masonry && window.jQuery && window.jQuery.fn.imagesLoaded) {
			window.jQuery(this.items).imagesLoaded(this.relayout.bind(this));
		} else {
			this.relayout();
		}
	};

	/**
	 * Returns fn, delayed so a burst of calls runs it once.
	 *
	 * @param {Function} fn   The function to defer.
	 * @param {number}   wait Milliseconds of quiet before it runs.
	 * @return {Function}
	 */
	ThemePosts.prototype.debounce = function (fn, wait) {
		var timer = null;

		return function () {
			var args = arguments;
			var self = this;

			window.clearTimeout(timer);
			timer = window.setTimeout(function () {
				fn.apply(self, args);
			}, wait);
		};
	};

	window.ThemePosts = ThemePosts;

})();
