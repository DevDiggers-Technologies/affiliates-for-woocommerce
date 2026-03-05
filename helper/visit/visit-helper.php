<?php
/**
 * Visit helper
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Helper\Visit;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Visit_Helper' ) ) {
	/**
	 * Visit helper class
	 */
	class DDWCAF_Visit_Helper {
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
		 * Save Visit function
		 *
		 * @param array $args
		 * @return int
		 */
		public function ddwcaf_save_visit( $args ) {
			$default_args = [
				'affiliate_id'    => 0,
				'url'             => '',
				'referrer_url'    => '',
				'ip'              => '',
				'order_id'        => NULL,
				'date'            => current_time( 'Y-m-d H:i:s' ),
				'conversion_date' => NULL,
			];

			$args = wp_parse_args( $args, $default_args );

			if ( ! empty( $args[ 'id' ] ) ) {
				$id = $args[ 'id' ];
				unset( $args[ 'id' ] );

				$this->wpdb->update(
					$this->wpdb->ddwcaf_visits,
					$args,
					[ 'id' => $id ],
					[ '%d', '%s', '%s', '%s', '%s', '%s', '%s' ],
					[ '%d' ]
				);

				return $id;
			} else {
				$this->wpdb->insert(
					$this->wpdb->ddwcaf_visits,
					$args,
					[ '%d', '%s', '%s', '%s', '%s', '%s', '%s' ]
				);

				return $this->wpdb->insert_id;
			}
		}

		/**
		 * Check visit exists function
		 *
		 * @param array $args
		 * @return int|boolean
		 */
		public function ddwcaf_check_visit_exists( $args ) {
			$conditions = '';
			foreach ( $args as $key => $value ) {
				if ( 'date' === $key ) {
					if ( ! empty( $value ) ) {
						$conditions .= $this->wpdb->prepare( " AND {$key}>=%s", $value );
					}
				} else {
					$conditions .= $this->wpdb->prepare( " AND {$key}=%s", $value );
				}
			}
			return $this->wpdb->get_var( "SELECT id FROM {$this->wpdb->ddwcaf_visits} WHERE 1=1 $conditions" );
		}

		/**
		 * Get recent visit function
		 *
		 * @param array $args
		 * @return int|boolean
		 */
		public function ddwcaf_get_recent_visit( $args ) {
			return $this->wpdb->get_var( $this->wpdb->prepare( "SELECT id FROM {$this->wpdb->ddwcaf_visits} WHERE affiliate_id=%d AND ip=%s ORDER BY id DESC LIMIT 1", $args[ 'affiliate_id' ], $args[ 'ip' ] ) );
		}

		/**
		 * Prepare where conditions for visits function
		 *
		 * @param array $args
		 * @return string
		 */
		public function ddwcaf_prepare_where_conditions_for_visits( $args ) {
			extract( $args );
			$conditions = '';

			if ( ! empty( $affiliate_id ) ) {
				$conditions .= $this->wpdb->prepare( " AND visits.affiliate_id=%d", $affiliate_id );
			}
			if ( ! empty( $from_date ) ) {
				$from_date   = str_replace( 'T', ' ', $from_date );
				$conditions .= $this->wpdb->prepare( " AND visits.date>=%s", $from_date );
			}
			if ( ! empty( $end_date ) ) {
				$end_date   = str_replace( 'T', ' ', $end_date );
				$conditions .= $this->wpdb->prepare( " AND visits.date<=%s", $end_date );
			}
			if ( ! empty( $search ) ) {
				$conditions .= $this->wpdb->prepare( " AND visits.id LIKE %s", '%' . $search . '%' );
			}
			if ( ! empty( $show ) ) {
				if ( 'converted' === $show ) {
					$conditions .= " AND visits.conversion_date IS NOT NULL";
				} elseif ( 'not_converted' === $show ) {
					$conditions .= " AND visits.conversion_date IS NULL";
				}
			}

			return $conditions;
		}

		/**
		 * Get visits function
		 *
		 * @param array $args
		 * @return array
		 */
		public function ddwcaf_get_visits( $args ) {
			extract( $args );

			$conditions = $this->ddwcaf_prepare_where_conditions_for_visits( $args );
			$response   = $this->wpdb->get_results( $this->wpdb->prepare( "SELECT DISTINCT visits.* FROM {$this->wpdb->ddwcaf_visits} as visits LEFT JOIN {$this->wpdb->users} as users ON visits.affiliate_id=users.ID WHERE 1=1 $conditions ORDER BY id DESC LIMIT %d OFFSET %d", $per_page, $offset ), ARRAY_A );

			return apply_filters( 'ddwcaf_modify_visits_response', $response, $args );
		}

		/**
		 * Get all visits count function
		 *
		 * @param array $args
		 * @return int
		 */
		public function ddwcaf_get_visits_count( $args ) {
			$conditions = $this->ddwcaf_prepare_where_conditions_for_visits( $args );
			$response   = $this->wpdb->get_var( "SELECT count(DISTINCT visits.id) FROM {$this->wpdb->ddwcaf_visits} as visits LEFT JOIN {$this->wpdb->users} as users ON visits.affiliate_id=users.ID WHERE 1=1 $conditions" );

			return apply_filters( 'ddwcaf_modify_visits_count', $response, $args );
		}

		/**
		 * Get visit by id function
		 *
		 * @param int $id
		 * @return array|null
		 */
		public function ddwcaf_get_visit_by_id( $id ) {
			return $this->wpdb->get_row( $this->wpdb->prepare( "SELECT * FROM {$this->wpdb->ddwcaf_visits} WHERE id=%s", $id ), ARRAY_A );
		}

		/**
		 * Get conversion details function
		 *
		 * @param array $args
		 * @return array
		 */
		public function ddwcaf_get_conversion_details( $args ) {
			$conditions = $this->ddwcaf_prepare_where_conditions_for_visits( $args );
			$response   = $this->wpdb->get_row( "SELECT SUM( CASE WHEN visits.order_id IS NOT NULL THEN 1 ELSE 0 END ) / COUNT( visits.id ) * 100 AS conversion_rate, SUM( CASE WHEN visits.order_id IS NOT NULL THEN 1 ELSE 0 END ) as customers_count FROM {$this->wpdb->ddwcaf_visits} as visits LEFT JOIN {$this->wpdb->users} as users ON visits.affiliate_id=users.ID WHERE 1=1 $conditions", ARRAY_A );

			return apply_filters( 'ddwcaf_modify_conversion_details', $response, $args );
		}

		/**
		 * Register conversion function
		 *
		 * @param int $order_id
		 * @return boolean
		 */
		public function ddwcaf_register_visit_conversion( $order_id ) {
			if ( empty( $this->ddwcaf_configuration[ 'register_visits_enabled' ] ) ) {
				return false;
			}

			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				return false;
			}

			// Retrieve visit registered within current order.
			$visit_id   = $order->get_meta( '_ddwcaf_visit_id' );
			$registered = $order->get_meta( '_ddwcaf_visit_conversion_registered' );

			$visit = $this->ddwcaf_get_visit_by_id( $visit_id );

			if ( ! $visit ) {
				return false;
			}

			$converted = $order->has_status( wc_get_is_paid_statuses() );

			if ( $converted ) {
				if ( ! $visit_id || $registered ) {
					return false;
				}

				$visit[ 'order_id' ]        = $order_id;
				$visit[ 'conversion_date' ] = current_time( 'Y-m-d H:i:s' );

				// update order meta.
				$order->update_meta_data( '_ddwcaf_visit_conversion_registered', true );
			} else {
				if ( ! $visit_id || ! $registered ) {
					return false;
				}

				$visit[ 'order_id' ]        = NULL;
				$visit[ 'conversion_date' ] = NULL;

				// update order meta.
				$order->update_meta_data( '_ddwcaf_visit_conversion_registered', false );
			}

			$order->save();

			return $this->ddwcaf_save_visit( $visit );
		}

		/**
		 * Render Visits Table Rows function
		 *
		 * @param array $visits
		 * @return void
		 */
		public function ddwcaf_render_visits_table_rows( $visits ) {
			if ( ! empty( $visits ) ) {
				$date_format = get_option( 'date_format' );
				$time_format = get_option( 'time_format' );

				foreach ( $visits as $key => $visit ) {
					$affiliate_id   = $visit[ 'affiliate_id' ];
					$affiliate_data = get_userdata( $affiliate_id );
					?>
					<tr>
						<td><?php echo esc_html( $visit[ 'id' ] ); ?></td>
						<td>
							<a href="<?php echo esc_url( $visit[ 'url' ] ); ?>" target="_blank"><?php echo esc_url( $visit[ 'url' ] ); ?></a>
						</td>
						<td><?php echo wp_kses_post( ! empty( $visit[ 'referrer_url' ] ) ? '<a href="' . esc_url( $visit[ 'referrer_url' ] ) . '">' . esc_url( $visit[ 'referrer_url' ] ) . '</a>' : 'N/A' ); ?></td>
						<td><?php echo esc_html( date_i18n( "{$date_format} {$time_format}", strtotime( $visit[ 'date' ] ) ) ); ?></td>
						<td><?php echo esc_html( ! empty( $visit[ 'conversion_date' ] ) ? date_i18n( "{$date_format} {$time_format}", strtotime( $visit[ 'conversion_date' ] ) ) : 'N/A' ); ?></td>
					</tr>
					<?php
				}
			} else {
				?>
				<tr>
					<td colspan="5"><center><?php esc_html_e( 'No visits yet.', 'affiliates-for-woocommerce' ); ?></center></td>
				</tr>
				<?php
			}
		}

		/**
		 * Delete visit function
		 * 
		 * @param int $id
		 * @return int|bool
		 */
		public function ddwcaf_delete_visit( $id ) {
			return $this->wpdb->delete(
				$this->wpdb->ddwcaf_visits,
				[
					'id' => $id
				],
				[ '%d' ]
			);
		}
	}
}
