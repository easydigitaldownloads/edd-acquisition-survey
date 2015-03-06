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

// store the custom field data in the payment meta
function edd_acq_store_custom_fields( $payment, $payment_data ) {

	$acquisition_method = isset( $_POST['edd_acquisition_method'] ) ? sanitize_text_field( $_POST['edd_acquisition_method'] ) : '';
	if ( ! empty( $acquisition_method ) && -1 != $acquisition_method ) {
		update_post_meta( $payment, '_edd_payment_acquisition_method', $acquisition_method );
	}

}
add_action( 'edd_insert_payment', 'edd_acq_store_custom_fields', 10, 2 );

function edd_acq_add_reports_item( $reports ) {
	$reports['acquisition'] = __( 'Acquisition', 'edd-acquisition-survey' );

	return $reports;
}
add_filter( 'edd_report_views', 'edd_acq_add_reports_item', 10, 1 );

/**
 * Renders the Gateways Table
 *
 * @since 1.3
 * @uses EDD_Gateawy_Reports_Table::prepare_items()
 * @uses EDD_Gateawy_Reports_Table::display()
 * @return void
 */
function edd_reports_acquisition_table() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/admin/class-acquisition-reports-table.php' );

	$downloads_table = new EDD_Acquisition_Reports_Table();
	$downloads_table->prepare_items();
	$downloads_table->display();
}
add_action( 'edd_reports_view_acquisition', 'edd_reports_acquisition_table' );

function edd_acq_count_sales_by_method( $method ) {

	global $wpdb;

	$ids_sql = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_acquisition_method' AND meta_value = %s", $method );
	$ids     = $wpdb->get_col( $ids_sql );

	return count( $ids );

}

function edd_acq_count_earnings_by_method( $method ) {

	global $wpdb;

	$ids_sql = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_acquisition_method' AND meta_value = %s", $method );
	$ids     = $wpdb->get_col( $ids_sql );
	$ids     = "'" . implode( "', '", $ids ) . "'";

	$earnings_sql = "SELECT SUM( meta_value ) FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_total' AND post_id IN ( $ids )";
	$earnings     = $wpdb->get_var( $earnings_sql );

	return $earnings;

}

function edd_acq_method_payment_details( $payment_id ) {
	$acquisition_method = edd_get_payment_meta( $payment_id, '_edd_payment_acquisition_method', true );
	if ( ! empty( $acquisition_method ) ) :
		$current_methods = edd_acq_get_methods();
		foreach ( $current_methods as $method ) {
			if ( $method['value'] === $acquisition_method ) {
				$acquisition_name = $method['name'];
				break;
			}
		}

		if ( empty( $acquisition_name ) ) {
			$acquisition_name = $acquisition_method . ' - ' . __( 'inactive' , 'edd-acquisition-survey' );
		}
		?>
		<div class="edd-order-acquisition-method edd-admin-box-inside">
			<p>
				<span class="label"><?php _e( 'Acquisition:', 'edd-acquisition-survey' ); ?></span>&nbsp;
				<span><?php echo $acquisition_name; ?></span>
			</p>
		</div>
		<?php
	endif;
}
add_action( 'edd_view_order_details_payment_meta_after', 'edd_acq_method_payment_details', 10, 1 );
