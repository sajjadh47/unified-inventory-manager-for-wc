<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @package       Uimfwc_Unified_Inventory_Manager_For_Wc
 * @subpackage    Uimfwc_Unified_Inventory_Manager_For_Wc/admin/views
 * @version       1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$uimfwc_nonce       = wp_create_nonce( 'uimfwc_products_inventory_nonce' );
$uimfwc_current_tab = 'simple-products';

$uimfwc_tabs = array(
	'simple-products'   => array(
		'label' => __( 'Simple Products', 'unified-inventory-manager-for-wc' ),
		'url'   => add_query_arg(
			array(
				'tab'          => 'simple-products',
				'uimfwc_nonce' => $uimfwc_nonce,
			),
			admin_url( 'edit.php?post_type=product&page=unified-inventory-manager-for-wc' )
		),
	),
	'variable-products' => array(
		'label' => __( 'Variable Products', 'unified-inventory-manager-for-wc' ),
		'url'   => add_query_arg(
			array(
				'tab'          => 'variable-products',
				'uimfwc_nonce' => $uimfwc_nonce,
			),
			admin_url( 'edit.php?post_type=product&page=unified-inventory-manager-for-wc' )
		),
	),
);

/**
 * Filters the admin menu tabs.
 *
 * This filter allows you to add, remove, or modify the tabs that appear
 * on the admin menu page.
 *
 * @since     1.0.0
 * @param     array $uimfwc_tabs  An array of admin tabs with their URLs.
 * @param     array $uimfwc_nonce Nonce.
 * @return    array               The filtered array of admin tabs.
 */
$uimfwc_tabs = apply_filters( 'uimfwc_admin_menu_tabs', $uimfwc_tabs, $uimfwc_nonce );

if ( isset( $_GET['tab'], $_GET['uimfwc_nonce'] ) && wp_verify_nonce( sanitize_key( $_GET['uimfwc_nonce'] ), 'uimfwc_products_inventory_nonce' ) ) {
	$uimfwc_current_tab = sanitize_key( $_GET['tab'] );
}

/**
 * Abstract base class for product inventory tables.
 *
 * This file contains the definition of the abstract class
 * `Uimfwc_Abstract_Products_Table`, which extends WP_List_Table.
 * It provides a foundation for creating tables that display product inventory
 * information in the WordPress admin.
 */
require_once UIMFWC_UNIFIED_INVENTORY_MANAGER_FOR_WC_PLUGIN_PATH . 'includes/class-uimfwc-abstract-products-table.php';

?>
<div class="wrap">
	<form class="uimfwc_inventory_manager">
		<input type="hidden" id="uimfwc_nonce" name="uimfwc_nonce" value="<?php echo esc_attr( $uimfwc_nonce ); ?>">
		<input type="hidden" id="page" name="page" value="unified-inventory-manager-for-wc">
		<h2 class="nav-tab-wrapper">
			<?php
			foreach ( $uimfwc_tabs as $tab_id => $tab_data ) {
				printf(
					'<a href="%s" class="nav-tab %s">%s</a>',
					esc_url( $tab_data['url'] ),
					( $tab_id === $uimfwc_current_tab ) ? 'nav-tab-active' : '',
					esc_html( $tab_data['label'] )
				);
			}
			?>
		</h2>
		<?php

		$uimfwc_list_table = null;

		if ( 'simple-products' === $uimfwc_current_tab ) :
			/**
			 * Class for displaying simple product inventory table.
			 *
			 * This file contains the definition of the class
			 * `Uimfwc_Simple_Products_Table`, which extends
			 * `Uimfwc_Abstract_Products_Table`. It is used to display
			 * inventory information for simple products in the WordPress admin.
			 */
			require_once UIMFWC_UNIFIED_INVENTORY_MANAGER_FOR_WC_PLUGIN_PATH . 'includes/class-uimfwc-simple-products-table.php';

			$uimfwc_list_table = new Uimfwc_Simple_Products_Table();

		elseif ( 'variable-products' === $uimfwc_current_tab ) :
			/**
			 * Class for displaying variable product inventory table.
			 *
			 * This file contains the definition of the class
			 * `Uimfwc_Variable_Products_Table`, which extends
			 * `Uimfwc_Abstract_Products_Table`. It is used to display
			 * inventory information for variable products in the WordPress admin.
			 */
			require_once UIMFWC_UNIFIED_INVENTORY_MANAGER_FOR_WC_PLUGIN_PATH . 'includes/class-uimfwc-variable-products-table.php';

			$uimfwc_list_table = new Uimfwc_Variable_Products_Table();
		endif;

		/**
		 * Filters the list table object to display for the current admin tab.
		 *
		 * This filter allows you to provide a custom list table object for a new
		 * tab. Other plugins can use this filter to hook their own table classes.
		 *
		 * @since     1.0.0
		 * @param     object $uimfwc_list_table  The default list table object, or null.
		 * @param     string $uimfwc_current_tab The ID of the current tab.
		 * @return    object                     The filtered list table object.
		 */
		$uimfwc_list_table = apply_filters( 'uimfwc_admin_list_table', $uimfwc_list_table, $uimfwc_current_tab );

		if ( $uimfwc_list_table ) {
			$uimfwc_list_table->prepare_items();
			$uimfwc_list_table->search_box( __( 'Search', 'unified-inventory-manager-for-wc' ), 'search' );
			$uimfwc_list_table->display();
			?>
			<p class="submit uimfwc_save_products_submit_container">
				<input type="button" disabled="disabled" id="uimfwc_save_bulk" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'unified-inventory-manager-for-wc' ); ?>">
				<img class="uimfwc-d-none" alt="table saving loading spinner" src="<?php echo esc_url( admin_url( 'images/spinner.gif' ) ); ?>">
			</p>
		<?php } ?>
	</form>
</div>