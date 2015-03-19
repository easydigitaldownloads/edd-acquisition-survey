<?php
/**
 * Helper Functions
 *
 * @package     EDD\AcquisitionSurvey\Functions
 * @since       1.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Retrieve the stored methods
 *
 * @since  1.0
 * @return array The array of methods
 */
function edd_acq_get_methods() {
	return apply_filters( 'edd_acq_get_methods', get_option( 'edd_acq_methods', array() ) );
}

/**
 * Output the dropdown on the checkout form
 *
 * @since  1.0
 * @return void
 */
function edd_acq_custom_checkout_fields() {
	static $has_displayed = NULL;

	// Make sure we don't display more than once.
	if ( NULL !== $has_displayed ) {
		return;
	}

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

	$has_displayed = true;
}
add_action('edd_purchase_form_user_info', 'edd_acq_custom_checkout_fields');

/**
 * Validates the survey results on checkout
 *
 * @since  1.0
 * @param  array $valid_data The array of valid data
 * @param  array $data       The data submitted
 * @return void
 */
function edd_acq_validate_custom_fields( $valid_data, $data ) {

	$methods  = edd_acq_get_methods();
	if ( empty( $methods ) ) {
		return;
	}

	$required = edd_get_option( 'acq_require_response', false );
	if ( $required && ( empty( $data['edd_acquisition_method'] ) || $data['edd_acquisition_method'] == '-1' ) ) {
		// check for a phone number
		edd_set_error( 'invalid_acquisition_method', __( 'Please tell us how you found us.', 'edd-acquisition-survey' ) );
	}

}
add_action( 'edd_checkout_error_checks', 'edd_acq_validate_custom_fields', 10, 2 );

/**
 * Saves the Acquisition method upon payment insertion
 *
 * @since  1.0
 * @param  int $payment      The Payment ID
 * @param  array $payment_data The payment data
 * @return void
 */
function edd_acq_store_custom_fields( $payment, $payment_data ) {

	$acquisition_method = isset( $_POST['edd_acquisition_method'] ) ? sanitize_text_field( $_POST['edd_acquisition_method'] ) : '';

	$acquisition_method = apply_filters( 'edd_acq_record_acquisition_method', $acquisition_method, $payment, $payment_data );
	if ( ! empty( $acquisition_method ) && -1 != $acquisition_method ) {
		update_post_meta( $payment, '_edd_payment_acquisition_method', $acquisition_method );
	}

}
add_action( 'edd_insert_payment', 'edd_acq_store_custom_fields', 10, 2 );

/**
 * Given an acquisition method shortname, get the total sales
 *
 * @since  1.0
 * @param  string $method The method shortname (value)
 * @return int            Total sales count for given method
 */
function edd_acq_count_sales_by_method( $method ) {

	global $wpdb;

	$ids_sql = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_acquisition_method' AND meta_value = %s", $method );
	$ids     = $wpdb->get_col( $ids_sql );

	return apply_filters( 'edd_acq_method_sales', count( $ids ), $method );

}

/**
 * Given an acquisition method shortname, get the total sales
 *
 * @since  1.0
 * @param  string $method The method shortname (value)
 * @return string         The unformatted earnings for the method
 */
function edd_acq_count_earnings_by_method( $method ) {

	global $wpdb;

	$ids_sql = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_acquisition_method' AND meta_value = %s", $method );
	$ids     = $wpdb->get_col( $ids_sql );
	$ids     = "'" . implode( "', '", $ids ) . "'";

	$earnings_sql = "SELECT SUM( meta_value ) FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_total' AND post_id IN ( $ids )";
	$earnings     = $wpdb->get_var( $earnings_sql );

	return apply_filters( 'edd_acq_method_earnings', $earnings, $method );

}
