<?php
/**
 * Payouts List Template
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Payouts;

use DDWCAffiliates\Helper\Error\DDWCAF_Error_Helper;
use DDWCAffiliates\Helper\Payout\DDWCAF_Payout_Helper;
use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Payouts_List_Template' ) ) {
	/**
	 * Payouts list class
	 */
	class DDWCAF_Payouts_List_Template extends \WP_List_table {
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
         * Payout Helper Variable
         *
         * @var object
         */
        protected $payout_helper;

		/**
         * Affiliate Helper Variable
         *
         * @var object
         */
        protected $affiliate_helper;

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
            $this->payout_helper        = new DDWCAF_Payout_Helper( $ddwcaf_configuration );
            $this->affiliate_helper     = new DDWCAF_Affiliate_Helper( $ddwcaf_configuration );

            parent::__construct( [
				'singular' => esc_html__( 'Payout List', 'affiliates-for-woocommerce' ),
				'plural'   => esc_html__( 'Payouts List', 'affiliates-for-woocommerce' ),
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

            $per_page     = $this->get_items_per_page( 'ddwcaf_payouts_per_page', 20 );
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

            $total_items = $this->payout_helper->ddwcaf_get_payouts_count( $this->args );
            $payouts     = $this->payout_helper->ddwcaf_get_payouts( $this->args );

			$this->set_pagination_args( [
				'total_items' => intval( $total_items ),
				'per_page'    => $per_page,
			] );

			$data = $this->ddwcaf_prepare_data( $payouts );

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
		 * @param array $payouts
		 * @return array $data
		 */
		public function ddwcaf_prepare_data( $payouts ) {
            $data = [];

            if ( ! empty( $payouts ) ) {
				$date_format = get_option( 'date_format' );
				$time_format = get_option( 'time_format' );

                foreach ( $payouts as $payout ) {
                    $affiliate_id   = $payout[ 'affiliate_id' ];
                    $affiliate_data = get_userdata( $affiliate_id );

                    ob_start();
                    ?>
                    <p><strong><?php echo esc_html( $this->affiliate_helper->ddwcaf_get_withdrawal_method_name( $payout[ 'payment_method' ] ) ); ?></strong></p>
                    <small>
                        <?php
						$payment_info = maybe_unserialize( $payout[ 'payment_info' ] );
                        if ( 'bacs' === $payout[ 'payment_method' ] ) {
                            echo sprintf( esc_html__( 'Account Name: %s', 'affiliates-for-woocommerce' ), ! empty( $payment_info[ 'account_name' ] ) ? $payment_info[ 'account_name' ] : '' ) . '<br />';
                            echo sprintf( esc_html__( 'IBAN: %s', 'affiliates-for-woocommerce' ), ! empty( $payment_info[ 'iban' ] ) ? $payment_info[ 'iban' ] : '' ) . '<br />';
                            echo sprintf( esc_html__( 'Swift Code: %s', 'affiliates-for-woocommerce' ), ! empty( $payment_info[ 'swift_code' ] ) ? $payment_info[ 'swift_code' ] : '' );
                        } else {
                            echo esc_html( ! empty( $payment_info ) ? $payment_info : 'N/A' );
						}
                        ?>
                    </small>
                    <?php
                    $payment_method_html = ob_get_clean();

                    ob_start();
					?>
					<mark class="ddwcaf-status ddwcaf-commission-status-<?php echo esc_attr( $payout[ 'status' ] ); ?>"><span><?php echo esc_html( $this->payout_helper->ddwcaf_get_translation( $payout[ 'status' ] ) ); ?></span></mark>
					<?php
					$status_html = ob_get_clean();

                    $data[] = [
                        'id'                => $payout[ 'id' ],
                        'id_link'           => '<a href="' . esc_url( admin_url( "admin.php?page={$_GET[ 'page' ]}&tab=manage&ddwcaf-id={$payout[ 'id' ]}" ) ) . '">' . esc_html( $payout[ 'id' ] ) . '</a>',
                        'affiliate_details' => '<a href="' . esc_url( admin_url( "admin.php?page=affiliates-for-woocommerce&action=view&ddwcaf-id={$affiliate_id}" ) ) . '">' . esc_html( "(#{$affiliate_id}) {$affiliate_data->user_login} <{$affiliate_data->user_email}>" ) . '</a>',
                        'payment_method'    => $payment_method_html,
                        'amount'            => wc_price( $payout[ 'amount' ] ),
                        'reference'         => $this->payout_helper->ddwcaf_get_references( $payout[ 'reference' ] ),
                        'status'            => $status_html,
                        'created_at'        => date_i18n( "{$date_format} {$time_format}", strtotime( $payout[ 'created_at' ] ) ),
                        'completed_at'      => ! empty( $payout[ 'completed_at' ] ) ? date_i18n( "{$date_format} {$time_format}", strtotime( $payout[ 'completed_at' ] ) ) : 'N/A',
					];
                }
            }

			return apply_filters( 'ddwcaf_payouts_list_data', $data );
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

					if ( in_array( $action, [ 'delete', 'pending', 'completed', 'cancelled' ] ) || strpos( $action, 'payout_' ) !== false ) {
						if ( ! empty( $_GET[ 'ddwcaf-id' ] ) ) { // WPCS: input var ok.
							if ( is_array( $_GET[ 'ddwcaf-id' ] ) ) { // WPCS: input var ok.
								$ids     = array_map( 'sanitize_text_field', wp_unslash( $_GET[ 'ddwcaf-id' ] ) );  // WPCS: input var ok.
								$success = $error = 0;

                                if ( strpos( $action, 'payout_' ) !== false ) {
                                    $payout_helper = new DDWCAF_Payout_Helper( $this->ddwcaf_configuration );
                                    $response      = $payout_helper->ddwcaf_send_payouts_payout( $ids, str_replace( 'payout_', '', $action ) );
                                    $success       = $response[ 'success' ];
                                    $error         = $response[ 'error' ];
                                } else {
                                    foreach ( $ids as $id ) {
                                        if ( 'delete' === $action ) {
                                            $response = $this->payout_helper->ddwcaf_delete_payout( $id );
                                        } else {
                                            $response = $this->payout_helper->ddwcaf_update_payout_status( $id, $action );
                                        }

                                        if ( $response ) {
                                            $success++;
                                        } else {
                                            $error++;
                                        }
                                    }
                                }

                                if ( $success ) {
                                    if ( 'delete' === $action ) {
                                        $message = sprintf( esc_html__( '%d payout(s) deleted successfully.', 'affiliates-for-woocommerce' ), $success );
                                    } elseif ( strpos( $action, 'payout_' ) !== false ) {
                                        $message = sprintf( esc_html__( 'Payout is processed successfully for %d payout(s).', 'affiliates-for-woocommerce' ), $success );
                                    } else {
                                        $message = sprintf( esc_html__( '%d payout(s) status changed successfully.', 'affiliates-for-woocommerce' ), $success );
                                    }

                                    $this->ddwcaf_print_notification( $message );
                                }

                                if ( $error ) {
                                    if ( strpos( $action, 'payout_' ) !== false ) {
                                        $message = sprintf( esc_html__( '%d payout(s) could not be paid.', 'affiliates-for-woocommerce' ), $error );
                                    } else {
                                        $message = sprintf( esc_html__( '%d payout(s) not exits.', 'affiliates-for-woocommerce' ), $error );
                                    }

                                    $this->ddwcaf_print_notification( $message, 'error' );
                                }
							}
						} else {
							$message = esc_html__( 'Select payout(s) to delete.', 'affiliates-for-woocommerce' );
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
			esc_html_e( 'No payouts avaliable.', 'affiliates-for-woocommerce' );
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
				'id_link'           => esc_html__( 'ID', 'affiliates-for-woocommerce' ),
				'affiliate_details' => esc_html__( 'Affiliate', 'affiliates-for-woocommerce' ),
				'payment_method'    => esc_html__( 'Payment Method', 'affiliates-for-woocommerce' ),
				'amount'            => esc_html__( 'Amount', 'affiliates-for-woocommerce' ),
				'reference'         => esc_html__( 'Reference', 'affiliates-for-woocommerce' ),
				'status'            => esc_html__( 'Status', 'affiliates-for-woocommerce' ),
				'created_at'        => esc_html__( 'Created On', 'affiliates-for-woocommerce' ),
				'completed_at'      => esc_html__( 'Completed On', 'affiliates-for-woocommerce' ),
			];

			return apply_filters( 'ddwcaf_payouts_list_columns', $columns );
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
			return apply_filters( 'ddwcaf_payouts_list_sortable_columns', [
				'id_link'           => [ 'id_link', true ],
				'affiliate_details' => [ 'affiliate_details', true ],
				'amount'            => [ 'amount', true ],
				'reference'         => [ 'reference', true ],
				'status'            => [ 'status', true ],
				'created_at'        => [ 'created_at', true ],
				'completed_at'      => [ 'completed_at', true ],
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
		 * Column actions
		 *
		 * @param array $item Items.
		 * @return array $actions
		 */
		public function column_id_link( $item ) {
			$actions = [
                'view' => sprintf( '<a href="%s">%s</a>', sanitize_url( admin_url( "admin.php?page={$_GET[ 'page' ]}&tab=manage&ddwcaf-id={$item[ 'id' ]}" ) ), esc_html__( 'View', 'affiliates-for-woocommerce' ) ),
			];

			return sprintf( '%1$s %2$s', $item[ 'id_link' ], $this->row_actions( apply_filters( 'ddwcaf_payouts_list_line_actions', $actions ) ) );
		}

		/**
         * Bulk actions on list.
		 * 
		 * @return array
         */
        public function get_bulk_actions() {
            $bulk_actions = [
                'pending'   => esc_html__( 'Change Status to Pending', 'affiliates-for-woocommerce' ),
                'cancelled' => esc_html__( 'Change Status to Cancelled', 'affiliates-for-woocommerce' ),
                'completed' => esc_html__( 'Change Status to Completed', 'affiliates-for-woocommerce' ),
                'delete'    => esc_html__( 'Delete', 'affiliates-for-woocommerce' ),
			];

            return apply_filters( 'ddwcaf_modify_bulk_actions_in_payouts_list', $bulk_actions );
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
                <label for="from-date"><?php esc_html_e( 'From:', 'affiliates-for-woocommerce' ); ?></label>
                <input type="datetime-local" value="<?php echo esc_attr( $this->args[ 'from_date' ] ); ?>" name="from-date" id="from-date" class="ddwcaf-datepicker" placeholder="yyyy-mm-dd" autocomplete="off" />

                <label for="end-date"><?php esc_html_e( 'To:', 'affiliates-for-woocommerce' ); ?></label>
                <input type="datetime-local" value="<?php echo esc_attr( $this->args[ 'end_date' ] ); ?>" name="end-date" id="end-date" class="ddwcaf-datepicker" placeholder="yyyy-mm-dd" autocomplete="off" />

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
				<?php
			}
		}
	}
}
