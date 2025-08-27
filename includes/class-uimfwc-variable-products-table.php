<?php
/**
 * Class for displaying variable products in the admin.
 *
 * This class extends the WP_List_Table class to display a list of variable
 * products in the WordPress admin interface. It is used to manage
 * and display variable products inventory.
 *
 * @version       1.0.0
 * @package       Uimfwc_Unified_Inventory_Manager_For_Wc
 * @subpackage    Uimfwc_Unified_Inventory_Manager_For_Wc/admin
 */

/**
 * Non grouped variable products table rendering class.
 */
class Uimfwc_Variable_Products_Table extends Uimfwc_Abstract_Products_Table {
	/**
	 * The product type this table handles.
	 *
	 * @var    string
	 */
	protected $product_type = 'variable';

	/**
	 * Meta query arguments for the product type.
	 *
	 * @var    array
	 */
	protected $meta_query = array();

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
			'id'         => __( 'Product ID', 'unified-inventory-manager-for-wc' ),
			'image'      => sprintf( '<span class="wc-image tips">%s</span>', __( 'Image', 'unified-inventory-manager-for-wc' ) ),
			'name'       => __( 'Product Name', 'unified-inventory-manager-for-wc' ),
			'sku'        => __( 'Product SKU', 'unified-inventory-manager-for-wc' ),
			'variations' => __( 'Variations', 'unified-inventory-manager-for-wc' ),
		);

		/**
		 * Filters the column headers for the variable products table.
		 *
		 * Use this filter to add, remove, or modify the columns displayed in the
		 * variable products table.
		 *
		 * @since     1.0.0
		 * @param     array $columns An associative array of default column headers.
		 * @return    array          The filtered array of column headers.
		 */
		$this->columns = apply_filters( 'uimfwc_variable_products_table_column_headers', $columns );

		parent::__construct(
			__( 'Variable Product', 'unified-inventory-manager-for-wc' ), // Singular name of the listed records.
			__( 'Variable Products', 'unified-inventory-manager-for-wc' ), // Plural name of the listed records.
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
					'id'         => $product_id,
					'image'      => $product->get_image( 'thumbnail' ),
					'name'       => $this->get_product_name_html( $product_id, $product->get_name() ),
					'sku'        => $product->get_sku(),
					'variations' => $this->get_variations_html( $product ),
				);

				/**
				 * Filters the variable product table column data.
				 *
				 * Use this filter to modify the data within the columns of the
				 * variable product table before it is displayed.
				 *
				 * @since     1.0.0
				 * @param     array      $data    An array of data for each row/column in the table.
				 * @param     WC_Product $product The product object.
				 * @return    array               The filtered array of table column data.
				 */
				$items[] = apply_filters( 'uimfwc_variable_products_table_column_data', $data, $product );
			}
		}

		return $items;
	}

	/**
	 * Generates HTML for displaying product variations in a table.
	 *
	 * This function retrieves the variations of a given product and generates an HTML
	 * table to display their details, including ID, image, name, SKU, stock status,
	 * and stock quantity. It specifically targets variations that are managing stock.
	 *
	 * @since     1.0.0
	 * @access    protected
	 * @param     WC_Product $product The WooCommerce product object.
	 * @return    string              HTML table markup for displaying the product variations.
	 */
	protected function get_variations_html( $product ) {
		$variations = $product->get_children();

		$variations_html = sprintf(
			'<table class="widefat striped">
				<thead>
					<tr>
						<th>%s</th>
						<th>%s</th>
						<th>%s</th>
						<th>%s</th>
						<th>%s</th>
						<th>%s</th>
					</tr>
				</thead>
				<tbody>',
			esc_html__( 'Variation ID', 'unified-inventory-manager-for-wc' ),
			sprintf( '<span class="wc-image tips">%s</span>', __( 'Image', 'unified-inventory-manager-for-wc' ) ),
			esc_html__( 'Variation Name', 'unified-inventory-manager-for-wc' ),
			esc_html__( 'Variation SKU', 'unified-inventory-manager-for-wc' ),
			esc_html__( 'Stock Status', 'unified-inventory-manager-for-wc' ),
			esc_html__( 'Stock On Hand', 'unified-inventory-manager-for-wc' )
		);

		foreach ( $variations as $variation_id ) {
			$variation = wc_get_product( $variation_id );

			if ( ! $variation || ! $variation->managing_stock() ) {
				continue;
			}

			$variations_html .= sprintf(
				'<tr>
					<td>%d</td>
					<td class="image column-image">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="stock column-stock">%s</td>
					<td class="quantity column-quantity">%s</td>
				</tr>',
				$variation_id,
				$variation->get_image( 'thumbnail' ),
				$this->get_product_name_html( $variation_id, $variation->get_name() ),
				$variation->get_sku(),
				$this->get_stock_status_html( $variation ),
				$this->get_quantity_input_html(
					$variation_id,
					'uimfwc_variable_product_inventory',
					$variation->get_stock_quantity(),
					$product->get_type(),
					array(
						'data-product_id' => esc_attr( $variation_id ),
					)
				)
			);
		}

		$variations_html .= '</tbody></table>';

		/**
		 * Filters the product variations table to be displayed.
		 *
		 * @since     1.0.0
		 * @param     string     $variations_html The original variations table.
		 * @param     WC_Product $product         The product object.
		 * @return    string                      The filtered markup of the variations table.
		 */
		return apply_filters( 'uimfwc_variations_table', $variations_html, $product );
	}
}
