<?php
/**
 * Class for displaying simple products in the admin.
 *
 * This class extends the WP_List_Table class to display a list of simple
 * products in the WordPress admin interface. It is used to manage
 * and display simple products inventory.
 *
 * @version       1.0.0
 * @package       Uimfwc_Unified_Inventory_Manager_For_Wc
 * @subpackage    Uimfwc_Unified_Inventory_Manager_For_Wc/admin
 */

/**
 * Simple products table rendering class.
 */
class Uimfwc_Simple_Products_Table extends Uimfwc_Abstract_Products_Table {
	/**
	 * The product type this table handles.
	 *
	 * @var    string
	 */
	protected $product_type = 'simple';

	/**
	 * Meta query arguments for the product type.
	 *
	 * @var    array
	 */
	protected $meta_query = array(
		array(
			'key'     => '_manage_stock',
			'compare' => '=',
			'value'   => 'yes',
		),
	);

	/**
	 * Columns to display in the table.
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
	 * @return    void
	 */
	public function __construct() {
		$columns = array(
			'id'       => __( 'Product ID', 'unified-inventory-manager-for-wc' ),
			'image'    => sprintf( '<span class="wc-image tips">%s</span>', __( 'Image', 'unified-inventory-manager-for-wc' ) ),
			'name'     => __( 'Product Name', 'unified-inventory-manager-for-wc' ),
			'sku'      => __( 'Product SKU', 'unified-inventory-manager-for-wc' ),
			'stock'    => __( 'Stock Status', 'unified-inventory-manager-for-wc' ),
			'quantity' => __( 'Stock On Hand', 'unified-inventory-manager-for-wc' ),
		);

		/**
		 * Filters the column headers for the simple products table.
		 *
		 * Use this filter to add, remove, or modify the columns displayed in the
		 * simple products table.
		 *
		 * @since     1.0.0
		 * @param     array $columns An associative array of default column headers.
		 * @return    array          The filtered array of column headers.
		 */
		$this->columns = apply_filters( 'uimfwc_simple_products_table_column_headers', $columns );

		parent::__construct(
			__( 'Simple Product', 'unified-inventory-manager-for-wc' ), // Singular name of the listed records.
			__( 'Simple Products', 'unified-inventory-manager-for-wc' ), // Plural name of the listed records.
		);
	}

	/**
	 * Retrieves items for the list table with pagination and search, returning the data.
	 *
	 * This method fetches simple type products data, applies
	 * pagination, and handles search queries. It populates and returns an array
	 * with the formatted data for display in the list table. This version does not
	 * directly assign to `$this->items`.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @param     int $per_page     The number of items to retrieve per page.
	 * @param     int $current_page The current page number.
	 * @return    array             An array of items formatted for the list table.
	 */
	public function get_items( $per_page = 10, $current_page = 1 ) {
		$items    = array();
		$args     = $this->get_base_query_args( $per_page, $current_page );
		$products = new WP_Query( $args );

		if ( ! empty( $products->posts ) ) {
			foreach ( $products->posts as $product_id ) {
				$product = wc_get_product( $product_id );

				if ( ! $product ) {
					continue;
				}

				$data = array(
					'id'       => $product_id,
					'image'    => $product->get_image( 'thumbnail' ),
					'name'     => $this->get_product_name_html( $product_id, $product->get_name() ),
					'sku'      => $product->get_sku(),
					'stock'    => $this->get_stock_status_html( $product ),
					'quantity' => $this->get_quantity_input_html(
						$product_id,
						'uimfwc_simple_product_inventory',
						$product->get_stock_quantity(),
						$product->get_type(),
						array(
							'data-product_id' => esc_attr( $product_id ),
						)
					),
				);

				/**
				 * Filters the simple product table column data.
				 *
				 * Use this filter to modify the data within the columns of the
				 * simple product table before it is displayed.
				 *
				 * @since     1.0.0
				 * @param     array      $data    An array of data for each row/column in the table.
				 * @param     WC_Product $product The product object.
				 * @return    array               The filtered array of table column data.
				 */
				$items[] = apply_filters( 'uimfwc_simple_products_table_column_data', $data, $product );
			}
		}

		return $items;
	}
}
