(function($){

	FLBuilder.registerModuleHelper('theme-button', {

		init: function() {
			var form = $( '.fl-builder-settings:visible' ),
				customWidth = form.find( 'input[name=custom_width]' ),
				icon = form.find( 'input[name=icon]' ),
				iconPosition = form.find( 'select[name=icon_position]' );

			customWidth.on( 'input', this._previewCustomWidth );
			icon.on( 'change', this._previewIcon );
			iconPosition.on( 'change', this._previewIcon );
			icon.on( 'change', this._flipSettings );
			this._flipSettings()
		},

		_flipSettings: function() {
			var form  = $( '.fl-builder-settings' ),
					icon = form.find( 'input[name=icon]' );
			if ( -1 !== icon.val().indexOf( 'fad fa') ) {
				$('#fl-field-duo_color1').show();
				$('#fl-field-duo_color2').show();
				$('#fl-builder-settings-section-icons').show()
			} else {
				$('#fl-field-duo_color1').hide();
				$('#fl-field-duo_color2').hide();
				$('#fl-builder-settings-section-icons').hide()
			}
		},

		_previewCustomWidth: function() {
			var preview	        = FLBuilder.preview,
				selector        = preview.classes.node + ' .theme-button:is(a, button)',
				form            = $( '.fl-builder-settings:visible' ),
				width           = form.find( 'select[name=width]' ).val(),
				customWidth     = form.find( 'input[name=custom_width]' ).val(),
				customWidthUnit = form.find( 'select[name=custom_width_unit]' ).val();

			if ( 'custom' === width && '' === customWidth.trim() ) {
				preview.updateCSSRule( selector, 'width', '200' + customWidthUnit );
			}
		},


		_previewIcon: function() {
			var node = FLBuilder.preview.elements.node,
				wrap = node.find( '.theme-button-wrap' ).addBack( '.theme-button-wrap' ),
				link = node.find( '.theme-button:is(a, button)' ),
				form = $( '.fl-builder-settings:visible' ),
				icon = form.find( 'input[name=icon]' ).val(),
				position = form.find( 'select[name=icon_position]' ).val();

			node.find( '.theme-button-icon' ).remove();
			wrap.removeClass( 'theme-button-has-icon' );

			if ( '' !== icon ) {
				wrap.addClass( 'theme-button-has-icon' );

				if ( 'before' === position ) {
					link.prepend( '<span class="theme-button-icon theme-button-icon-before ' + icon + '" aria-hidden="true"></span>' );
				} else if ( 'after' === position ) {
					link.append( '<span class="theme-button-icon theme-button-icon-after ' + icon + '" aria-hidden="true"></span>' );
				}
			}
		},
	});

})(jQuery);
