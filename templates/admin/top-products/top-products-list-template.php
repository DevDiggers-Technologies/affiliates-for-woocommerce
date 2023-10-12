<?php
/**
 * Top Products List Template
 *
 * @package Affiliates for WooCommerce
 * @version 1.0.0
 */

namespace DDWCAffiliates\Templates\Admin\Top_Products;

use DDWCAffiliates\Helper\Commission\DDWCAF_Commission_Helper;
use DDWCAffiliates\Helper\Payout\DDWCAF_Payout_Helper;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'DDWCAF_Top_Products_List_Template' ) ) {
	/**
	 * Top Products list class
	 */
	class DDWCAF_Top_Products_List_Template extends \WP_List_table {
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

            parent::__construct( [
				'singular' => esc_html__( 'Top Product List', 'affiliates-for-woocommerce' ),
				'plural'   => esc_html__( 'Top Products List', 'affiliates-for-woocommerce' ),
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

            $per_page     = $this->get_items_per_page( 'ddwcaf_top_products_per_page', 20 );
            $current_page = $this->get_pagenum();
            $offset       = ( $current_page - 1 ) * $per_page;

            $this->args = [
                'search'       => ! empty( $_GET[ 's' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 's' ] ) ) : '',
                'order'        => ! empty( $_GET[ 'order' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'order' ] ) ) : 'desc',
                'orderby'      => ! empty( $_GET[ 'orderby' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'orderby' ] ) ) : 'quantity',
                'affiliate_id' => ! empty( $_GET[ 'affiliate-id' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'affiliate-id' ] ) ) : '',
                'per_page'     => $per_page,
                'offset'       => $offset,
            ];

            $total_items  = $this->commission_helper->ddwcaf_get_top_products_count( $this->args );
            $top_products = $this->commission_helper->ddwcaf_get_top_products( $this->args );

			$this->set_pagination_args( [
				'total_items' => intval( $total_items ),
				'per_page'    => $per_page,
			] );

			$data = $this->ddwcaf_prepare_data( $top_products );

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
			$orderby = ! empty( $_GET[ 'orderby' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'orderby' ] ) ) : 'quantity';
			$order   = ! empty( $_GET[ 'order' ] ) ? sanitize_text_field( wp_unslash( $_GET[ 'order' ] ) ) : 'desc';
			$result  = strnatcmp( $first[ $orderby ], $second[ $orderby ] );

			return 'asc' === $order ? $result : -$result;
		}

		/**
		 * Fetch Data function
		 *
		 * @param array $top_products
		 * @return array $data
		 */
		public function ddwcaf_prepare_data( $top_products ) {
            $data = [];

            if ( ! empty( $top_products ) ) {
                foreach ( $top_products as $top_product ) {
                    $product = wc_get_product( $top_product[ 'product' ] );

                    $data[] = [
                        'product'    => '<a href="' . esc_url( $product->get_permalink() ) . '" target="_blank">' . wp_kses_post( $product->get_image( 'thumbnail' ) ) . '<div>' . esc_html( $product->get_name() ) . '</div></a>',
                        'quantity'   => $top_product[ 'quantity' ],
                        'earnings'   => wc_price( $top_product[ 'earnings' ] ),
                        'commission' => '<strong>' . wc_price( $top_product[ 'commission' ] ) . '</strong>',
					];
                }
            }

			return apply_filters( 'ddwcaf_top_products_list_data', $data );
		}

		/**
		 *  No items
		 *
		 * @return void
		 */
		public function no_items() {
			esc_html_e( 'No products avaliable.', 'affiliates-for-woocommerce' );
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
				'product'    => esc_html__( 'Product', 'affiliates-for-woocommerce' ),
				'quantity'   => esc_html__( 'Quantity', 'affiliates-for-woocommerce' ),
				'earnings'   => esc_html__( 'Total Earnings', 'affiliates-for-woocommerce' ),
				'commission' => esc_html__( 'Total Commisions', 'affiliates-for-woocommerce' ),
			];

			return apply_filters( 'ddwcaf_top_products_list_columns', $columns );
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
			return apply_filters( 'ddwcaf_top_products_list_sortable_columns', [
				'product'    => [ 'product', true ],
				'quantity'   => [ 'quantity', true ],
				'earnings'   => [ 'earnings', true ],
				'commission' => [ 'commission', true ],
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
