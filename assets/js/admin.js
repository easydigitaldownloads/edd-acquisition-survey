jQuery(document).ready(function ($) {

	/**
	 * Settings screen JS
	 */
	var EDD_Acq_Configuration = {

		init : function() {
			this.acq_methods();
			this.move();
			this.make_value();
		},

		acq_methods : function() {

			$('#edd-acq-add-method').on('click', function() {
				var row = $('#edd-acquisition-methods tr:last');
				var clone = row.clone();
				var count = row.parent().find( 'tr' ).length;
				clone.find( 'td input' ).val( '' );
				clone.find( 'input, select' ).each(function() {
					var name = $( this ).attr( 'name' );
					name = name.replace( /\[(\d+)\]/, '[' + parseInt( count ) + ']');
					$( this ).attr( 'name', name ).attr( 'id', name );
				});
				clone.insertAfter( row );
				return false;
			});

			$('body').on('click', '#edd-acquisition-methods .edd-acq-remove-method', function() {
				if( confirm( 'Remove this method? Reports will still be available for past records.' ) ) {
					var count = $('#edd-acquisition-methods tr:visible').length;

					if( count === 2 ) {
						$('#edd-acquisition-methods input[type="text"]').val('');
					} else {
						$(this).closest('tr').remove();

						var rows  = 0;
						$('.edd-acq-method-row' ).each(function() {

							$(this).find( 'input, select' ).each(function() {
								var name = $( this ).attr( 'name' );
								name = name.replace( /\[(\d+)\]/, '[' + parseInt( rows ) + ']');
								$( this ).attr( 'name', name ).attr( 'id', name );
							});

							rows++;
						});
					}
				}
				return false;
			});

		},

		move : function() {

			$("#edd-acquisition-methods tbody").sortable({
				handle: '.edd_draghandle', items: '.edd-acq-method-row', opacity: 0.6, cursor: 'move', axis: 'y', update: function() {
					var count  = 0;
					$(this).find( 'tr' ).each(function() {

						$(this).find( 'input, select' ).each(function() {
							var name = $( this ).attr( 'name' );
							name = name.replace( /\[(\d+)\]/, '[' + parseInt( count ) + ']');
							$( this ).attr( 'name', name ).attr( 'id', name );
						});

						count++;
					});
				}
			});

		},

		make_value: function() {
			$('body').on( 'blur', '.edd-acq-name input', function() {

				var name = $(this).val().replace(/\W+/g, '-').toLowerCase();

				var value_input   = $(this).parent().parent().parent().find('.edd-acq-value input');
				var current_value = value_input.val();

				if ( current_value.length == 0 ) {
					var found_names = 0;
					$('#edd-acquisition-methods tbody').find( '.edd-acq-value input').each(function() {
						if ( $(this).val() == name ) {
							found_names++;
						}
					});

					if ( found_names > 0 ) {
						name = name + found_names;
					}

					value_input.val(name);
				}
			});
		}

	}
	EDD_Acq_Configuration.init();

});
