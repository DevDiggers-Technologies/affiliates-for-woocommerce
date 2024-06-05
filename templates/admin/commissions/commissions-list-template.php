<?php
/**
 * Commissions List Template
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Commissions;

use DDWCAffiliates\Helper\Error\DDWCAF_Error_Helper;
use DDWCAffiliates\Helper\Commission\DDWCAF_Commission_Helper;
use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;
use DDWCAffiliates\Helper\Payout\DDWCAF_Payout_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Commissions_List_Template' ) ) {
	/**
	 * Commissions list class
	 */
	class DDWCAF_Commissions_List_Template extends \WP_List_table {
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
         * Commission Helper Variable
         *
         * @var object
         */
        protected $commission_helper;

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
            $this->commission_helper    = new DDWCAF_Commission_Helper( $ddwcaf_configuration );
			$this->affiliate_helper     = new DDWCAF_Affiliate_Helper( $ddwcaf_configuration );

            parent::__construct( [
				'singular' => esc_html__( 'Commission List', 'affiliates-for-woocommerce' ),
				'plural'   => esc_html__( 'Commissions List', 'affiliates-for-woocommerce' ),
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

            $per_page     = $this->get_items_per_page( 'ddwcaf_commissions_per_page', 20 );
            $current_page = $this->get_pagenum();
            $offset       = ( $current_page - 1 ) * $per_page;

            $this->args = [
                'search'       => ! empty( $_GET[ 's' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 's' ] ) ) : '',
                'show'         => ! empty( $_GET[ 'show' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'show' ] ) ) : '',
                'affiliate_id' => ! empty( $_GET[ 'affiliate-id' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'affiliate-id' ] ) ) : '',
                'payout_id'    => ! empty( $_GET[ 'payout-id' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'payout-id' ] ) ) : '',
                'product_id'   => ! empty( $_GET[ 'product-id' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'product-id' ] ) ) : '',
                'from_date'    => ! empty( $_GET[ 'from-date' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'from-date' ] ) ) : '',
                'end_date'     => ! empty( $_GET[ 'end-date' ] ) ? sanitize_text_field( wp_unslash ( $_GET[ 'end-date' ] ) ) : '',
                'per_page'     => $per_page,
                'offset'       => $offset,
            ];

            $total_items = $this->commission_helper->ddwcaf_get_commissions_count( $this->args );
            $commissions = $this->commission_helper->ddwcaf_get_commissions( $this->args );

			$this->set_pagination_args( [
				'total_items' => intval( $total_items ),
				'per_page'    => $per_page,
			] );

			$data = $this->ddwcaf_prepare_data( $commissions );

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
		 * @param array $commissions
		 * @return array $data
		 */
		public function ddwcaf_prepare_data( $commissions ) {
            $data = [];

            if ( ! empty( $commissions ) ) {
				$date_format = get_option( 'date_format' );
				$time_format = get_option( 'time_format' );

                foreach ( $commissions as $commission ) {
                    $affiliate_id   = $commission[ 'affiliate_id' ];
                    $affiliate_data = get_userdata( $affiliate_id );
                    $order          = wc_get_order( $commission[ 'order_id' ] );
                    $order_currency = $order->get_currency();
                    $product        = wc_get_product( $commission[ 'product_id' ] );
                    $buyer          = '';

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

                    ob_start();
					?>
					<mark class="ddwcaf-status ddwcaf-commission-status-<?php echo esc_attr( $commission[ 'status' ] ); ?>"><span><?php echo esc_html( $this->commission_helper->ddwcaf_get_translation( $commission[ 'status' ] ) ); ?></span></mark>
					<?php
					$status_html = ob_get_clean();

                    if ( ! empty( $commission[ 'updated_at' ] ) && strtotime( $commission[ 'updated_at' ] ) > strtotime( $commission[ 'created_at' ] ) ) {
                        $date = sprintf( esc_html__( 'Last Updated %s', 'affiliates-for-woocommerce' ), date_i18n( "{$date_format} {$time_format}", strtotime( $commission[ 'updated_at' ] ) ) );
                    } else {
                        $date = sprintf( esc_html__( 'Created %s', 'affiliates-for-woocommerce' ), date_i18n( "{$date_format} {$time_format}", strtotime( $commission[ 'created_at' ] ) ) );
                    }

                    $data[] = [
                        'id'                => $commission[ 'id' ],
                        'affiliate_details' => '<a href="' . esc_url( admin_url( "admin.php?page=affiliates-for-woocommerce&action=view&ddwcaf-id={$affiliate_id}" ) ) . '">' . esc_html( "(#{$affiliate_id}) {$affiliate_data->user_login} <{$affiliate_data->user_email}>" ) . '</a>',
                        'order'             => '<a href="#" class="order-preview" data-order-id="' . absint( $order->get_id() ) . '" title="' . esc_attr( __( 'Preview', 'affiliates-for-woocommerce' ) ) . '">' . esc_html__( 'Preview', 'affiliates-for-woocommerce' ) . '</a><a href="' . esc_url( admin_url( 'post.php?post=' . absint( $order->get_id() ) ) . '&action=edit' ) . '"><strong>#' . esc_attr( $order->get_order_number() ) . ' ' . esc_html( $buyer ) . '</strong></a>',
                        'product'           => '<a href="' . esc_url( $product->get_permalink() ) . '" target="_blank">' . wp_kses_post( $product->get_image( 'thumbnail' ) ) . '<div>' . esc_html( $product->get_name() ) . '<br />x ' . esc_html( $commission[ 'quantity' ] ) . '</div></a>',
                        'total'             => wc_price( $commission[ 'line_total' ], [ 'currency' => $order_currency ] ),
                        'refund'            => wc_price( $commission[ 'refund' ], [ 'currency' => $order_currency ] ),
                        'commission'        => '<strong>' . wc_price( $commission[ 'commission' ], [ 'currency' => $order_currency ] ) . '</strong>',
                        'status'            => $status_html,
                        'date'              => $date,
					];
                }
            }

			return apply_filters( 'ddwcaf_commissions_list_data', $data );
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

					if ( in_array( $action, [ 'delete', 'pending', 'paid', 'not_confirmed', 'cancelled', 'refunded' ] ) || strpos( $action, 'payout_' ) !== false ) {
						if ( ! empty( $_GET[ 'ddwcaf-id' ] ) ) { // WPCS: input var ok.
							if ( is_array( $_GET[ 'ddwcaf-id' ] ) ) { // WPCS: input var ok.
								$ids     = array_map( 'sanitize_text_field', wp_unslash( $_GET[ 'ddwcaf-id' ] ) );  // WPCS: input var ok.
								$success = $error = 0;

                                if ( strpos( $action, 'payout_' ) !== false ) {
                                    $payout_helper = new DDWCAF_Payout_Helper( $this->ddwcaf_configuration );
                                    $response      = $payout_helper->ddwcaf_send_commissions_payout( $ids, str_replace( 'payout_', '', $action ) );
                                    $success       = $response[ 'success' ];
                                    $error         = $response[ 'error' ];
                                } else {
                                    foreach ( $ids as $id ) {
                                        if ( 'delete' === $action ) {
                                            $response = $this->commission_helper->ddwcaf_delete_commission( $id );
                                        } else {
                                            $response = $this->commission_helper->ddwcaf_update_commission_status( $id, $action );
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
                                        $message = sprintf( esc_html__( '%d commission(s) deleted successfully.', 'affiliates-for-woocommerce' ), $success );
                                    } elseif ( strpos( $action, 'payout_' ) !== false ) {
                                        $message = sprintf( esc_html__( 'Payout is processed successfully for %d commission(s).', 'affiliates-for-woocommerce' ), $success );
                                    } else {
                                        $message = sprintf( esc_html__( '%d commission(s) status changed successfully.', 'affiliates-for-woocommerce' ), $success );
                                    }

                                    $this->ddwcaf_print_notification( $message );
                                }

                                if ( $error ) {
                                    if ( strpos( $action, 'payout_' ) !== false ) {
                                        $message = sprintf( esc_html__( '%d commission(s) could not be paid.', 'affiliates-for-woocommerce' ), $error );
                                    } else {
                                        $message = sprintf( esc_html__( '%d commission(s) not exits.', 'affiliates-for-woocommerce' ), $error );
                                    }

                                    $this->ddwcaf_print_notification( $message, 'error' );
                                }
							}
						} else {
							$message = esc_html__( 'Select commission(s) to delete.', 'affiliates-for-woocommerce' );
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
			esc_html_e( 'No commissions avaliable.', 'affiliates-for-woocommerce' );
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
				'product'           => esc_html__( 'Product', 'affiliates-for-woocommerce' ),
				'total'             => esc_html__( 'Total', 'affiliates-for-woocommerce' ),
				'refund'            => esc_html__( 'Refund', 'affiliates-for-woocommerce' ),
				'commission'        => esc_html__( 'Commision', 'affiliates-for-woocommerce' ),
				'status'            => esc_html__( 'Status', 'affiliates-for-woocommerce' ),
				'date'              => esc_html__( 'Date', 'affiliates-for-woocommerce' ),
			];

			return apply_filters( 'ddwcaf_commissions_list_columns', $columns );
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
			return apply_filters( 'ddwcaf_commissions_list_sortable_columns', [
				'id'                => [ 'id', true ],
				'affiliate_details' => [ 'affiliate_details', true ],
				'order'             => [ 'order', true ],
				'product'           => [ 'product', true ],
				'total'             => [ 'total', true ],
				'refund'            => [ 'refund', true ],
				'commission'        => [ 'commission', true ],
				'status'            => [ 'status', true ],
				'date'              => [ 'date', true ],
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
            $bulk_actions = [
                'pending'       => esc_html__( 'Change Status to Pending', 'affiliates-for-woocommerce' ),
                'not_confirmed' => esc_html__( 'Change Status to Not Confirmed', 'affiliates-for-woocommerce' ),
                'cancelled'     => esc_html__( 'Change Status to Cancelled', 'affiliates-for-woocommerce' ),
                'refunded'      => esc_html__( 'Change Status to Refunded', 'affiliates-for-woocommerce' ),
                'paid'          => esc_html__( 'Change Status to Paid', 'affiliates-for-woocommerce' ),
			];

            foreach ( $this->ddwcaf_configuration[ 'withdrawal_methods' ] as $key => $withdrawal_method ) {
                if ( ! empty( $withdrawal_method[ 'available' ] ) && ! empty( $withdrawal_method[ 'status' ] ) ) {
                    $bulk_actions[ 'payout_' . $key ] = sprintf( esc_html__( 'Pay via %s', 'affiliates-for-woocommerce' ), $this->affiliate_helper->ddwcaf_get_withdrawal_method_name( $key ) );
                }
            }

            $bulk_actions[ 'payout_default' ] = esc_html__( 'Pay to Affiliate Default Withdrawal Method', 'affiliates-for-woocommerce' );
            $bulk_actions[ 'delete' ]         = esc_html__( 'Delete', 'affiliates-for-woocommerce' );

            return apply_filters( 'ddwcaf_modify_bulk_actions_in_commissions_list', $bulk_actions );
		}

        /**
		 * Filters function
		 *
		 * @param string $which Position of filter.
		 */
		public function extra_tablenav( $which ) {
			if ( 'top' === $which ) {
                $affiliate_id = $this->args[ 'affiliate_id' ];
                $product_id   = $this->args[ 'product_id' ];
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

                <select class="regular-text ddwcaf-products" name="product-id" data-placeholder="<?php esc_attr_e( 'Select Product', 'affiliates-for-woocommerce' ); ?>">
                    <?php
                    if ( ! empty( $product_id ) ) {
                        $product = wc_get_product( $product_id );
                        ?>
                        <option value="<?php echo esc_attr( $product_id ); ?>" selected="selected"><?php echo esc_html( $product->get_name() ); ?></option>
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
