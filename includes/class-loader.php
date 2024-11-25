<?php

namespace WZC;

defined('ABSPATH') || exit;

final class WCZ_Loader
{

	protected static $_instance = null;

	public static function instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	function __construct()
	{

		$this->includes_main();
		$this->run();
		$this->include_shortcode();
		$this->initial_activation();
	}

	// Include Core
	public function includes_main()
	{
		require_once WZC_DIR_PATH . 'includes/class-activator.php';
		require_once WZC_DIR_PATH . 'admin/class-admin.php';
		require_once WZC_DIR_PATH . 'admin/partials/class-pincode-list-table.php';
		new Admin\WZC_Admin();
	}


	public function run()
	{

		$run_setup = new \WZC\Includes\Activator_Plugin();
		/**
		 * check woocommerce plugin installed
		 */

		add_action('admin_init', array($run_setup, 'wzc_check_woocommerce_plugin_active_state'));

		deactivate_plugins(plugin_basename(__FILE__));
	}

	// Include Shortcode
	public function include_shortcode()
	{

		include_once WZC_DIR_PATH . 'frontend/Check_Pincode_Form.php';
		new frontend\Check_Pincode_Form();
	}



	// Activation & Deactivation Hook
	public function initial_activation()
	{
		$create_table = new \WZC\Includes\Activator_Plugin();

		register_activation_hook(WZC_FILE, array($create_table, 'wzc_create_table_store_pincode'));
	}
}
