<?php
/**
 * Affiliates Registration Fields Template
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Registration;

use DDWCAffiliates\Helper\Error\DDWCAF_Error_Helper;
use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Affiliate_Registration_Fields_Template' ) ) {
	/**
	 * Affiliates Registration Fields list class
	 */
	class DDWCAF_Affiliate_Registration_Fields_Template extends \WP_List_table {
		/**
		 * Error Helper Trait
		 */
		use DDWCAF_Error_Helper;

        /**
         * Configuration Variable
         *
         * @var array
         */
        protected $ddwcaf_configuration;

		/**
         * Affiliate Helper Variable
         *
         * @var object
         */
        protected $affiliate_helper;

        /**
         * Affiliate registration fields Variable
         *
         * @var array
         */
        protected $affiliate_registration_fields;

        /**
         * Construct
         * 
         * @param array $ddwcaf_configuration
         */
        public function __construct( $ddwcaf_configuration ) {
            $this->ddwcaf_configuration = $ddwcaf_configuration;
            $this->affiliate_helper     = new DDWCAF_Affiliate_Helper( $ddwcaf_configuration );
            parent::__construct( [
				'singular' => esc_html__( 'Registration Field List', 'affiliates-for-woocommerce' ),
				'plural'   => esc_html__( 'Registration Fields List', 'affiliates-for-woocommerce' ),
				'ajax'     => false,
			] );
		}

		/**
		 * Prepare Items
		 *
		 * @return void
		 */
		public function prepare_items() {
			$this->_column_headers = $this->get_column_info();

			$request_scheme = is_ssl() ? 'https' : 'http';
			$current_url    = sanitize_url( "$request_scheme://" . wp_unslash( $_SERVER[ 'HTTP_HOST' ] ) . wp_unslash( $_SERVER[ 'REQUEST_URI' ] ) );

            if ( strpos( $current_url, '_wp_http_referer' ) !== false ) {
                $new_url = remove_query_arg( [ '_wp_http_referer', '_wpnonce' ], stripslashes( $current_url ) );
				wp_safe_redirect( $new_url );
				exit();
			}

			$this->process_bulk_action();

            $search = ! empty( $_GET[ 's' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 's' ] ) ) : '';

			$per_page     = $this->get_items_per_page( 'ddwcaf_affiliates_registration_fields_per_page', 20 );
			$current_page = $this->get_pagenum();
			$off          = ( $current_page - 1 ) * $per_page;

            $this->affiliate_registration_fields = $this->affiliate_helper->ddwcaf_get_affiliate_registration_fields();

			if ( ! empty( $_GET[ 's' ] ) ) {
				$search = strtolower( sanitize_text_field( wp_unslash( $_GET[ 's' ] ) ) );

				$this->affiliate_registration_fields = array_filter( $this->affiliate_registration_fields, function( $affiliate_registration_field ) use ( $search ) {
					return strpos( strtolower( $affiliate_registration_field[ 'label' ] ), $search ) !== false;
				} );
			}

			$total_items = count( $this->affiliate_registration_fields );

			$this->set_pagination_args( [
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			] );

			$data = array_slice( $this->ddwcaf_prepare_data(), $off, $per_page );

			usort( $data, [ $this, 'usort_reorder' ] );

			$this->items = $data;
		}

		/**
		 * Usort
		 *
		 * @param int $first First value.
		 * @param int $second Second value.
		 * @return $result
		 */
		public function usort_reorder( $first, $second ) {
			$orderby = ! empty( $_GET[ 'orderby' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'orderby' ] ) ) : 'position';
			$order   = ! empty( $_GET[ 'order' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'order' ] ) ) : 'asc';
			$result  = strnatcmp( $first[ $orderby ], $second[ $orderby ] );

			return 'asc' === $order ? $result : -$result;
		}

		/**
		 * Fetch Data function
		 *
		 * @return array $data
		 */
		public function ddwcaf_prepare_data() {
            $data = [];

            if ( ! empty( $this->affiliate_registration_fields ) ) {
                foreach ( $this->affiliate_registration_fields as $id => $registration_field ) {
                    ob_start();
                    ?>
                    <span class="ddwcaf-required dashicons <?php echo esc_attr( $registration_field[ 'required' ] ? 'ddwcaf-required-yes dashicons-yes' : 'ddwcaf-required-no dashicons-no' ); ?>"></span>
                    <?php
					$required_html = ob_get_clean();

					ob_start();
                    ?>
                    <span class="ddwcaf-required dashicons <?php echo esc_attr( $registration_field[ 'editable' ] ? 'ddwcaf-required-yes dashicons-yes' : 'ddwcaf-required-no dashicons-no' ); ?>"></span>
                    <?php
					$editable_html = ob_get_clean();

                    ob_start();
					?>
					<mark class="ddwcaf-status ddwcaf-field-status-<?php echo esc_attr( $registration_field[ 'status' ] ); ?>"><span><?php echo esc_html( $this->affiliate_helper->ddwcaf_get_translation( $registration_field[ 'status' ] ) ); ?></span></mark>
					<?php
					$status_html = ob_get_clean();

                    $data[] = [
                        'id'       => $id,
                        'modify'   => $registration_field[ 'modify' ],
                        'label'    => $registration_field[ 'label' ] . ( ! $registration_field[ 'modify' ] ? ' ' . wc_help_tip( esc_html__( 'This field is protected and can\'t be deleted or modified. It is used in the registration form and it is active or inactive as per the WooCommerce configuration.', 'affiliates-for-woocommerce' ) ) : '' ),
                        'type'     => $registration_field[ 'type' ],
                        'name'     => $registration_field[ 'name' ],
                        'position' => $registration_field[ 'position' ],
                        'required' => $required_html,
                        'editable' => $editable_html,
                        'status'   => $status_html,
					];
                }
            }

			return apply_filters( 'ddwcaf_affiliates_registration_fields_list_data', $data );
		}

		/**
		 * Process bulk actions
		 *
		 * @return void
		 */
		public function process_bulk_action() {
			if ( ! empty( $_GET[ 'ddwcaf_nonce' ] ) && wp_unslash( $_GET[ 'ddwcaf_nonce' ] ) ) { // WPCS: sanitization ok.
				$nonce = filter_input( INPUT_GET, 'ddwcaf_nonce', FILTER_SANITIZE_STRING );
				if ( wp_verify_nonce( $nonce, 'ddwcaf_nonce_action' ) ) {
					$action = $this->current_action();

					if ( in_array( $action, [ 'active', 'inactive' ] ) ) {
						if ( ! empty( $_GET[ 'ddwcaf-id' ] ) ) { // WPCS: input var ok.
							if ( is_array( $_GET[ 'ddwcaf-id' ] ) ) { // WPCS: input var ok.
								$ids = array_map( 'sanitize_text_field', wp_unslash( $_GET[ 'ddwcaf-id' ] ) ); // WPCS: input var ok.

								$success = $error = 0;

								foreach ( $ids as $id ) {
									$response = $this->affiliate_helper->ddwcaf_update_affiliate_registration_field_status( $id, $action );

									if ( $response ) {
										$success++;
									} else {
										$error++;
									}
								}

                                if ( $success ) {
									$message = sprintf( esc_html__( 'Status changed for %d field(s) successfully.', 'affiliates-for-woocommerce' ), $success );

                                    $this->ddwcaf_print_notification( $message );
                                }

                                if ( $error ) {
                                    $message = sprintf( esc_html__( '%d field(s) not exits.', 'affiliates-for-woocommerce' ), $error );
                                    $this->ddwcaf_print_notification( $message, 'error' );
                                }
							}
						} else {
							$message = esc_html__( 'Select field(s) to take action.', 'affiliates-for-woocommerce' );
							$this->ddwcaf_print_notification( $message, 'error' );
						}
					}
				} else {
					$message = esc_html__( 'Invalid nonce. Security check failed!!!', 'affiliates-for-woocommerce' );
					$this->ddwcaf_print_notification( $message, 'error' );
				}
			}
		}

		/**
		 * No items
		 *
		 * @return void
		 */
		public function no_items() {
			esc_html_e( 'No fields avaliable.', 'affiliates-for-woocommerce' );
		}

		/**
		 * Hidden Columns
		 *
		 * @return array
		 */
		public function get_hidden_columns() {
			return [];
		}

		/**
		 *  Associative array of columns
		 *
		 * @return array
		 */
		public function get_columns() {
			$columns = [
				'cb'       => '<input type="checkbox" />',
				'label'    => esc_html__( 'Label', 'affiliates-for-woocommerce' ),
				'type'     => esc_html__( 'Type', 'affiliates-for-woocommerce' ),
				'name'     => esc_html__( 'Name', 'affiliates-for-woocommerce' ),
				'position' => esc_html__( 'Position', 'affiliates-for-woocommerce' ),
				'required' => esc_html__( 'Required', 'affiliates-for-woocommerce' ),
				'editable' => esc_html__( 'Editable', 'affiliates-for-woocommerce' ),
				'status'   => esc_html__( 'Status', 'affiliates-for-woocommerce' ),
			];

			return apply_filters( 'ddwcaf_affiliates_registration_fields_list_columns', $columns );
		}

		/**
		 * Render a column when no column specific method exists.
		 *
		 * @param array  $item Items.
		 * @param string $column_name Name.
		 *
		 * @return mixed
		 */
		public function column_default( $item, $column_name ) {
			return array_key_exists( $column_name, $item ) ? $item[ $column_name ] : print_r( $item, true );
		}

		/**
		 * Columns to make sortable.
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			return apply_filters( 'ddwcaf_affiliates_registration_fields_list_sortable_columns', [
				'id'       => [ 'id', true ],
				'label'    => [ 'label', true ],
				'type'     => [ 'type', true ],
				'name'     => [ 'name', true ],
				'position' => [ 'position', true ],
			] );
		}

		/**
		 * Render the bulk edit checkbox
		 *
		 * @param array $item Item.
		 * @return string
		 */
		public function column_cb( $item ) {
            if ( $item[ 'modify' ] ) {
                return sprintf( '<input type="checkbox" name="ddwcaf-id[]" value="%d" />', esc_attr( $item[ 'id' ] ) );
            }
            return '';
		}

		/**
		 * Column actions
		 *
		 * @param array $item Items.
		 * @return array $actions
		 */
		public function column_label( $item ) {
			$search       = ! empty( $_GET[ 's' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 's' ] ) ) : '';
			$current_page = $this->get_pagenum();

            if ( $item[ 'modify' ] ) {
                $actions = [
                    'edit'   => sprintf( '<a href="%s">%s</a>', sanitize_url( admin_url( 'admin.php?page=' . $_REQUEST[ 'page' ] . "&action=edit&ddwcaf-id=" . $item[ 'id' ] ) ), esc_html__( 'Edit', 'affiliates-for-woocommerce' ) ),
                ];
            } else {
                $actions = [];
            }

			return sprintf( '%1$s %2$s', $item[ 'label' ], $this->row_actions( apply_filters( 'ddwcaf_affiliates_registration_fields_list_line_actions', $actions ) ) );
		}

		/**
         * Bulk actions on list.
		 * 
		 * @return array
         */
        public function get_bulk_actions() {
            return apply_filters( 'ddwcaf_modify_bulk_actions_in_affiliates_registration_fields_list', [
                'active'   => esc_html__( 'Change to Active', 'affiliates-for-woocommerce' ),
                'inactive' => esc_html__( 'Change to Inactive', 'affiliates-for-woocommerce' ),
			] );
		}
	}
}
