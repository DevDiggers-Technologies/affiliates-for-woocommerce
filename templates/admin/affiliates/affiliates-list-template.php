<?php
/**
 * Affiliates List Template
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Affiliates;

use DDWCAffiliates\Helper\Error\DDWCAF_Error_Helper;
use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;
use DDWCAffiliates\Helper\Visit\DDWCAF_Visit_Helper;
use DDWCAffiliates\Helper\Commission\DDWCAF_Commission_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Affiliates_List_Template' ) ) {
	/**
	 * Affiliates list class
	 */
	class DDWCAF_Affiliates_List_Template extends \WP_List_table {
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
		 * Visit Helper Variable
		 *
		 * @var object
		 */
		protected $visit_helper;

		/**
		 * Commission Helper Variable
		 *
		 * @var object
		 */
		protected $commission_helper;

		/**
		 * Affiliate Status Helper Variable
		 *
		 * @var string
		 */
		protected $affiliate_status;

		/**
		 * Construct
		 * 
		 * @param array $ddwcaf_configuration
		 */
		public function __construct( $ddwcaf_configuration ) {
			$this->ddwcaf_configuration = $ddwcaf_configuration;
			$this->affiliate_helper     = new DDWCAF_Affiliate_Helper( $ddwcaf_configuration );
			$this->visit_helper         = new DDWCAF_Visit_Helper( $ddwcaf_configuration );
			$this->commission_helper    = new DDWCAF_Commission_Helper( $ddwcaf_configuration );

			parent::__construct( [
				'singular' => esc_html__( 'Affiliate List', 'affiliates-for-woocommerce' ),
				'plural'   => esc_html__( 'Affiliates List', 'affiliates-for-woocommerce' ),
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
			$current_url    = "$request_scheme://" . wp_unslash( $_SERVER[ 'HTTP_HOST' ] ) . wp_unslash( $_SERVER[ 'REQUEST_URI' ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ( strpos( $current_url, '_wp_http_referer' ) !== false ) {
				$new_url = remove_query_arg( [ '_wp_http_referer', '_wpnonce' ], stripslashes( $current_url ) );
				wp_safe_redirect( $new_url );
				exit();
			}

			$this->process_bulk_action();
			$this->process_row_action();

			$search                 = ! empty( $_GET[ 's' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 's' ] ) ) : '';
			$this->affiliate_status = ! empty( $_GET[ 'affiliate_status' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'affiliate_status' ] ) ) : '';
			$per_page               = $this->get_items_per_page( 'ddwcaf_affiliates_per_page', 20 );
			$current_page           = $this->get_pagenum();
			$off                    = ( $current_page - 1 ) * $per_page;

			$args = [
				'role'           => 'ddwcaf_affiliate',
				'number'         => $per_page,
				'offset'         => $off,
				'order'          => ! empty( $_GET[ 'order' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'order' ] ) ) : 'DESC',
				'orderby'        => ! empty( $_GET[ 'orderby' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'orderby' ] ) ) : 'ID',
				'search'         => '*' . esc_attr( $search ) . '*',
				'search_columns' => [ 'user_nicename', 'ID', 'user_login', 'user_email', 'display_name' ],
				'fields'         => [ 'ID', 'display_name', 'user_email', 'user_login', 'user_registered' ],
			];

			if ( ! empty( $this->affiliate_status ) ) {
				$args[ 'meta_query' ] = [
					[
						'key'     => '_ddwcaf_affiliate_status',
						'compare' => '=',
						'value'   => $this->affiliate_status
					]
				];
			}

			$query = new \WP_User_Query( $args );

			wp_reset_postdata();

			$this->set_pagination_args( [
				'total_items' => $query->get_total(),
				'per_page'    => $per_page,
			] );

			$data = $this->ddwcaf_prepare_data( $query->get_results() );

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
		 * @param array $users
		 * @return array $data
		 */
		public function ddwcaf_prepare_data( $users ) {
			$data = [];

			if ( ! empty( $users ) ) {
				$date_format = get_option( 'date_format' );
				$time_format = get_option( 'time_format' );

				$page = ! empty( $_GET[ 'page' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'page' ] ) ) : '';
				$menu = ! empty( $_GET[ 'menu' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'menu' ] ) ) : '';

				foreach ( $users as $user ) {
					$user_id          = $user->ID;
					$affiliate_status = $this->affiliate_helper->ddwcaf_get_affiliate_status( $user_id );

					ob_start();
					?>
					<mark class="ddwcaf-status ddwcaf-affiliate-status-<?php echo esc_attr( $affiliate_status ); ?>"><?php echo esc_html( $this->affiliate_helper->ddwcaf_get_translation( $affiliate_status ) ); ?></mark>
					<?php
					$affiliate_status_html = ob_get_clean();

					$args = [
						'affiliate_id' => $user_id,
					];

					$visits_count     = $this->visit_helper->ddwcaf_get_visits_count( $args );
					$statistics       = $this->commission_helper->ddwcaf_get_affiliate_statistics( $user_id );
					$conversion_rate  = wc_format_decimal( $this->visit_helper->ddwcaf_get_conversion_details( $args )[ 'conversion_rate' ], wc_get_price_decimals() );
					$registered_date  = get_date_from_gmt( $user->user_registered, "{$date_format} {$time_format}" );

					$data[] = [
						'id'              => $user_id,
						'affiliate'       => sprintf(
							'<div class="ddwcaf-affiliate-details-column">
								%1$s
								<div class="ddwcaf-affiliate-info">
									<div class="ddwcaf-affiliate-name"><strong>%2$s</strong></div>
									<div class="ddwcaf-affiliate-email">%3$s</div>
								</div>
							</div>',
							get_avatar( $user_id, 32 ),
							esc_html( $user->display_name ),
							esc_html( $user->user_email )
						),
						'commission_rate' => $this->commission_helper->ddwcaf_get_affiliate_commission_rate( $user_id ) . '%',
						'earnings'        => sprintf(
							'<div class="ddwcaf-amount-column">
								<div class="ddwcaf-total-earnings">
									<a href="%1$s" target="_blank"><strong>%2$s</strong></a>
								</div>
								<div class="ddwcaf-paid-unpaid-earnings">
									<span class="ddwcaf-paid-amount">%3$s %4$s</span> | <span class="ddwcaf-unpaid-amount">%5$s %6$s</span>
								</div>
							</div>',
							esc_url( admin_url( "admin.php?page={$page}&menu=commissions&affiliate-id={$user_id}" ) ),
							wc_price( $statistics[ 'total_earnings' ] ),
							esc_html__( 'Paid:', 'affiliates-for-woocommerce' ),
							'<a href="' . esc_url( admin_url( "admin.php?page={$page}&menu=commissions&show=paid&affiliate-id={$user_id}" ) ) . '" target="_blank">' . wc_price( $statistics[ 'paid_earnings' ] ) . '</a>',
							esc_html__( 'Unpaid:', 'affiliates-for-woocommerce' ),
							'<a href="' . esc_url( admin_url( "admin.php?page={$page}&menu=commissions&show=pending&affiliate-id={$user_id}" ) ) . '" target="_blank">' . wc_price( $statistics[ 'unpaid_earnings' ] ) . '</a>'
						),
						'metrics'         => sprintf(
							'<div class="ddwcaf-metrics-column">
								<div class="ddwcaf-visits-count">
									%1$s <a href="%2$s" target="_blank"><strong>%3$s</strong></a>
								</div>
								<div class="ddwcaf-conversion-rate">
									%4$s <a href="%5$s" target="_blank"><strong>%6$s%%</strong></a>
								</div>
							</div>',
							esc_html__( 'Visits:', 'affiliates-for-woocommerce' ),
							esc_url( admin_url( "admin.php?page={$page}&menu=visits&affiliate-id={$user_id}" ) ),
							esc_html( $visits_count ),
							esc_html__( 'Conv:', 'affiliates-for-woocommerce' ),
							esc_url( admin_url( "admin.php?page={$page}&menu=visits&show=converted&affiliate-id={$user_id}" ) ),
							esc_html( $conversion_rate )
						),
						'registered'      => $registered_date,
						'status'          => $affiliate_status_html,
					];
				}
			}

			return apply_filters( 'ddwcaf_affiliates_list_data', $data );
		}

		/**
		 * Process bulk actions
		 *
		 * @return void
		 */
		public function process_bulk_action() {
			if ( ! empty( $_GET[ 'ddwcaf_nonce' ] ) && wp_unslash( $_GET[ 'ddwcaf_nonce' ] ) ) { // WPCS: CSRF ok. // WPCS: input var ok. // WPCS: sanitization ok.
				$nonce = sanitize_text_field( wp_unslash( $_GET[ 'ddwcaf_nonce' ] ) );
				if ( wp_verify_nonce( $nonce, 'ddwcaf_nonce_action' ) ) {
					$action = $this->current_action();

					if ( in_array( $action, [ 'delete', 'pending', 'approved', 'rejected', 'banned' ] ) ) {
						if ( ! empty( $_GET[ 'id' ] ) ) { // WPCS: input var ok.
							if ( is_array( $_GET[ 'id' ] ) ) { // WPCS: input var ok.
								$ids = array_map( 'sanitize_text_field', wp_unslash( $_GET[ 'id' ] ) ); // WPCS: input var ok.

								$success = $error = 0;

                                foreach ( $ids as $id ) {
                                    if ( 'delete' === $action ) {
                                        $response = wp_delete_user( $id );
                                    } else {
                                        $this->affiliate_helper->ddwcaf_update_affiliate_status( $id, $action );
                                        $response = true;
                                    }

                                    if ( $response ) {
                                        $success++;
                                    } else {
                                        $error++;
                                    }
                                }

                                if ( $success ) {
                                    if ( 'delete' === $action ) {
                                        $message = sprintf( esc_html__( '%d affiliate(s) deleted successfully.', 'affiliates-for-woocommerce' ), $success );
                                    } else {
                                        $message = sprintf( esc_html__( 'Status changed for %d affiliate(s) successfully.', 'affiliates-for-woocommerce' ), $success );
                                    }

                                    $this->ddwcaf_print_notification( $message );
                                }

                                if ( $error ) {
                                    $message = sprintf( esc_html__( '%d affiliate(s) not exists.', 'affiliates-for-woocommerce' ), $error );
                                    $this->ddwcaf_print_notification( $message, 'error' );
                                }
							}
						} else {
							$message = esc_html__( 'Select affiliate(s) to delete.', 'affiliates-for-woocommerce' );
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
		 * Process row actions
		 *
		 * @return void
		 */
		public function process_row_action() {
			if ( ! empty( $_GET[ 'ddwcaf_nonce' ] ) && wp_unslash( $_GET[ 'ddwcaf_nonce' ] ) ) { // WPCS: CSRF ok. // WPCS: input var ok. // WPCS: sanitization ok.
				$nonce = sanitize_text_field( wp_unslash( $_GET[ 'ddwcaf_nonce' ] ) );
				if ( wp_verify_nonce( $nonce, 'ddwcaf_nonce_action' ) ) {

					$action = $this->current_action();

					if ( in_array( $action, [ 'delete' ] ) ) {
						if ( ! empty( $_GET[ 'id' ] ) && ! is_array( $_GET[ 'id' ] ) ) { // WPCS: input var ok.
							$id       = intval( wp_unslash( $_GET[ 'id' ] ) );  // WPCS: input var ok.
							$response = wp_delete_user( $id );

							if ( $response ) {
								$message = esc_html__( 'Affiliate is deleted successfully.', 'affiliates-for-woocommerce' );
								$this->ddwcaf_print_notification( $message );
							} else {
								$message = esc_html__( 'Affiliate not exists.', 'affiliates-for-woocommerce' );
								$this->ddwcaf_print_notification( $message, 'error' );
							}
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
			esc_html_e( 'No affiliates avaliable.', 'affiliates-for-woocommerce' );
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
				'cb'              => '<input type="checkbox" />',
				'affiliate'       => esc_html__( 'Affiliate', 'affiliates-for-woocommerce' ),
				'commission_rate' => esc_html__( 'Rate', 'affiliates-for-woocommerce' ),
				'earnings'        => esc_html__( 'Earnings', 'affiliates-for-woocommerce' ),
				'metrics'         => esc_html__( 'Metrics', 'affiliates-for-woocommerce' ),
				'registered'      => esc_html__( 'Registered', 'affiliates-for-woocommerce' ),
				'status'          => esc_html__( 'Status', 'affiliates-for-woocommerce' ),
			];

			return apply_filters( 'ddwcaf_affiliates_list_columns', $columns );
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
			return apply_filters( 'ddwcaf_affiliates_list_sortable_columns', [
				'id'              => [ 'id', true ],
				'affiliate'       => [ 'affiliate_name', true ],
				'commission_rate' => [ 'commission_rate', true ],
				'earnings'        => [ 'earnings', true ],
				'metrics'         => [ 'visits', true ],
				'registered'      => [ 'id', true ],
				'status'          => [ 'status', true ],
			] );
		}

		/**
		 * Render the bulk edit checkbox
		 *
		 * @param array $item Item.
		 * @return string
		 */
		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" name="id[]" value="%d" />', esc_attr( $item[ 'id' ] ) );
		}

		/**
		 * Column actions
		 *
		 * @param array $item Items.
		 * @return array $actions
		 */
		public function column_affiliate( $item ) {
			$search       = ! empty( $_GET[ 's' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 's' ] ) ) : '';
			$current_page = $this->get_pagenum();

			$actions = [
				'id'     => sprintf( 'ID: %s', $item[ 'id' ] ),
				'view'   => sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=' . $_GET[ 'page' ] . "&menu=affiliates&action=view&id=" . $item[ 'id' ] ) ), esc_html__( 'View', 'affiliates-for-woocommerce' ) ),
				'delete' => sprintf( '<a href="%s">%s</a>', wp_nonce_url( 'admin.php?page=' . $_GET[ 'page' ] . "&menu=affiliates&action=delete&s=$search&paged=$current_page&id=" . $item[ 'id' ], 'ddwcaf_nonce_action', 'ddwcaf_nonce' ), esc_html__( 'Delete', 'affiliates-for-woocommerce' ) ),
			];

			return sprintf( '%1$s %2$s', $item[ 'affiliate' ], $this->row_actions( apply_filters( 'ddwcaf_affiliates_list_line_actions', $actions ) ) );
		}

		/**
		 * Bulk actions on list.
		 * 
		 * @return array
		 */
		public function get_bulk_actions() {
			return apply_filters( 'ddwcaf_modify_bulk_actions_in_affiliates_list', [
				'delete'   => esc_html__( 'Delete', 'affiliates-for-woocommerce' ),
				'pending'  => esc_html__( 'Change Affiliate Status to Pending', 'affiliates-for-woocommerce' ),
				'approved' => esc_html__( 'Approve Affiliate', 'affiliates-for-woocommerce' ),
				'rejected' => esc_html__( 'Reject Affiliate', 'affiliates-for-woocommerce' ),
				'banned'   => esc_html__( 'Ban Affiliate', 'affiliates-for-woocommerce' ),
			] );
		}

		/**
		 * Filters function
		 *
		 * @param string $which Position of filter.
		 */
		public function extra_tablenav( $which ) {
			if ( 'top' === $which ) {
				$page = ! empty( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
				$menu = ! empty( $_GET['menu'] ) ? sanitize_text_field( wp_unslash( $_GET['menu'] ) ) : '';
				?>
				<div class="alignleft actions bulkactions">
					<select name="affiliate_status" class="regular-text">
						<option value=""><?php esc_html_e( 'All Status', 'affiliates-for-woocommerce' ); ?></option>
						<option value="pending" <?php echo esc_attr( 'pending' === $this->affiliate_status ? 'selected="selected"' : '' ); ?>><?php esc_html_e( 'Pending', 'affiliates-for-woocommerce' ); ?></option>
						<option value="approved" <?php echo esc_attr( 'approved' === $this->affiliate_status ? 'selected="selected"' : '' ); ?>><?php esc_html_e( 'Approved', 'affiliates-for-woocommerce' ); ?></option>
						<option value="rejected" <?php echo esc_attr( 'rejected' === $this->affiliate_status ? 'selected="selected"' : '' ); ?>><?php esc_html_e( 'Rejected', 'affiliates-for-woocommerce' ); ?></option>
						<option value="banned" <?php echo esc_attr( 'banned' === $this->affiliate_status ? 'selected="selected"' : '' ); ?>><?php esc_html_e( 'Banned', 'affiliates-for-woocommerce' ); ?></option>
					</select>

					<input type="submit" value="<?php esc_attr_e( 'Filter', 'affiliates-for-woocommerce' ); ?>" name="ddwcaf_filter_submit" class="button" />

					<?php
					if ( ! empty( $_GET[ 'ddwcaf_filter_submit' ] ) ) {
						?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $page . '&menu=' . $menu ) ); ?>" class="button"><?php esc_html_e( 'Reset', 'affiliates-for-woocommerce' ); ?></a>
						<?php
					}
					?>
				</div>
				<?php
			}
		}
	}
}
