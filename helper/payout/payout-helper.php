<?php
/**
 * Payout helper
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Helper\Payout;

use DDWCAffiliates\Helper\Error\DDWCAF_Error_Helper;
use DDWCAffiliates\Helper\Visit\DDWCAF_Visit_Helper;
use DDWCAffiliates\Helper\Affiliate\DDWCAF_Affiliate_Helper;
use DDWCAffiliates\Helper\Commission\DDWCAF_Commission_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Payout_Helper' ) ) {
	/**
	 * Payout helper class
	 */
	class DDWCAF_Payout_Helper {
        /**
		 * Error Helper Trait
		 */
		use DDWCAF_Error_Helper;

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
         * Commission helper Variable
         *
         * @var object
         */
        protected $commission_helper;

        /**
         * Affiliate helper Variable
         *
         * @var object
         */
        protected $affiliate_helper;

		/**
		 * Construct
		 */
		public function __construct( $ddwcaf_configuration ) {
			global $wpdb;
			$this->wpdb                 = $wpdb;
			$this->ddwcaf_configuration = $ddwcaf_configuration;
			$this->affiliate_helper     = new DDWCAF_Affiliate_Helper( $ddwcaf_configuration );
			$this->commission_helper    = new DDWCAF_Commission_Helper( $ddwcaf_configuration );
		}

		/**
         * Save Payout function
         *
         * @param array $args
         * @return int
         */
        public function ddwcaf_save_payout( $args ) {
            $default_args = [
				'affiliate_id'   => 0,
				'payment_method' => '',
				'amount'         => 0,
				'transaction_id' => '',
                'reference'      => 'manually_by_admin',
				'status'         => 'pending',
				'payment_info'   => '',
				'created_at'     => current_time( 'Y-m-d H:i:s' ),
				'completed_at'   => NULL,
			];

			$args = wp_parse_args( $args, $default_args );

            if ( ! empty( $args[ 'id' ] ) ) {
                $id = $args[ 'id' ];
                unset( $args[ 'id' ] );
				unset( $args[ 'created_at' ] );

                $this->wpdb->update(
                    $this->wpdb->ddwcaf_payouts,
                    $args,
                    [ 'id' => $id ],
                    [ '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ],
                    [ '%d' ]
                );

                return $id;
            } else {
                $this->wpdb->insert(
                    $this->wpdb->ddwcaf_payouts,
                    $args,
                    [ '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ],
                );

                return $this->wpdb->insert_id;
            }
        }

		/**
         * Get payout by id function
         *
		 * @param int $id
         * @return array
         */
        public function ddwcaf_get_payout_by_id( $id ) {
            return $this->wpdb->get_row( $this->wpdb->prepare( "SELECT * FROM {$this->wpdb->ddwcaf_payouts} WHERE id=%d", $id ), ARRAY_A );
        }

        /**
         * Prepare where conditions for payouts function
         *
		 * @param array $args
         * @return string
         */
        public function ddwcaf_prepare_where_conditions_for_payouts( $args ) {
            extract( $args );
            $conditions = '';

			if ( ! empty( $affiliate_id ) ) {
				$conditions .= $this->wpdb->prepare( " AND payouts.affiliate_id=%d", $affiliate_id );
			}
            if ( ! empty( $from_date ) ) {
				$from_date   = str_replace( 'T', ' ', $from_date );
				$conditions .= $this->wpdb->prepare( " AND payouts.created_at>=%s", $from_date );
			}
			if ( ! empty( $end_date ) ) {
				$end_date   = str_replace( 'T', ' ', $end_date );
				$conditions .= $this->wpdb->prepare( " AND payouts.created_at<=%s", $end_date );
			}
			if ( ! empty( $search ) ) {
				$conditions .= $this->wpdb->prepare( " AND payouts.id LIKE %s", '%' . $search . '%' );
			}
            if ( ! empty( $show ) ) {
				$conditions .= $this->wpdb->prepare( " AND payouts.status=%s", $show );
			}

            return $conditions;
        }

        /**
         * Get payouts function
         *
		 * @param array $args
         * @return array
         */
        public function ddwcaf_get_payouts( $args ) {
            extract( $args );

            $conditions = $this->ddwcaf_prepare_where_conditions_for_payouts( $args );
            $response   = $this->wpdb->get_results( $this->wpdb->prepare( "SELECT DISTINCT payouts.* FROM {$this->wpdb->ddwcaf_payouts} as payouts LEFT JOIN {$this->wpdb->users} as users ON payouts.affiliate_id=users.ID WHERE 1=1 $conditions ORDER BY id DESC LIMIT %d OFFSET %d", $per_page, $offset ), ARRAY_A );

			return apply_filters( 'ddwcaf_modify_payouts_response', $response, $args );
        }

        /**
         * Get all payouts count function
         *
		 * @param array $args
         * @return int
         */
        public function ddwcaf_get_payouts_count( $args ) {
            $conditions = $this->ddwcaf_prepare_where_conditions_for_payouts( $args );
            $response   = $this->wpdb->get_var( "SELECT count(DISTINCT payouts.id) FROM {$this->wpdb->ddwcaf_payouts} as payouts LEFT JOIN {$this->wpdb->users} as users ON payouts.affiliate_id=users.ID WHERE 1=1 $conditions" );

			return apply_filters( 'ddwcaf_modify_payouts_count', $response, $args );
        }

        /**
         * Send commissions payout function
         *
         * @param array $commission_ids
         * @param string $payment_method
         * @return int
         */
        public function ddwcaf_send_commissions_payout( $commission_ids, $payment_method ) {
            $payout_args = [];
            $success     = $error = 0;

            foreach ( $commission_ids as $commission_id ) {
                $commission = $this->commission_helper->ddwcaf_get_commission_by_id( $commission_id );

                if ( ! $commission ) {
                    ++$error;
                    continue;
                }

                if ( 'pending' !== $commission[ 'status' ] ) {
                    ++$error;
                    continue;
                }

                $affiliate_id = $commission[ 'affiliate_id' ];

                if ( ! empty( $payout_args[ $affiliate_id ][ 'args' ] ) ) {
                    $payout_args[ $affiliate_id ][ 'args' ][ 'amount' ] += floatval( $commission[ 'commission' ] );
                } else {
                    if ( 'default' === $payment_method ) {
                        $payment_method = $this->affiliate_helper->ddwcaf_get_affiliate_default_withdrawal_method( $affiliate_id );
                    }

                    $withdrawal_methods = $this->affiliate_helper->ddwcaf_get_affiliate_withdrawal_methods( $affiliate_id );

                    $payout_args[ $affiliate_id ][ 'args' ] = [
                        'affiliate_id'   => $affiliate_id,
                        'payment_method' => $payment_method,
                        'amount'         => floatval( $commission[ 'commission' ] ),
                        'transaction_id' => '',
                        'created_at'     => current_time( 'Y-m-d H:i:s' ),
                        'completed_at'   => NULL,
                        'reference'      => 'manually_by_admin',
                        'status'         => 'pending',
                        'payment_info'   => ! empty( $withdrawal_methods[ $payment_method ] ) ? maybe_serialize( $withdrawal_methods[ $payment_method ] ) : '',
                    ];
                }

                $payout_args[ $affiliate_id ][ 'commission_ids' ][] = $commission_id;
                ++$success;
            }

            foreach ( $payout_args as $affiliate_id => $payout_arg ) {
                $payout_id = $this->ddwcaf_save_payout( $payout_arg[ 'args' ] );

                $payout_arg[ 'args' ][ 'id' ] = $payout_id;

                foreach ( $payout_arg[ 'commission_ids' ] as $commission_id ) {
                    $this->commission_helper->ddwcaf_update_commission_meta( $commission_id, '_ddwcaf_payout_id', $payout_id );
                    $this->commission_helper->ddwcaf_update_commission_status( $commission_id, 'pending_payment' );
                }
            }

            return [
                'success' => $success,
                'error'   => $error,
            ];
        }

        /**
         * Update payout status function
         *
         * @param int $payout_id
         * @param string $status
         * @param boolean $no_email
         * @param array $payout
         * @return int
         */
        public function ddwcaf_update_payout_status( $payout_id, $status, $no_email = false, $payout = [] ) {
            if ( empty( $payout ) ) {
                $payout = $this->ddwcaf_get_payout_by_id( $payout_id );
            }

			if ( ! $payout ) {
				return false;
			}

            if ( $status === $payout[ 'status' ] ) {
				return false;
            }

            if ( ! $payout ) {
				return false;
			}

			if ( ! $no_email ) {
                $affiliate_id = $payout[ 'affiliate_id' ];
			}

            if ( 'completed' === $status ) {
                $commission_status = 'paid';
            } elseif ( 'pending' === $status ) {
                $commission_status = 'pending_payment';
            } else {
                $commission_status = 'pending';
            }

            $payout[ 'status' ]       = $status;
            $payout[ 'completed_at' ] = 'completed' === $status ? current_time( 'Y-m-d H:i:s' ) : NULL;

            $this->ddwcaf_save_payout( $payout );

            $this->wpdb->query( $this->wpdb->prepare( "UPDATE {$this->wpdb->ddwcaf_commissions} SET status=%s, updated_at=%s WHERE id IN (SELECT commission_id FROM {$this->wpdb->ddwcaf_commissionsmeta} WHERE meta_key=%s AND meta_value=%s)", $commission_status, current_time( 'Y-m-d H:i:s' ), '_ddwcaf_payout_id', $payout_id ) );

			return $payout;
        }

        /**
         * Get payout commissions function
         *
         * @param array $args
         * @return array|null
         */
        public function ddwcaf_get_payout_commissions( $args ) {
            extract( $args );

            return $this->wpdb->get_results( $this->wpdb->prepare( "SELECT * FROM {$this->wpdb->ddwcaf_commissions} WHERE id IN (SELECT commission_id FROM {$this->wpdb->ddwcaf_commissionsmeta} WHERE meta_key=%s AND meta_value=%s) ORDER BY id DESC LIMIT %d OFFSET %d", '_ddwcaf_payout_id', $payout_id, $per_page, $offset ), ARRAY_A );
        }

        /**
         * Get payout commissions count function
         *
         * @param array $args
         * @return array|null
         */
        public function ddwcaf_get_payout_commissions_count( $args ) {
            extract( $args );

            return intval( $this->wpdb->get_var( $this->wpdb->prepare( "SELECT COUNT(commissions.id) FROM {$this->wpdb->ddwcaf_commissions} as commissions WHERE id IN (SELECT commission_id FROM {$this->wpdb->ddwcaf_commissionsmeta} WHERE meta_key=%s AND meta_value=%s)", '_ddwcaf_payout_id', $payout_id ) ) );
        }

		/**
         * Get translation function
         *
         * @param string $word
         * @return string
         */
		public function ddwcaf_get_translation( $word ) {
			$translation = [
				'pending'   => esc_html__( 'Pending', 'affiliates-for-woocommerce' ),
				'completed' => esc_html__( 'Completed', 'affiliates-for-woocommerce' ),
				'cancelled' => esc_html__( 'Cancelled', 'affiliates-for-woocommerce' ),
			];

            return ! empty( $translation[ $word ] ) ? $translation[ $word ] : $word;
		}

        /**
         * Get References function
         *
         * @param string $reference
         * @return string|array
         */
		public function ddwcaf_get_references( $reference = '' ) {
			$references = [
				'manually_by_admin'        => esc_html__( 'Manually by Admin', 'affiliates-for-woocommerce' ),
				'requested_by_affiliate'   => esc_html__( 'Requested by Affiliate', 'affiliates-for-woocommerce' ),
				'automatic_monthly_payout' => esc_html__( 'Automatic Monthly Payout', 'affiliates-for-woocommerce' ),
			];

            return ! empty( $reference ) ? $references[ $reference ] : $references;
		}

        /**
		 * Delete payout function
		 * 
		 * @param int $id
		 * @return int|bool
		 */
        public function ddwcaf_delete_payout( $id ) {
            $response = $this->wpdb->delete(
				$this->wpdb->ddwcaf_payouts,
				[
					'id' => $id
				],
                [ '%d' ]
            );

            if ( $response ) {
                $this->wpdb->query( $this->wpdb->prepare( "UPDATE {$this->wpdb->ddwcaf_commissions} SET status=%s, updated_at=%s WHERE id IN (SELECT commission_id FROM {$this->wpdb->ddwcaf_commissionsmeta} WHERE meta_key=%s AND meta_value=%d)", 'pending', current_time( 'Y-m-d H:i:s' ), '_ddwcaf_payout_id', $id ) );

                $this->wpdb->delete(
                    $this->wpdb->ddwcaf_commissionsmeta,
                    [
                        'meta_key'   => '_ddwcaf_payout_id',
                        'meta_value' => $id,
                    ],
                    [ '%s', '%s' ]
                );
            }

            return $response;
        }
	}
}
