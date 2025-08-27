<?php
/**
 * This file contains the definition of the Uimfwc_Unified_Inventory_Manager_For_Wc_Admin class, which
 * is used to load the plugin's admin-specific functionality.
 *
 * @package       Uimfwc_Unified_Inventory_Manager_For_Wc
 * @subpackage    Uimfwc_Unified_Inventory_Manager_For_Wc/admin
 * @version       1.0.0
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version and other methods.
 *
 * @since    1.0.0
 */
class Uimfwc_Unified_Inventory_Manager_For_Wc_Admin {
	/**
	 * The ID of this plugin.
	 *
	 * @since     1.0.0
	 * @access    private
	 * @var       string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since     1.0.0
	 * @access    private
	 * @var       string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @param     string $plugin_name The name of this plugin.
	 * @param     string $version     The version of this plugin.
	 * @return    void
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @return    void
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, UIMFWC_UNIFIED_INVENTORY_MANAGER_FOR_WC_PLUGIN_URL . 'admin/css/admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @return    void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, UIMFWC_UNIFIED_INVENTORY_MANAGER_FOR_WC_PLUGIN_URL . 'admin/js/admin.js', array( 'jquery' ), $this->version, true );

		wp_localize_script(
			$this->plugin_name,
			'uimfwcUnifiedInventoryManagerForWc',
			array(
				'ajaxurl'           => admin_url( 'admin-ajax.php' ),
				'ajaxSaveMsgI18n'   => esc_attr__( 'Save Changes', 'unified-inventory-manager-for-wc' ),
				'ajaxSavingMsgI18n' => esc_attr__( 'Saving Changes', 'unified-inventory-manager-for-wc' ),
			)
		);
	}

	/**
	 * Adds a settings link to the plugin's action links on the plugin list table.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @param     array $links The existing array of plugin action links.
	 * @return    array $links The updated array of plugin action links, including the settings link.
	 */
	public function plugin_action_links( $links ) {
		$links[] = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'edit.php?post_type=product&page=unified-inventory-manager-for-wc' ) ), __( 'Settings', 'unified-inventory-manager-for-wc' ) );

		return $links;
	}

	/**
	 * Adds the plugin settings page to the WordPress dashboard menu.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @return    void
	 */
	public function admin_menu() {
		add_submenu_page(
			'edit.php?post_type=product',
			__( 'Stock Manager', 'unified-inventory-manager-for-wc' ),
			__( 'Stock Manager', 'unified-inventory-manager-for-wc' ),
			// phpcs:ignore WordPress.WP.Capabilities.Unknown
			'edit_products',
			'unified-inventory-manager-for-wc',
			array( $this, 'menu_page' ),
		);
	}

	/**
	 * Renders the plugin menu page content.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @return    void
	 */
	public function menu_page() {
		// phpcs:ignore WordPress.WP.Capabilities.Unknown
		if ( ! current_user_can( 'edit_products' ) ) {
			wp_die( esc_attr__( 'You do not have sufficient permissions to access this page.', 'unified-inventory-manager-for-wc' ) );
		}

		include UIMFWC_UNIFIED_INVENTORY_MANAGER_FOR_WC_PLUGIN_PATH . 'admin/views/plugin-admin-display.php';
	}

	/**
	 * Displays admin notices in the admin area.
	 *
	 * This function checks if the required plugin is active.
	 * If not, it displays a warning notice and deactivates the current plugin.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @return    void
	 */
	public function admin_notices() {
		// Check if required plugin is active.
		if ( ! class_exists( 'WooCommerce', false ) ) {
			sprintf(
				'<div class="notice notice-warning is-dismissible"><p>%s <a href="%s">%s</a> %s</p></div>',
				__( 'Unified Inventory Manager For WooCommerce requires', 'unified-inventory-manager-for-wc' ),
				esc_url( 'https://wordpress.org/plugins/woocommerce/' ),
				__( 'WooCommerce', 'unified-inventory-manager-for-wc' ),
				__( 'plugin to be active!', 'unified-inventory-manager-for-wc' ),
			);

			// Deactivate the plugin.
			deactivate_plugins( UIMFWC_UNIFIED_INVENTORY_MANAGER_FOR_WC_PLUGIN_BASENAME );
		}
	}

	/**
	 * Declares compatibility with WooCommerce's custom order tables feature.
	 *
	 * This function is hooked into the `before_woocommerce_init` action and checks
	 * if the `FeaturesUtil` class exists in the `Automattic\WooCommerce\Utilities`
	 * namespace. If it does, it declares compatibility with the 'custom_order_tables'
	 * feature. This is important for ensuring the plugin works correctly with
	 * WooCommerce versions that support this feature.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @return    void
	 */
	public function declare_compatibility_with_wc_custom_order_tables() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				UIMFWC_UNIFIED_INVENTORY_MANAGER_FOR_WC_PLUGIN_FULLPATH,
				true // true (compatible, default) or false (not compatible).
			);
		}
	}

	/**
	 * Saves stock data for a product.
	 *
	 * This function handles the saving of stock data for a product, It performs security checks,
	 * validates input, and updates the product's meta data accordingly. This function is
	 * intended to be called via an AJAX request.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @return    void
	 */
	public function save_product() {
		// Verify the request using the nonce.
		check_ajax_referer( 'uimfwc_products_inventory_nonce', 'uimfwc_nonce' );

		// Check if the user has permission to edit products and if the product ID is provided.
		// phpcs:ignore WordPress.WP.Capabilities.Unknown
		if ( ! current_user_can( 'edit_products' ) || empty( $_REQUEST['product_id'] ) ) {
			wp_die( 403 ); // Return a 403 Forbidden error code.
		}

		// Get the parent product ID from the request.
		$parent_product_id = intval( $_REQUEST['product_id'] );

		// Get the parent product object.
		$parent_product = wc_get_product( $parent_product_id );

		// Check if the parent product is a simple product.
		if ( $parent_product && ( $parent_product->is_type( 'simple' ) || $parent_product->is_type( 'variation' ) ) && isset( $_REQUEST['data'][ $parent_product_id ]['quantity'] ) ) {
			$new_quantity = intval( $_REQUEST['data'][ $parent_product_id ]['quantity'] );

			// Set stock quantity for the parent product.
			$parent_product->set_stock_quantity( $new_quantity );

			// Save the changes to the parent product.
			$parent_product->save();

			wp_die( esc_html( $new_quantity ) ); // Return quantity.
		}

		wp_die( 400 ); // Return a 400 Bad Request error code if the product is not a variable product.
	}
}
