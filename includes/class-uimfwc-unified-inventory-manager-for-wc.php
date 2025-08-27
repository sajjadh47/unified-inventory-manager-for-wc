<?php
/**
 * This file contains the definition of the Uimfwc_Unified_Inventory_Manager_For_Wc class, which
 * is used to begin the plugin's functionality.
 *
 * @package       Uimfwc_Unified_Inventory_Manager_For_Wc
 * @subpackage    Uimfwc_Unified_Inventory_Manager_For_Wc/includes
 * @version       1.0.0
 */

/**
 * The core plugin class.
 *
 * This is used to define admin-specific hooks and public-facing hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since    1.0.0
 */
class Uimfwc_Unified_Inventory_Manager_For_Wc {
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since     1.0.0
	 * @access    protected
	 * @var       Uimfwc_Unified_Inventory_Manager_For_Wc_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since     1.0.0
	 * @access    protected
	 * @var       string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since     1.0.0
	 * @access    protected
	 * @var       string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @return    void
	 */
	public function __construct() {
		$this->version     = defined( 'UIMFWC_UNIFIED_INVENTORY_MANAGER_FOR_WC_PLUGIN_VERSION' ) ? UIMFWC_UNIFIED_INVENTORY_MANAGER_FOR_WC_PLUGIN_VERSION : '1.0.0';
		$this->plugin_name = 'unified-inventory-manager-for-wc';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Uimfwc_Unified_Inventory_Manager_For_Wc_Loader. Orchestrates the hooks of the plugin.
	 * - Uimfwc_Unified_Inventory_Manager_For_Wc_Admin.  Defines all hooks for the admin area.
	 * - Uimfwc_Unified_Inventory_Manager_For_Wc_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since     1.0.0
	 * @access    private
	 * @return    void
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once UIMFWC_UNIFIED_INVENTORY_MANAGER_FOR_WC_PLUGIN_PATH . 'includes/class-uimfwc-unified-inventory-manager-for-wc-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once UIMFWC_UNIFIED_INVENTORY_MANAGER_FOR_WC_PLUGIN_PATH . 'admin/class-uimfwc-unified-inventory-manager-for-wc-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once UIMFWC_UNIFIED_INVENTORY_MANAGER_FOR_WC_PLUGIN_PATH . 'public/class-uimfwc-unified-inventory-manager-for-wc-public.php';

		$this->loader = new Uimfwc_Unified_Inventory_Manager_For_Wc_Loader();
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since     1.0.0
	 * @access    private
	 * @return    void
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Uimfwc_Unified_Inventory_Manager_For_Wc_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'plugin_action_links_' . UIMFWC_UNIFIED_INVENTORY_MANAGER_FOR_WC_PLUGIN_BASENAME, $plugin_admin, 'plugin_action_links' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'admin_notices' );

		$this->loader->add_action( 'before_woocommerce_init', $plugin_admin, 'declare_compatibility_with_wc_custom_order_tables' );

		$this->loader->add_action( 'wp_ajax_uimfwc_save_product', $plugin_admin, 'save_product' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since     1.0.0
	 * @access    private
	 * @return    void
	 */
	private function define_public_hooks() {
		$plugin_public = new Uimfwc_Unified_Inventory_Manager_For_Wc_Public( $this->get_plugin_name(), $this->get_version() );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @return    void
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of WordPress.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @return    string The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @return    Uimfwc_Unified_Inventory_Manager_For_Wc_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @access    public
	 * @return    string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
