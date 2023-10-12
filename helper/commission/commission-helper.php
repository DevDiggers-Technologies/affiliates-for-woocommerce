<?php
/**
 * Commission helper
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Helper\Commission;

use DDWCAffiliates\Helper\Visit\DDWCAF_Visit_Helper;
use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Commission_Helper' ) ) {
	/**
	 * Commission helper class
	 */
	class DDWCAF_Commission_Helper {
		/**
		 * Database object
		 *
		 * @var object
		 */
		protected $wpdb;

        /**
         * Configuration Variable
         *
         * @var array
         */
        protected $ddwcaf_configuration;

		/**
		 * Construct
		 */
		public function __construct( $ddwcaf_configuration ) {
			global $wpdb;
			$this->wpdb                 = $wpdb;
			$this->ddwcaf_configuration = $ddwcaf_configuration;
		}

		/**
         * Save Commission function
         *
         * @param array $args
         * @return int
         */
        public function ddwcaf_save_commission( $args ) {
            $default_args = [
				'affiliate_id' => 0,
				'order_id'     => 0,
				'line_item_id' => 0,
				'product_id'   => 0,
				'quantity'     => 0,
				'line_total'   => 0,
				'commission'   => 0,
				'refund'       => 0,
				'status'       => 'pending',
				'created_at'   => current_time( 'Y-m-d H:i:s' ),
				'updated_at'   => current_time( 'Y-m-d H:i:s' ),
			];

			$args = wp_parse_args( $args, $default_args );

            if ( ! empty( $args[ 'id' ] ) ) {
                $id = $args[ 'id' ];
                unset( $args[ 'id' ] );
				unset( $args[ 'created_at' ] );

                $this->wpdb->update(
                    $this->wpdb->ddwcaf_commissions,
                    $args,
                    [ 'id' => $id ],
                    [ '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s' ],
                    [ '%d' ]
                );

                return $id;
            } else {
                $this->wpdb->insert(
                    $this->wpdb->ddwcaf_commissions,
                    $args,
                    [ '%d', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ],
                );

                return $this->wpdb->insert_id;
            }
        }

		/**
         * Update Commission status function
         *
         * @param int $id
         * @param string $status
         * @return int
         */
        public function ddwcaf_update_commission_status( $id, $status ) {
			$commission = $this->ddwcaf_get_commission_by_id( $id );

			if ( ! $commission ) {
				return false;
			}

			return $this->wpdb->update(
				$this->wpdb->ddwcaf_commissions,
				[
					'status'     => $status,
					'updated_at' => current_time( 'Y-m-d H:i:s' ),
				],
				[ 'id' => $id ],
				[ '%s', '%s' ],
				[ '%d' ]
			);
        }

		/**
         * Get commission by id function
         *
		 * @param int $id
         * @return array
         */
        public function ddwcaf_get_commission_by_id( $id ) {
            return $this->wpdb->get_row( $this->wpdb->prepare( "SELECT * FROM {$this->wpdb->ddwcaf_commissions} WHERE id=%d", $id ), ARRAY_A );
        }

        /**
         * Prepare where conditions for commissions function
         *
		 * @param array $args
         * @return string
         */
        public function ddwcaf_prepare_where_conditions_for_commissions( $args ) {
            extract( $args );
            $conditions = '';

			if ( ! empty( $affiliate_id ) ) {
				$conditions .= $this->wpdb->prepare( " AND commissions.affiliate_id=%d", $affiliate_id );
			}
			if ( ! empty( $from_date ) ) {
				$from_date   = str_replace( 'T', ' ', $from_date );
				$conditions .= $this->wpdb->prepare( " AND commissions.created_at>=%s", $from_date );
			}
			if ( ! empty( $end_date ) ) {
				$end_date   = str_replace( 'T', ' ', $end_date );
				$conditions .= $this->wpdb->prepare( " AND commissions.created_at<=%s", $end_date );
			}
			if ( ! empty( $search ) ) {
				$conditions .= $this->wpdb->prepare( " AND commissions.id LIKE %s", '%' . $search . '%' );
			}
            if ( ! empty( $show ) ) {
				$conditions .= $this->wpdb->prepare( " AND commissions.status=%s", $show );
			}
			if ( ! empty( $product_id ) ) {
				$conditions .= $this->wpdb->prepare( " AND commissions.product_id=%d", $product_id );
			}
			if ( ! empty( $order_id ) ) {
				$conditions .= $this->wpdb->prepare( " AND commissions.order_id=%d", $order_id );
			}
			if ( ! empty( $payout_id ) ) {
				$conditions .= $this->wpdb->prepare( " AND commissions.id IN (SELECT commission_id FROM {$this->wpdb->ddwcaf_commissionsmeta} WHERE meta_key=%s AND meta_value=%s)", '_ddwcaf_payout_id', $payout_id );
			}

            return $conditions;
        }

        /**
         * Get commissions function
         *
		 * @param array $args
         * @return array
         */
        public function ddwcaf_get_commissions( $args ) {
            extract( $args );

            $conditions = $this->ddwcaf_prepare_where_conditions_for_commissions( $args );
            $response   = $this->wpdb->get_results( $this->wpdb->prepare( "SELECT DISTINCT commissions.* FROM {$this->wpdb->ddwcaf_commissions} as commissions LEFT JOIN {$this->wpdb->users} as users ON commissions.affiliate_id=users.ID WHERE 1=1 $conditions ORDER BY id DESC LIMIT %d OFFSET %d", $per_page, $offset ), ARRAY_A );

			return apply_filters( 'ddwcaf_modify_commissions_response', $response, $args );
        }

        /**
         * Get all commissions count function
         *
		 * @param array $args
         * @return int
         */
        public function ddwcaf_get_commissions_count( $args ) {
            $conditions = $this->ddwcaf_prepare_where_conditions_for_commissions( $args );
            $response   = $this->wpdb->get_var( "SELECT count(DISTINCT commissions.id) FROM {$this->wpdb->ddwcaf_commissions} as commissions LEFT JOIN {$this->wpdb->users} as users ON commissions.affiliate_id=users.ID WHERE 1=1 $conditions" );

			return apply_filters( 'ddwcaf_modify_commissions_count', $response, $args );
        }

		/**
         * Prepare where conditions for top products function
         *
		 * @param array $args
         * @return string
         */
        public function ddwcaf_prepare_where_conditions_for_top_products( $args ) {
            extract( $args );
            $conditions = '';

			if ( ! empty( $affiliate_id ) ) {
				$conditions .= $this->wpdb->prepare( " AND commissions.affiliate_id=%d", $affiliate_id );
			}

            return $conditions;
        }

		/**
         * Get top products function
         *
		 * @param array $args
         * @return array
         */
        public function ddwcaf_get_top_products( $args ) {
            extract( $args );

            $conditions = $this->ddwcaf_prepare_where_conditions_for_top_products( $args );
            $response   = $this->wpdb->get_results( $this->wpdb->prepare( "SELECT DISTINCT commissions.product_id as product, SUM( CASE WHEN quantity IS NOT NULL THEN quantity ELSE 0 END ) AS quantity, SUM( CASE WHEN line_total IS NOT NULL THEN line_total ELSE 0 END ) AS earnings, SUM( CASE WHEN commission IS NOT NULL THEN commission ELSE 0 END ) AS commission FROM {$this->wpdb->ddwcaf_commissions} as commissions LEFT JOIN {$this->wpdb->users} as users ON commissions.affiliate_id=users.ID WHERE 1=1 $conditions GROUP BY product_id ORDER BY $orderby $order LIMIT %d OFFSET %d", $per_page, $offset ), ARRAY_A );

			return apply_filters( 'ddwcaf_modify_top_products_response', $response, $args );
        }

		/**
         * Get all top products count function
         *
		 * @param array $args
         * @return int
         */
        public function ddwcaf_get_top_products_count( $args ) {
            $conditions = $this->ddwcaf_prepare_where_conditions_for_top_products( $args );
            $response   = $this->wpdb->get_var( "SELECT count(DISTINCT commissions.product_id) FROM {$this->wpdb->ddwcaf_commissions} as commissions LEFT JOIN {$this->wpdb->users} as users ON commissions.affiliate_id=users.ID WHERE 1=1 $conditions ORDER BY quantity DESC" );

			return apply_filters( 'ddwcaf_modify_top_products_count', $response, $args );
        }

		/**
		 * Get commission status by order status function
		 *
		 * @param string $order_status
		 * @return array
		 */
		public function ddwcaf_get_commission_status_by_order_status( $order_status ) {
			$commission_order_status = [
				'pending'         => [ 'completed', 'processing' ],
				'pending_payment' => [],
				'paid'            => [],
				'not_confirmed'   => [ 'pending', 'on-hold' ],
				'cancelled'       => [ 'cancelled', 'failed' ],
				'refunded'        => [ 'refunded' ],
			];

			foreach ( $commission_order_status as $commission_status => $mapped_order_statuses ) {
				if ( in_array( $order_status, $mapped_order_statuses, true ) ) {
					return apply_filters( 'ddwcaf_map_commission_status', $commission_status, $order_status );
				}
			}

			return apply_filters( 'ddwcaf_default_commission_status', 'pending', $order_status );
		}

		/**
		 * Calculate commission amount function
		 *
		 * @param float $amount
		 * @param int $affiliate_id
		 * @return float
		 */
		public function ddwcaf_calculate_commission_amount( $amount, $affiliate_id ) {
			$commission_rate   = $this->ddwcaf_get_affiliate_commission_rate( $affiliate_id );
			$commission_amount = floatval( $commission_rate * $amount / 100 );

			return wc_format_decimal( ( $commission_amount >= $amount ? $amount : $commission_amount ), wc_get_price_decimals() );
		}

		/**
		 * Save commission for order function
		 *
		 * @param object $order
		 * @param int $affiliate_id
		 * @return boolean
		 */
		public function ddwcaf_save_commission_for_order( $order, $affiliate_id ) {
			$order_id          = $order->get_id();
			$commission_status = $this->ddwcaf_get_commission_status_by_order_status( $order->get_status() );
			$items             = $order->get_items();
			$flag              = false;

			if ( ! empty( $items ) ) {
				foreach ( $items as $item_id => $item ) {
					$product_id   = $item->get_product_id();
					$variation_id = $item->get_variation_id();

					// retrieves current product id.
					$product_id = $variation_id ? $variation_id : $product_id;

					$flag = true;

					// choose method to retrieve item total.
					$get_item_amount = ! empty( $this->ddwcaf_configuration[ 'exclude_discounts_enabled' ] ) ? 'get_line_total' : 'get_line_subtotal';
					$item_amount     = (float) $order->$get_item_amount( $item, empty( $this->ddwcaf_configuration[ 'exclude_taxes_enabled' ] ), false );
					$line_total      = abs( $item_amount );

					$commission_amount = $this->ddwcaf_calculate_commission_amount( $line_total, $affiliate_id );

					$commission_args = [
						'order_id'     => $order_id,
						'line_item_id' => $item_id,
						'product_id'   => $product_id,
						'quantity'     => $item->get_quantity(),
						'affiliate_id' => $affiliate_id,
						'line_total'   => $line_total,
						'commission'   => $commission_amount,
						'refund'       => '',
						'status'       => $commission_status,
					];

					$commission_id = intval( $item->get_meta( '_ddwcaf_commission_id' ) );

					if ( $commission_id ) {
						$commission_args[ 'id' ] = $commission_id;
					}

					$commission_id = $this->ddwcaf_save_commission( $commission_args );
					$item->update_meta_data( '_ddwcaf_commission_id', $commission_id );
				}
			}

			return $flag;
		}

        /**
         * Get affiliate statitics function
         *
         * @param int $user_id
         * @return array
         */
        public function ddwcaf_get_affiliate_statistics( $user_id ) {
			return $this->wpdb->get_row( $this->wpdb->prepare( "SELECT ROUND( SUM( CASE WHEN status!='cancelled' THEN commission ELSE 0 END ), 2 ) AS total_earnings, ROUND( SUM( CASE WHEN status='paid' THEN commission ELSE 0 END ), 2 ) AS paid_earnings, ROUND( SUM( CASE WHEN status='pending' THEN commission ELSE 0 END ), 2 ) AS unpaid_earnings FROM {$this->wpdb->ddwcaf_commissions} WHERE affiliate_id=%d", $user_id ), ARRAY_A );
        }

        /**
		 * Get affiliate commission rate function
		 *
		 * @param int $user_id
		 * @return float
		 */
		public function ddwcaf_get_affiliate_commission_rate( $user_id ) {
			if ( empty( $user_id ) ) {
				$user_id = get_current_user_id();
			}

			return wc_format_decimal( floatval( $this->ddwcaf_configuration[ 'default_commission_rate' ] ), wc_get_price_decimals() );
		}

		/**
         * Update commission meta function
         *
         * @param int $commission_id
         * @param string $meta_key
         * @param string $meta_value
         * @return int
         */
		public function ddwcaf_update_commission_meta( $commission_id, $meta_key, $meta_value ) {
			$id = $this->wpdb->get_var( $this->wpdb->prepare( "SELECT id FROM {$this->wpdb->ddwcaf_commissionsmeta} WHERE commission_id=%d AND meta_key=%s", $commission_id, $meta_key ) );

			$meta_value = is_array( $meta_value ) ? maybe_serialize( $meta_value ) : $meta_value;

			if ( $id ) {
				$this->wpdb->update(
                    $this->wpdb->ddwcaf_commissionsmeta,
                    [
						'commission_id' => $commission_id,
						'meta_key'      => $meta_key,
						'meta_value'    => $meta_value,
					],
                    [ 'id' => $id ],
                    [ '%d', '%s', '%s' ],
                    [ '%d' ]
                );

				return $id;
			} else {
				$this->wpdb->insert(
                    $this->wpdb->ddwcaf_commissionsmeta,
                    [
						'commission_id' => $commission_id,
						'meta_key'      => $meta_key,
						'meta_value'    => $meta_value,
					],
                    [ '%d', '%s', '%s' ],
                );

				return $this->wpdb->insert_id;
			}
		}

		/**
         * Get commission meta function
         *
         * @param int $commission_id
         * @param string $meta_key
         * @return mixed
         */
		public function ddwcaf_get_commission_meta( $commission_id, $meta_key ) {
			$meta_value = $this->wpdb->get_var( $this->wpdb->prepare( "SELECT meta_value FROM {$this->wpdb->ddwcaf_commissionsmeta} WHERE commission_id=%d AND meta_key=%s", $commission_id, $meta_key ) );

			return ! empty( $meta_value ) ? maybe_unserialize( $meta_value ) : '';
		}

		/**
         * Get translation function
         *
         * @param string $word
         * @return string
         */
		public function ddwcaf_get_translation( $word ) {
			$translation = [
				'pending'         => esc_html__( 'Pending', 'affiliates-for-woocommerce' ),
				'pending_payment' => esc_html__( 'Pending Payment', 'affiliates-for-woocommerce' ),
				'paid'            => esc_html__( 'Paid', 'affiliates-for-woocommerce' ),
				'not_confirmed'   => esc_html__( 'Not Confirmed', 'affiliates-for-woocommerce' ),
				'cancelled'       => esc_html__( 'Cancelled', 'affiliates-for-woocommerce' ),
				'refunded'        => esc_html__( 'Refunded', 'affiliates-for-woocommerce' ),
			];

            return ! empty( $translation[ $word ] ) ? $translation[ $word ] : $word;
		}

        /**
		 * Delete commission function
		 * 
		 * @param int $id
		 * @return int|bool
		 */
        public function ddwcaf_delete_commission( $id ) {
            return $this->wpdb->delete(
				$this->wpdb->ddwcaf_commissions,
				[
					'id' => $id
				],
                [ '%d' ]
            );
        }
	}
}
