<?php
/**
 * Visits List Template
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Visits;

use DDWCAffiliates\Helper\Error\DDWCAF_Error_Helper;
use DDWCAffiliates\Helper\Visit\DDWCAF_Visit_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Visits_List_Template' ) ) {
	/**
	 * Visits list class
	 */
	class DDWCAF_Visits_List_Template extends \WP_List_table {
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
         * Visit Helper Variable
         *
         * @var object
         */
        protected $visit_helper;

		/**
         * Args Variable
         *
         * @var array
         */
        protected $args;

        /**
         * Construct
         * 
         * @param array $ddwcaf_configuration
         */
        public function __construct( $ddwcaf_configuration ) {
            $this->ddwcaf_configuration = $ddwcaf_configuration;
            $this->visit_helper     = new DDWCAF_Visit_Helper( $ddwcaf_configuration );

            parent::__construct( [
				'singular' => esc_html__( 'Visit List', 'affiliates-for-woocommerce' ),
				'plural'   => esc_html__( 'Visits List', 'affiliates-for-woocommerce' ),
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

            $per_page     = $this->get_items_per_page( 'ddwcaf_visits_per_page', 20 );
            $current_page = $this->get_pagenum();
            $offset       = ( $current_page - 1 ) * $per_page;

            $this->args = [
                'search'       => ! empty( $_GET[ 's' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 's' ] ) ) : '',
                'show'         => ! empty( $_GET[ 'show' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'show' ] ) ) : '',
                'affiliate_id' => ! empty( $_GET[ 'affiliate-id' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'affiliate-id' ] ) ) : '',
                'from_date'    => ! empty( $_GET[ 'from-date' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'from-date' ] ) ) : '',
                'end_date'     => ! empty( $_GET[ 'end-date' ] ) ? sanitize_text_field( wp_unslash ( $_GET[ 'end-date' ] ) ) : '',
                'per_page'     => $per_page,
                'offset'       => $offset,
            ];

            $total_items  = $this->visit_helper->ddwcaf_get_visits_count( $this->args );
            $visits       = $this->visit_helper->ddwcaf_get_visits( $this->args );

			$this->set_pagination_args( [
				'total_items' => $total_items,
				'per_page'    => $per_page,
			] );

			$data = $this->ddwcaf_prepare_data( $visits );

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
			$orderby = ! empty( $_GET[ 'orderby' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'orderby' ] ) ) : 'id';
			$order   = ! empty( $_GET[ 'order' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'order' ] ) ) : 'desc';
			$result  = strnatcmp( $first[ $orderby ], $second[ $orderby ] );

			return 'asc' === $order ? $result : -$result;
		}

		/**
		 * Fetch Data function
		 *
		 * @param array $visits
		 * @return array $data
		 */
		public function ddwcaf_prepare_data( $visits ) {
            $data = [];

            if ( ! empty( $visits ) ) {
				$date_format = get_option( 'date_format' );
				$time_format = get_option( 'time_format' );

                foreach ( $visits as $visit ) {
                    $affiliate_id   = $visit[ 'affiliate_id' ];
                    $affiliate_data = get_userdata( $affiliate_id );

					if ( ! empty( $visit[ 'order_id' ] ) ) {
						$order = wc_get_order( $visit[ 'order_id' ] );
						$buyer = '';

						if ( $order->get_billing_first_name() || $order->get_billing_last_name() ) {
							/* translators: 1: first name 2: last name */
							$buyer = trim( sprintf( _x( '%1$s %2$s', 'full name', 'affiliates-for-woocommerce' ), $order->get_billing_first_name(), $order->get_billing_last_name() ) );
						} elseif ( $order->get_billing_company() ) {
							$buyer = trim( $order->get_billing_company() );
						} elseif ( $order->get_customer_id() ) {
							$user  = get_user_by( 'id', $order->get_customer_id() );
							$buyer = ucwords( $user->display_name );
						}

						/**
						 * Filter buyer name in list table orders.
						 *
						 * @since 3.7.0
						 *
						 * @param string   $buyer Buyer name.
						 * @param WC_Order $order Order data.
						 */
						$buyer = apply_filters( 'woocommerce_admin_order_buyer_name', $buyer, $order );
						$order_html = '<a href="#" class="order-preview" data-order-id="' . absint( $order->get_id() ) . '" title="' . esc_attr( __( 'Preview', 'affiliates-for-woocommerce' ) ) . '">' . esc_html__( 'Preview', 'affiliates-for-woocommerce' ) . '</a><a href="' . esc_url( admin_url( 'post.php?post=' . absint( $order->get_id() ) ) . '&action=edit' ) . '"><strong>#' . esc_attr( $order->get_order_number() ) . ' ' . esc_html( $buyer ) . '</strong></a>';
					} else {
						$order_html = 'N/A';
					}

                    ob_start();
                    ?>
                    <a href="<?php echo esc_url( $visit[ 'url' ] ); ?>" target="_blank"><?php echo esc_url( $visit[ 'url' ] ); ?></a>
                    <small class="meta"><?php esc_html_e( 'Guest IP: [Pro]', 'affiliates-for-woocommerce' ); ?></small>
                    <?php
                    $url = ob_get_clean();

                    $data[] = [
                        'id'                => $visit[ 'id' ],
                        'affiliate_details' => '<a href="' . esc_url( admin_url( "admin.php?page=affiliates-for-woocommerce&action=view&ddwcaf-id={$affiliate_id}" ) ) . '">' . esc_html( "(#{$affiliate_id}) {$affiliate_data->user_login} <{$affiliate_data->user_email}>" ) . '</a>',
                        'url'               => $url,
                        'referrer_url'      => ! empty( $visit[ 'referrer_url' ] ) ? '<a href="' . esc_url( $visit[ 'referrer_url' ] ) . '">' . esc_url( $visit[ 'referrer_url' ] ) . '</a>' : 'N/A',
                        'order'             => $order_html,
                        'date'              => date_i18n( "{$date_format} {$time_format}", strtotime( $visit[ 'date' ] ) ),
                        'conversion_date'   => ! empty( $visit[ 'conversion_date' ] ) ? date_i18n( "{$date_format} {$time_format}", strtotime( $visit[ 'conversion_date' ] ) ) : 'N/A',
					];
                }
            }

			return apply_filters( 'ddwcaf_visits_list_data', $data );
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

					if ( in_array( $action, [ 'delete' ] ) ) {
						if ( ! empty( $_GET[ 'ddwcaf-id' ] ) ) { // WPCS: input var ok.
							if ( is_array( $_GET[ 'ddwcaf-id' ] ) ) { // WPCS: input var ok.
								$ids = array_map( 'sanitize_text_field', wp_unslash( $_GET[ 'ddwcaf-id' ] ) ); // WPCS: input var ok.

								$success = $error = 0;

                                foreach ( $ids as $id ) {
                                    $response = $this->visit_helper->ddwcaf_delete_visit( $id );

                                    if ( $response ) {
                                        $success++;
                                    } else {
                                        $error++;
                                    }
                                }

                                if ( $success ) {
                                    $message = sprintf( esc_html__( '%d visit(s) deleted successfully.', 'affiliates-for-woocommerce' ), $success );

                                    $this->ddwcaf_print_notification( $message );
                                }

                                if ( $error ) {
                                    $message = sprintf( esc_html__( '%d visit(s) not exits.', 'affiliates-for-woocommerce' ), $error );
                                    $this->ddwcaf_print_notification( $message, 'error' );
                                }
							}
						} else {
							$message = esc_html__( 'Select visit(s) to delete.', 'affiliates-for-woocommerce' );
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
		 *  No items
		 *
		 * @return void
		 */
		public function no_items() {
			esc_html_e( 'No visits avaliable.', 'affiliates-for-woocommerce' );
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
		 * Associative array of columns
		 *
		 * @return array
		 */
		public function get_columns() {
			$columns = [
				'cb'                => '<input type="checkbox" />',
				'id'                => esc_html__( 'ID', 'affiliates-for-woocommerce' ),
				'affiliate_details' => esc_html__( 'Affiliate', 'affiliates-for-woocommerce' ),
				'order'             => esc_html__( 'Order', 'affiliates-for-woocommerce' ),
				'url'               => esc_html__( 'Referral URL', 'affiliates-for-woocommerce' ),
				'referrer_url'      => esc_html__( 'Referrer/Origin URL', 'affiliates-for-woocommerce' ),
				'date'              => esc_html__( 'Date', 'affiliates-for-woocommerce' ),
				'conversion_date'   => esc_html__( 'Conversion Date', 'affiliates-for-woocommerce' ),
			];

			return apply_filters( 'ddwcaf_visits_list_columns', $columns );
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
			return apply_filters( 'ddwcaf_visits_list_sortable_columns', [
				'id'                => [ 'id', true ],
				'affiliate_details' => [ 'affiliate_details', true ],
				'url'               => [ 'url', true ],
				'date'              => [ 'date', true ],
				'conversion_date'   => [ 'conversion_date', true ],
			] );
		}

		/**
		 * Render the bulk edit checkbox
		 *
		 * @param array $item Item.
		 * @return string
		 */
		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" name="ddwcaf-id[]" value="%d" />', esc_attr( $item[ 'id' ] ) );
		}

		/**
         * Bulk actions on list.
		 * 
		 * @return array
         */
        public function get_bulk_actions() {
            return apply_filters( 'ddwcaf_modify_bulk_actions_in_visits_list', [
                'delete' => esc_html__( 'Delete', 'affiliates-for-woocommerce' ),
			] );
		}

        /**
		 * Filters function
		 *
		 * @param string $which Position of filter.
		 */
		public function extra_tablenav( $which ) {
			if ( 'top' === $which ) {
                $affiliate_id = $this->args[ 'affiliate_id' ];
				?>
				<div class="alignleft actions bulkactions ddwcaf-bulk-actions">
                    <label for="from-date"><?php esc_html_e( 'From:', 'affiliates-for-woocommerce' ); ?></label>
					<input type="date" value="<?php echo esc_attr( $this->args[ 'from_date' ] ); ?>" name="from-date" id="from-date" class="ddwcaf-datepicker" placeholder="yyyy-mm-dd" autocomplete="off" />

					<label for="end-date"><?php esc_html_e( 'To:', 'affiliates-for-woocommerce' ); ?></label>
					<input type="date" value="<?php echo esc_attr( $this->args[ 'end_date' ] ); ?>" name="end-date" id="end-date" class="ddwcaf-datepicker" placeholder="yyyy-mm-dd" autocomplete="off" />

                    <select name="affiliate-id" class="ddwcaf-affiliate" data-placeholder="<?php esc_attr_e( 'Select Affiliate', 'affiliates-for-woocommerce' ); ?>">
                        <?php
                        if ( ! empty( $affiliate_id ) ) {
                            $affiliate_data = get_userdata( $affiliate_id );
                            ?>
                            <option value="<?php echo esc_attr( $affiliate_id ); ?>"><?php echo esc_html( "(#{$affiliate_id}) {$affiliate_data->user_login} <{$affiliate_data->user_email}>" ); ?></option>
                            <?php
                        }
                        ?>
                    </select>

					<input type="submit" value="<?php esc_attr_e( 'Filter', 'affiliates-for-woocommerce' ); ?>" name="ddwcaf_filter_submit" class="button" />
				</div>
				<?php
			}
		}
	}
}
