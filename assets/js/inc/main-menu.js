/**
 * Main menu - mega menu positioning and mobile off-canvas drill-down.
 *
 * Ported from the Bricks element in the Getaway Trekking theme.
 *
 * - Sizes the hover bridge between the top level link and the mega panel.
 *   The panel itself is laid out by CSS - full width, pinned below the
 *   header - so it is already in place before this script has run.
 * - Only when a menu names its own target nodes (data-mega-menu for the
 *   panel's width and left edge, data-mega-menu-vertical for its top edge)
 *   does this measure them and position the panel inline.
 * - Handles the mobile off-canvas panel with drill-down navigation,
 *   backdrop, close button and body scroll lock.
 *
 * Desktop mega menu open/close is CSS-only (:hover / :focus-within).
 *
 * Exposed as window.evekMainMenu so it can be re-run after markup is
 * replaced dynamically (for example inside a page builder preview).
 */
(function () {
	var DESKTOP = window.matchMedia('(min-width: 992px)');

	function getHeader(menu) {
		return menu.closest('.site-header') || menu.closest('header');
	}

	// Horizontal target node: data-mega-menu selector, default document.body
	function getHorizontalNode(menu) {
		var selector = menu.getAttribute('data-mega-menu');

		if (selector) {
			var node = document.querySelector(selector);
			if (node) {
				return node;
			}
		}

		return document.body;
	}

	// Vertical target node: data-mega-menu-vertical selector, default header
	function getVerticalNode(menu) {
		var selector = menu.getAttribute('data-mega-menu-vertical');

		if (selector) {
			var node = document.querySelector(selector);
			if (node) {
				return node;
			}
		}

		return getHeader(menu);
	}

	function positionMega(menu) {
		var megas = menu.querySelectorAll('.main-menu__mega');
		var custom = menu.getAttribute('data-mega-menu') || menu.getAttribute('data-mega-menu-vertical');

		// Mobile, or no custom targets: clear inline styles so the CSS layout
		// (drill-down on mobile, full width below the header on desktop) holds
		if (!DESKTOP.matches || !custom) {
			megas.forEach(function (mega) {
				mega.style.left = '';
				mega.style.right = '';
				mega.style.top = '';
				mega.style.minWidth = '';
			});
		}

		if (!DESKTOP.matches) {
			return;
		}

		var menuRect = menu.getBoundingClientRect();
		var verticalNode = getVerticalNode(menu);
		var verticalRect = verticalNode ? verticalNode.getBoundingClientRect() : null;

		// Menu bottom -> vertical target bottom (sizes the hover bridge)
		var gap = verticalRect ? Math.max(0, Math.round(verticalRect.bottom - menuRect.bottom)) : 0;
		menu.style.setProperty('--mm-pin-gap', gap + 'px');

		if (!custom) {
			return;
		}

		var refRect = getHorizontalNode(menu).getBoundingClientRect();

		// Measured against the panel's containing block (the header on
		// desktop), since that is what its inline left/top are relative to
		megas.forEach(function (mega) {
			var parentRect = (mega.offsetParent || document.body).getBoundingClientRect();

			mega.style.left = refRect.left - parentRect.left + 'px';
			mega.style.right = 'auto';
			mega.style.minWidth = refRect.width + 'px';

			if (verticalRect) {
				mega.style.top = verticalRect.bottom - parentRect.top + 'px';
			}
		});
	}

	function mainMenu() {
		document.querySelectorAll('.main-menu:not([data-main-menu])').forEach(function (menu) {
			menu.setAttribute('data-main-menu', 'true');

			var toggle = menu.querySelector('.main-menu__toggle');

			positionMega(menu);

			// Re-measure right before opening so the position is always fresh
			menu.querySelectorAll('.main-menu__item--has-mega').forEach(function (item) {
				item.addEventListener('mouseenter', function () {
					if (DESKTOP.matches) {
						positionMega(menu);
					}
				});

				item.addEventListener('focusin', function () {
					if (DESKTOP.matches) {
						positionMega(menu);
					}
				});
			});

			// Hamburger: open/close the mobile off-canvas panel
			if (toggle) {
				toggle.addEventListener('click', function () {
					if (menu.classList.contains('is-open')) {
						closeMobileMenu(menu);
					} else {
						menu.classList.add('is-open');
						toggle.setAttribute('aria-expanded', 'true');
						document.body.classList.add('mm-no-scroll');

						var close = menu.querySelector('.main-menu__close');
						if (close) {
							close.focus();
						}
					}
				});
			}

			// Close button inside the off-canvas panel
			menu.querySelectorAll('.main-menu__close').forEach(function (btn) {
				btn.addEventListener('click', function () {
					closeMobileMenu(menu);

					if (toggle) {
						toggle.focus();
					}
				});
			});

			// Backdrop click closes the panel
			menu.querySelectorAll('.main-menu__backdrop').forEach(function (backdrop) {
				backdrop.addEventListener('click', function () {
					closeMobileMenu(menu);
				});
			});

			// Drill down into a submenu. A drill target is either a top level
			// item (its mega panel) or a group inside a column (its links).
			menu.querySelectorAll('.main-menu__drill-toggle').forEach(function (btn) {
				btn.addEventListener('click', function () {
					var target = btn.closest('.main-menu__group') || btn.closest('.main-menu__item');

					if (target) {
						target.classList.add('is-drilled');
						btn.setAttribute('aria-expanded', 'true');

						// Column drills slide within the scrollable level-1 panel:
						// reset its scroll so the new panel starts at the top
						var scroller = target.closest('.main-menu__mega');
						if (scroller) {
							scroller.scrollTop = 0;
						}

						// Move focus to the back button for keyboard users
						var back = target.querySelector('.main-menu__back');
						if (back) {
							back.focus();
						}
					}
				});
			});

			// Column headings without a URL drill on tap too (mobile only)
			menu.querySelectorAll('span.main-menu__mega-heading').forEach(function (heading) {
				heading.addEventListener('click', function () {
					if (DESKTOP.matches) {
						return;
					}

					var group = heading.closest('.main-menu__group');
					var drillToggle = group ? group.querySelector('.main-menu__drill-toggle') : null;

					if (drillToggle) {
						drillToggle.click();
					}
				});
			});

			// Drill back up one level
			menu.querySelectorAll('.main-menu__back').forEach(function (btn) {
				btn.addEventListener('click', function () {
					var target = btn.closest('.main-menu__group') || btn.closest('.main-menu__item');

					if (target) {
						target.classList.remove('is-drilled');

						var drillToggle = target.querySelector('.main-menu__drill-toggle');
						if (drillToggle) {
							drillToggle.setAttribute('aria-expanded', 'false');
							drillToggle.focus();
						}
					}
				});
			});

			// Close on Escape
			menu.addEventListener('keydown', function (e) {
				if (e.key === 'Escape' && menu.classList.contains('is-open')) {
					closeMobileMenu(menu);

					if (toggle) {
						toggle.focus();
					}
				}
			});
		});
	}

	function closeMobileMenu(menu) {
		menu.classList.remove('is-open');
		document.body.classList.remove('mm-no-scroll');

		var toggle = menu.querySelector('.main-menu__toggle');
		if (toggle) {
			toggle.setAttribute('aria-expanded', 'false');
		}

		closeDrilled(menu);
	}

	function closeDrilled(menu) {
		menu.querySelectorAll('.main-menu__item.is-drilled, .main-menu__group.is-drilled').forEach(function (target) {
			target.classList.remove('is-drilled');

			var drillToggle = target.querySelector('.main-menu__drill-toggle');
			if (drillToggle) {
				drillToggle.setAttribute('aria-expanded', 'false');
			}
		});
	}

	function refreshAll() {
		document.querySelectorAll('.main-menu[data-main-menu]').forEach(function (menu) {
			// Leaving the mobile breakpoint: close the off-canvas panel so the
			// body scroll lock doesn't stick around
			if (DESKTOP.matches && menu.classList.contains('is-open')) {
				closeMobileMenu(menu);
			}

			positionMega(menu);
		});
	}

	window.evekMainMenu = mainMenu;

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', mainMenu);
	} else {
		mainMenu();
	}

	// Re-measure once everything (fonts, images) has settled, and on resize
	window.addEventListener('load', refreshAll);
	window.addEventListener('resize', refreshAll);

	// A Customizer partial refresh swaps the whole <nav> for fresh markup with
	// none of this bound to it (see the main menu partial in inc/customizer.php).
	// Bound on load because wp.customize is only present in the preview, and
	// only lands there once its own scripts have run.
	window.addEventListener('load', function () {
		if (window.wp && wp.customize && wp.customize.selectiveRefresh) {
			wp.customize.selectiveRefresh.bind('partial-content-rendered', function () {
				mainMenu();
			});
		}
	});
})();
