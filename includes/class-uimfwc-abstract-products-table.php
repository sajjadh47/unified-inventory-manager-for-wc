<?php
/**
 * WP_List_Table is not loaded automatically so we need to load it manually.
 *
 * @package    Uimfwc_Unified_Inventory_Manager_For_Wc
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Abstract base class for product inventory tables.
 *
 * This abstract class extends WP_List_Table to provide a foundation for creating
 * tables that display product inventory information in the WordPress admin.
 * It defines common properties and methods used by specific product type tables.
 *
 * @version       1.0.0
 * @package       Uimfwc_Unified_Inventory_Manager_For_Wc
 * @subpackage    Uimfwc_Unified_Inventory_Manager_For_Wc/includes
 */
abstract class Uimfwc_Abstract_Products_Table extends WP_List_Table {
	/**
	 * The product type this table handles
	 *
	 * @var    string
	 */
	protected $product_type = '';

	/**
	 * Meta query arguments for the product type
	 *
	 * @var    array
	 */
	protected $meta_query = array();

	/**
	 * Columns to display in the table
	 *
	 * @var    array
	 */
	protected $columns = array();

	/**
	 * Constructor for the class.
	 *
	 * This constructor sets up the class properties, including the singular and
	 * plural labels for the list table.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @param     string $singular Singular label for the table entries (e.g., 'Product').
	 * @param     string $plural   Plural label for the table entries (e.g., 'Products').
	 * @return    void
	 */
	public function __construct( $singular, $plural ) {
		parent::__construct(
			array(
				'singular' => $singular, // Singular name of the listed records.
				'plural'   => $plural,   // Plural name of the listed records.
			)
		);
	}

	/**
	 * Get the columns for the table.
	 *
	 * This method returns an array defining the columns to be displayed in the table.
	 * The keys of the array are the column IDs, and the values are the column labels.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @return    array An associative array of column IDs and labels.
	 */
	public function get_columns() {
		return $this->columns;
	}

	/**
	 * Default column handler.
	 *
	 * This method handles the display of data for columns that don't have a
	 * specific column rendering method defined (e.g., column_title()).  It
	 * retrieves the value from the `$item` array using the column name.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @param     array  $item        The data for the current table row.
	 * @param     string $column_name The name of the column being rendered.
	 * @return    mixed               The data to display for the column, or an empty string if the column is not found.
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ] ?? '';
	}

	/**
	 * Get the total record count for pagination.
	 *
	 * This method calculates the total number of records to be displayed in the
	 * table.  It's used to determine the total number of items for pagination.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @return    int The total number of records.
	 */
	public function total_record_count() {
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids', // Only get IDs to count.
		);

		// Handle search & verify the request using the nonce.
		if ( isset( $_REQUEST['s'], $_REQUEST['uimfwc_nonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['uimfwc_nonce'] ), 'uimfwc_products_inventory_nonce' ) ) {
			$args['s'] = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
		}

		// Handle meta_query.
		if ( ! empty( $this->meta_query ) ) {
			$meta_query = array();

			foreach ( $this->meta_query as $meta ) {
				if ( empty( $meta['key'] ) ) {
					continue;
				}

				$clause = array(
					'key' => $meta['key'],
				);

				if ( isset( $meta['compare'] ) && strtoupper( $meta['compare'] ) === 'NOT EXISTS' ) {
					$clause['compare'] = 'NOT EXISTS';
				} else {
					$clause['value']   = $meta['value'];
					$clause['compare'] = isset( $meta['compare'] ) ? $meta['compare'] : '=';
				}

				$meta_query[] = $clause;
			}

			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$args['meta_query'] = $meta_query;
		}

		// Handle taxonomy filter.
		if ( ! empty( $this->product_type ) ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => $this->product_type,
				),
			);
		}

		// Execute query and get the count.
		$query = new WP_Query( $args );
		$count = $query->found_posts;

		return (int) $count;
	}

	/**
	 * Message to display when no items are found.
	 *
	 * This method outputs a user-friendly message when there are no items
	 * to display in the table.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @return    void
	 */
	public function no_items() {
		esc_html_e( 'No items are available.', 'unified-inventory-manager-for-wc' );
	}

	/**
	 * Get the base query arguments.
	 *
	 * This method constructs an array of arguments for WP_Query, used to retrieve
	 * the products to be displayed in the table.  It includes parameters for
	 * post type, pagination, meta query, and search.
	 *
	 * @since     1.0.0
	 * @access    protected
	 * @param     int $per_page     Number of items to display per page.
	 * @param     int $current_page Current page number.
	 * @return    array             An array of arguments for WP_Query.
	 */
	protected function get_base_query_args( $per_page, $current_page ) {
		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => $per_page,
			'paged'          => $current_page,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query'     => $this->meta_query,
			'fields'         => 'ids', // Fetch only IDs for performance.
		);

		if ( $this->product_type ) {
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => $this->product_type,
				),
			);
		}

		// Handle search & verify the request using the nonce.
		if ( isset( $_REQUEST['s'], $_REQUEST['uimfwc_nonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['uimfwc_nonce'] ), 'uimfwc_products_inventory_nonce' ) ) {
			$args['s'] = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
		}

		return $args;
	}

	/**
	 * Prepare the stock status HTML.
	 *
	 * This method generates the HTML markup for displaying the stock status of a product.
	 * It includes the stock status text (e.g., "In stock", "Out of stock") and the
	 * current stock quantity, along with a data attribute for the quantity multiplier.
	 *
	 * @since     1.0.0
	 * @access    protected
	 * @param     WC_Product $product The WooCommerce product object.
	 * @return    string The HTML markup for the stock status display.
	 */
	protected function get_stock_status_html( $product ) {
		$stock_status_key = $product->get_stock_status();

		switch ( $stock_status_key ) {
			case 'instock':
				$stock_status_text = __( 'In stock', 'unified-inventory-manager-for-wc' );
				break;

			case 'onbackorder':
				$stock_status_text = __( 'On backorder', 'unified-inventory-manager-for-wc' );
				break;

			case 'outofstock':
				$stock_status_text = __( 'Out of stock', 'unified-inventory-manager-for-wc' );
				break;

			default:
				$stock_status_text = __( 'N/A', 'unified-inventory-manager-for-wc' );
				break;
		}

		$stock_status_text_label = sprintf(
			'<mark class="%s">%s (<span>%d</span>)</mark>',
			$stock_status_key,
			$stock_status_text,
			$product->get_stock_quantity()
		);

		/**
		 * Filters the stock status text label for a product.
		 *
		 * Use this filter to customize the text label that describes a product's
		 * stock status, such as "In stock" or "Out of stock."
		 *
		 * @since     1.0.0
		 * @param     string     $stock_status_text_label The default stock status text label.
		 * @param     WC_Product $product                 The product object.
		 * @return    string                              The filtered stock status text label.
		 */
		return apply_filters( 'uimfwc_product_stock_status_text', $stock_status_text_label, $product );
	}

	/**
	 * Prepare the quantity input HTML.
	 *
	 * This method generates the HTML markup for a quantity input field, used to
	 * allow users to update product quantities in the inventory table.
	 *
	 * @since     1.0.0
	 * @access    protected
	 * @param     int    $product_id   The ID of the product.
	 * @param     string $input_name   The name attribute for the input field.
	 * @param     int    $quantity     The current quantity of the product.
	 * @param     string $product_type The type of product (e.g., 'simple', 'variable').
	 * @param     array  $custom_attrs An array of additional HTML attributes for the input field.
	 * @return    string               The HTML markup for the quantity input field.
	 */
	protected function get_quantity_input_html( $product_id, $input_name, $quantity, $product_type, $custom_attrs = array() ) {
		ob_start();

			woocommerce_wp_text_input(
				array(
					'type'              => 'number',
					'id'                => $input_name . '[' . esc_attr( $product_id ) . '][quantity]',
					'label'             => '',
					'value'             => esc_attr( $quantity ),
					'placeholder'       => __( '1', 'unified-inventory-manager-for-wc' ),
					'class'             => 'uimfwc_product_inventory_quantity_bulk',
					'custom_attributes' => array_merge(
						array(
							'data-product_id'   => esc_attr( $product_id ),
							'data-product_type' => esc_attr( $product_type ),
						),
						$custom_attrs
					),
				)
			);

		return ob_get_clean();
	}

	/**
	 * Prepare the product name link HTML.
	 *
	 * This method generates the HTML markup for a link to the product edit page
	 * in the WordPress admin.  It displays the product name as a clickable link.
	 *
	 * @since     1.0.0
	 * @access    protecte
	 * @param     int    $product_id   The ID of the product.
	 * @param     string $product_name The name of the product.
	 * @return    string               The HTML markup for the product name link.
	 */
	protected function get_product_name_html( $product_id, $product_name ) {
		// Get parent product if provided id is a variation.
		$parent_id = wp_get_post_parent_id( $product_id );

		if ( $parent_id ) {
			$product_id = $parent_id;
		}

		return sprintf(
			"<a class='product_name' href='%s'>%s ↗</a>",
			esc_url(
				add_query_arg(
					array(
						'post'   => $product_id,
						'action' => 'edit',
					),
					admin_url( 'post.php' )
				)
			),
			esc_html( self::truncate_string( $product_name ) )
		);
	}

	/**
	 * Prepare the items for display.
	 *
	 * This method fetches the data for the table, sets up the column headers,
	 * and configures pagination.  It calls the abstract method {@see get_items()}
	 * to retrieve the actual data.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @return    void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$per_page              = $this->get_items_per_page( 'logs_per_page', 10 );
		$current_page          = $this->get_pagenum();
		$total_items           = $this->total_record_count();
		$this->_column_headers = array( $columns, array(), array() );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);

		$this->items = $this->get_items( $per_page, $current_page );
	}

	/**
	 * Get the items for the table.
	 *
	 * This abstract method is where the specific logic for retrieving the data
	 * for the table should be implemented by child classes.  It should return an
	 * array of data to be displayed in the table.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @param     int $per_page     Number of items to display per page. Default is 10.
	 * @param     int $current_page Current page number. Default is 1.
	 * @return    array             An array of data to display in the table.
	 */
	abstract public function get_items( $per_page = 10, $current_page = 1 );

	/**
	 * Truncates a string to a specified length and appends an ellipsis if needed.
	 *
	 * This function truncates a given string to a maximum length specified by the
	 * `$length` parameter.  If the string's length exceeds the maximum length,
	 * it is shortened, and an ellipsis (...) is appended to indicate truncation.
	 * The function uses mb_strlen and mb_substr for multibyte string safety.
	 *
	 * @since     1.0.0
	 * @access    private
	 * @param     string $text   The input string to truncate.
	 * @param     int    $length The maximum length of the string. Default is 30.
	 * @return    string         The truncated string with ellipsis if applicable.
	 */
	private static function truncate_string( $text, $length = 30 ) {
		// Use mb_strlen to get the string length for multibyte safety.
		if ( mb_strlen( $text ) > $length ) {
			// Use mb_substr to get a part of the string for multibyte safety, and append ellipsis.
			return mb_substr( $text, 0, $length ) . '...';
		} else {
			// If the string is already shorter than the limit, return it as is.
			return $text;
		}
	}
}
