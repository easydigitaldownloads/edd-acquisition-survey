<?php
/**
 * Acquisition Methods Reports Table Class
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2015, Chris Klosowski
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EDD_Acquisition_Reports_Table Class
 *
 * Renders the Download Reports table
 *
 * @since 1.0
 */
class EDD_Acquisition_Reports_Table extends WP_List_Table {

	/**
	 * @var int Number of items per page
	 * @since 1.0
	 */
	public $per_page = 30;


	/**
	 * Get things started
	 *
	 * @since 1.0
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular'  => __( 'Acquisition Method', 'edd-acquisistion-survey' ),
			'plural'    => __( 'Acquisition Methods', 'edd-acquisistion-survey' ),
			'ajax'      => false
		) );

	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since 1.0
	 *
	 * @param array $item Contains all the data of the downloads
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since 1.0
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'name'     => __( 'Method'  , 'edd-acquisistion-survey' ),
			'sales'    => __( 'Sales'   , 'edd-acquisistion-survey' ),
			'earnings' => __( 'Earnings', 'edd-acquisistion-survey' )
		);

		return $columns;
	}


	/**
	 * Retrieve the current page number
	 *
	 * @access public
	 * @since 1.0
	 * @return int Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}


	/**
	 * Outputs the reporting views
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function bulk_actions( $which = '' ) {
		// These aren't really bulk actions but this outputs the markup in the right place
		edd_report_views();
	}


	/**
	 * Build all the reports data
	 *
	 * @access public
	 * @since 1.0
	 * @return array $reports_data All the data for customer reports
	 */
	public function reports_data() {

		global $wpdb;
		$reports_data = array();
		$methods      = edd_acq_get_methods();

		$sql     = "SELECT DISTINCT( meta_value ) as value FROM $wpdb->postmeta WHERE meta_key = '_edd_payment_acquisition_method'";
		$methods = $wpdb->get_results( $sql );

		$current_method_data = edd_acq_get_methods();

		foreach ( $methods as $method ) {

			$acquisition_name = false;

			foreach ( $current_method_data as $current_method ) {
				if ( $current_method['value'] === $method->value ) {
					$acquisition_name = $current_method['name'];
					break;
				}
			}

			if ( empty( $acquisition_name ) ) {
				$acquisition_name = $method->value . ' - ' . __( 'inactive' , 'edd-acquisition-survey' );
			}

			$sales    = edd_acq_count_sales_by_method( $method->value );
			$earnings = edd_acq_count_earnings_by_method( $method->value );

			$reports_data[] = array(
				'name'     => $acquisition_name,
				'sales'    => edd_format_amount( $sales, false ),
				'earnings' => edd_currency_filter( edd_format_amount( $earnings ) ),
			);
		}

		return $reports_data;
	}


	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since 1.0
	 * @uses EDD_Acquisition_Reports_Table::get_columns()
	 * @uses EDD_Acquisition_Reports_Table::get_sortable_columns()
	 * @uses EDD_Acquisition_Reports_Table::reports_data()
	 * @return void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->reports_data();

	}
}
