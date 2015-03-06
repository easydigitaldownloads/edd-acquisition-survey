<?php
/**
 * Helper Functions
 *
 * @package     EDD\AcquisitionSurvey\Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

function edd_acq_get_methods() {
	return get_option( 'edd_acq_methods', array() );
}

// output our custom field HTML
function edd_acq_custom_checkout_fields() {
	$methods  = edd_acq_get_methods();
	$title    = edd_get_option( 'acq_survey_title', __( 'How did you hear about us?', 'edd-acquisition-survey' ) );
	$required = edd_get_option( 'acq_require_response', false );

	if ( empty( $methods ) ) {
		return;
	}
?>
	<p id="edd-acq-wrap">
		<label class="edd-label" for="edd-acq-method"><?php echo $title; ?><?php if ( $required ) : ?><span class="edd-required-indicator">*</span><?php endif; ?></label>
		<?php
		$options = array();
		foreach ( $methods as $method ) {
			$options[$method['value']] = $method['name'];
		}

		if ( ! empty( $options ) ) {
			$args = array(
				'options'          => $options,
				'name'             => 'edd_acquisition_method',
				'class'            => 'edd-acq-method',
				'id'               => '',
				'show_option_all'  => 0,
				'show_option_none' => _x( 'Select One', 'no dropdown items', 'edd-acquisition-survey' )
			);

			echo EDD()->html->select( $args );
		}

		?>
	</p>
	<?php
}
add_action('edd_purchase_form_user_info', 'edd_acq_custom_checkout_fields');

// check for errors with out custom fields
function edd_acq_validate_custom_fields( $valid_data, $data ) {

	$required = edd_get_option( 'acq_require_response', false );
	if ( $required && ( empty( $data['edd_acquisition_method'] ) || $data['edd_acquisition_method'] == '-1' ) ) {
		// check for a phone number
		edd_set_error( 'invalid_acquisition_method', __( 'Please tell us how you found us.', 'edd-acquisition-survey' ) );
	}

}
add_action( 'edd_checkout_error_checks', 'edd_acq_validate_custom_fields', 10, 2 );

// store the custom field data in the payment meta
function edd_acq_store_custom_fields( $payment, $payment_data ) {

	$acquisition_method = isset( $_POST['edd_acquisition_method'] ) ? sanitize_text_field( $_POST['edd_acquisition_method'] ) : '';
	if ( ! empty( $acquisition_method ) && -1 != $acquisition_method ) {
		update_post_meta( $payment, '_edd_payment_acquisition_method', $acquisition_method );
	}

}
add_action( 'edd_insert_payment', 'edd_acq_store_custom_fields', 10, 2 );
