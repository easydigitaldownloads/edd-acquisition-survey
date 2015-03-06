jQuery(document).ready(function ($) {

	/**
	 * Reports / Exports screen JS
	 */
	/*
	var EDD_Reports = {

		init : function() {
			this.date_options();
			this.customers_export();
		},

		date_options : function() {

			// Show hide extended date options
			$( '#edd-graphs-date-options' ).change( function() {
				var $this = $(this);
				if ( 'other' === $this.val() ) {
					$( '#edd-date-range-options' ).show();
				} else {
					$( '#edd-date-range-options' ).hide();
				}
			});

		},

		customers_export : function() {

			// Show / hide Download option when exporting customers

			$( '#edd_customer_export_download' ).change( function() {

				var $this = $(this), download_id = $('option:selected', $this).val();

				if ( '0' === $this.val() ) {
					$( '#edd_customer_export_option' ).show();
				} else {
					$( '#edd_customer_export_option' ).hide();
				}

				// On Download Select, Check if Variable Prices Exist
				if ( parseInt( download_id ) != 0 ) {
					var data = {
						action : 'edd_check_for_download_price_variations',
						download_id: download_id
					};
					$.post(ajaxurl, data, function(response) {
						$('.edd_price_options_select').remove();
						$this.after( response );
					});
				} else {
					$('.edd_price_options_select').remove();
				}
			});

		}

	};
	EDD_Reports.init();*/


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

			// Insert new tax rate row
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

			// Remove tax row
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
					value_input.val(name);
				}
			});
		}

	}
	EDD_Acq_Configuration.init();

});
