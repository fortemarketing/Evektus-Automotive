/**
 * Theme Posts — settings form helper.
 *
 * Beaver Builder's toggles do not nest: when one select hides another, the
 * hidden select's own toggle targets stay as they were. Two pairs need both
 * selects taken into account, so they are reconciled here.
 *
 * - A carousel pages itself, so the paging fields have nothing to say for it,
 *   while pagination_type's own toggle shows the Load More fields.
 * - Show Arrows hides Arrow Position, but not the gutter, gap and spacing
 *   fields that Arrow Position shows.
 */
(function ($) {

	FLBuilder.registerModuleHelper('theme-posts', {

		init: function () {
			var form = $('.fl-builder-settings:visible');
			var sync = this._sync.bind(this, form);

			// Run after Beaver Builder's own toggle handler for the same change,
			// which would otherwise show the fields again.
			form.find('select[name=layout], select[name=pagination_type], select[name=arrows], select[name=arrow_position]')
				.on('change', function () {
					window.setTimeout(sync, 0);
				});
			sync();
		},

		_sync: function (form) {
			var carousel = 'carousel' === form.find('select[name=layout]').val();
			var loadMore = 'load_more' === form.find('select[name=pagination_type]').val();
			var arrows = 'no' !== form.find('select[name=arrows]').val();
			var position = form.find('select[name=arrow_position]').val();

			// The rest of the section still applies: a carousel with no posts
			// shows the same no-results message.
			form.find('#fl-field-pagination_type').toggle(!carousel);
			form.find('#fl-field-load_more_text, #fl-field-load_more_style').toggle(!carousel && loadMore);

			form.find('#fl-field-arrow_gutter').toggle(arrows && 'outside' === position);
			form.find('#fl-field-arrow_gap, #fl-field-arrow_spacing').toggle(arrows && 'below' === position);
		}
	});

})(jQuery);
