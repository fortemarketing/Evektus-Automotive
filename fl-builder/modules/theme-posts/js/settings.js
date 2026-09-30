/**
 * Theme Posts — settings form helper.
 *
 * A carousel pages itself, so the paging fields have nothing to say for it.
 * Beaver Builder's field toggles cannot hide a field on one value of Layout
 * while pagination_type's own toggle shows the Load More fields on another, so
 * the two are reconciled here.
 */
(function ($) {

	FLBuilder.registerModuleHelper('theme-posts', {

		init: function () {
			var form = $('.fl-builder-settings:visible');
			var sync = this._sync.bind(this, form);

			form.find('select[name=layout], select[name=pagination_type]').on('change', sync);
			sync();
		},

		_sync: function (form) {
			var carousel = 'carousel' === form.find('select[name=layout]').val();
			var loadMore = 'load_more' === form.find('select[name=pagination_type]').val();

			// The rest of the section still applies: a carousel with no posts
			// shows the same no-results message.
			form.find('#fl-field-pagination_type').toggle(!carousel);
			form.find('#fl-field-load_more_text, #fl-field-load_more_style').toggle(!carousel && loadMore);
		}
	});

})(jQuery);
