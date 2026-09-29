/**
 * Theme Button click actions.
 *
 * A copy of the Forte Marketing Modules plugin's shared assets/button/button.js,
 * renamed so it can load beside the plugin's copy. Handles the Lightbox and
 * Copy Text actions; the Button action (custom JavaScript) runs whatever the
 * editor typed into the field, which is per instance by definition, so the
 * module still emits that one inline.
 *
 * Called with a scope selector rather than a node id, so a module can bind to
 * whichever element wraps its button.
 */

(function ($) {
	'use strict';

	if (!$) {
		return;
	}

	window.ThemeButtonActions = {

		/**
		 * @param {Object} config
		 *   scope        {string} Selector for the element wrapping the button.
		 *   action       {string} 'lightbox' or 'copy_text'.
		 *   lightboxType {string} 'video' or 'html'.
		 *   i18n         {Object} Translated strings.
		 */
		init: function (config) {
			var $scope = $(config.scope);

			if (!$scope.length) {
				return;
			}

			if ('lightbox' === config.action) {
				this._lightbox($scope, config);
			} else if ('copy_text' === config.action) {
				this._copyText($scope, config);
			}
		},

		_lightbox: function ($scope, config) {
			if ('function' !== typeof $.fn.magnificPopup) {
				return;
			}

			var options = {
				closeBtnInside: true,
				fixedContentPos: true,
				tLoading: '<i class="fas fa-spinner fa-spin fa-3x fa-fw"></i>'
			};

			if ('video' === config.lightboxType) {
				options.type = 'iframe';
				options.mainClass = 'theme-button-lightbox-wrap';
			} else {
				options.type = 'inline';
				options.items = {
					src: $scope.find('.theme-button-lightbox-content')[0]
				};
				options.callbacks = {
					open: function () {
						var content = $(this.content);
						var divWrap = $(content[0]).find('> div');

						divWrap.css('display', 'block');

						// Triggers select change if we have multiple forms in a page.
						if (divWrap.find('form select').length > 0) {
							divWrap.find('form select').trigger('change');
						}

						// Reload sliders.
						if ('undefined' !== typeof FLBuilderLayout) {
							FLBuilderLayout.reloadSlider(content);
							FLBuilderLayout.resizeSlideshow();
						}
					}
				};
			}

			$scope.find('.theme-button-lightbox').magnificPopup(options);
		},

		_copyText: function ($scope, config) {
			var i18n = config.i18n || {};
			var failed = i18n.copyFailed || 'Failed to copy';

			$scope.find('.theme-button').on('click', function (e) {
				e.preventDefault();

				var $btn = $(this);
				var textToCopy = $btn.data('copy-text');
				var successMessage = $btn.data('copy-success-message');

				// Both modules wrap the label in .theme-button-text, so look there
				// first; the bare text-node lookup is the fallback for a button
				// rendered without that span.
				var $label = $btn.find('.theme-button-text').first();
				var labelNode = $label.length
					? $label.get(0).firstChild
					: $btn.contents().filter(function () {
						return 3 === this.nodeType && '' !== this.nodeValue.trim();
					}).get(0);

				if (!textToCopy || !successMessage || !labelNode || 3 !== labelNode.nodeType) {
					return;
				}

				var originalText = labelNode.nodeValue;
				var originalLabel = $btn.attr('aria-label');

				$btn.prop('disabled', true);
				$btn.addClass('disabled');

				function setLabel(label) {
					labelNode.nodeValue = label;
					$btn.attr('aria-label', label);
				}

				function showSuccess() {
					setLabel(successMessage);
				}

				function showError() {
					setLabel(failed);
				}

				function showFinish() {
					setTimeout(function () {
						$btn.prop('disabled', false);
						$btn.removeClass('disabled');
						labelNode.nodeValue = originalText;
						$btn.attr('aria-label', originalLabel);
						$btn.focus();
					}, 1500);
				}

				if (!navigator.clipboard) {
					showError();
					showFinish();
					return;
				}

				navigator.clipboard.writeText(textToCopy).then(showSuccess).catch(showError).finally(showFinish);
			});
		}
	};
})(window.jQuery);
