<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://unqode.com
 * @since      1.0.0
 *
 * @package    Woo_Flexpay
 * @subpackage Woo_Flexpay/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woo_Flexpay
 * @subpackage Woo_Flexpay/includes
 * @author     David Meck <meckamsee@gmail.com>
 */
class Woo_Flexpay
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Woo_Flexpay_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('WOO_FLEXPAY_VERSION')) {
			$this->version = WOO_FLEXPAY_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'woo-flexpay';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->flex_woo_settings();
		$this->woocommerce_actions();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woo_Flexpay_Loader. Orchestrates the hooks of the plugin.
	 * - Woo_Flexpay_i18n. Defines internationalization functionality.
	 * - Woo_Flexpay_Admin. Defines all hooks for the admin area.
	 * - Woo_Flexpay_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-woo-flexpay-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-woo-flexpay-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-woo-flexpay-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-woo-flexpay-public.php';

		$this->loader = new Woo_Flexpay_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woo_Flexpay_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new Woo_Flexpay_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		$plugin_admin = new Woo_Flexpay_Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

		$this->loader->add_action('wp_ajax_load_product', 'frontend_display', 'loadProduct');
		$this->loader->add_action('wp_ajax_nopriv_load_product', 'frontend_display', 'loadProduct');

		$this->loader->add_action('wp_ajax_lpp_make_booking', 'frontend_display', 'lppMakeBooking');
		$this->loader->add_action('wp_ajax_nopriv_lpp_make_booking', 'frontend_display', 'lppMakeBooking');

		$this->loader->add_action('admin_notices', $plugin_admin, 'missing_settings');

		$this->loader->add_action('woocommerce_product_options_general_product_data', $plugin_admin, 'flexpay_lpp_button');

		$this->loader->add_action('woocommerce_process_product_meta', $plugin_admin, 'flexpay_save_lpp');

		$this->loader->add_action('woocommerce_process_product_meta', $plugin_admin, 'flexpay_save_lpp_enable');
	}

	private function flex_woo_settings()
	{
		$this->loader->add_filter('woocommerce_get_sections_products', 'flexSettings', 'flexpay_product_settings');

		$this->loader->add_filter('woocommerce_get_settings_products', 'flexSettings', 'flexpay_lpp_allsettings', 10, 2);
	}

	private function woocommerce_actions()
	{

		$this->loader->add_action('woocommerce_after_add_to_cart_button', 'flex_actions', 'lpp_button');


		$this->loader->add_action('woocommerce_after_shop_loop_item', 'flex_actions', 'lpp_button');
		// $this->loader->add_action( 'woocommerce_after_add_to_cart_button', 'flex_actions', 'lpp_button');

	}




	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new Woo_Flexpay_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
	}


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Woo_Flexpay_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}

class flexSettings
{
	function flexpay_product_settings($sections)
	{
		$sections['flex_lpp_button'] = __('Flexpay Lipia Pole Pole Button', 'woo-flexpay');
		return $sections;
	}

	function flexpay_lpp_allsettings($settings, $current_section)
	{
		if ($current_section == 'flex_lpp_button') {
			$settings_slider = array();
			// Add Title to the Settings
			$settings_slider[] = array('name' => __('Flexpay Lipia Pole Pole Product Setting', 'flexpay'), 'type' => 'title', 'desc' => __('The following options are used to configure Flexpay Button', 'flexpay'), 'id' => 'flex_lpp');
			$settings_slider[] = array(
				'name'     => __('Auto Insert button in a single product', 'flexpay'),
				'desc_tip' => __('This will automatically insert your LPP button in the product', 'flexpay'),
				'id'       => 'flex_lpp_auto_insert',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __('Enable Auto-Insert', 'flexpay'),
			);


			// Add second text field option
			$settings_slider[] = array(
				'name'     => __('Flexpay Button Icon', 'flexpay'),
				'desc_tip' => __('Font awesome icon class e.g "wheelchair"', 'flexpay'),
				'default'  => __('', 'flexpay'),
				'id'       => 'flexpay_btn_icon',
				'type'     => 'text',
			);




			$settings_slider[] = array(
				'name'     => __('Flexpay Button', 'flexpay'),
				'desc_tip' => __('Change the button label', 'flexpay'),
				'default'  => __('Flexpay Lipia Pole Pole', 'flexpay'),
				'id'       => 'flexpay_btn_title',
				'type'     => 'text',
				'desc'     => __('Any title you want can be added to your flexpay button', 'flexpay'),
			);


			$settings_slider[] = array(
				'name'     => __('Show in archives', 'flexpay'),
				// 'desc_tip' => __('Show in archives page', 'flexpay'),
				'default'  => __('yes', 'flexpay'),
				'id'       => 'flexpay_btn_archives',
				'type'     => 'checkbox',
				'desc'     => __('Show', 'flexpay'),
			);


			$settings_slider[] = array(
				'name'     => __('Show in Front Page', 'flexpay'),
				// 'desc_tip' => __('Show in archives page', 'flexpay'),
				'default'  => __('yes', 'flexpay'),
				'id'       => 'flexpay_btn_frontpage',
				'type'     => 'checkbox',
				'desc'     => __('Show', 'flexpay'),
			);


			$settings_slider[] = array(
				'name'     => __('Show in Single Product Page', 'flexpay'),
				// 'desc_tip' => __('Show in archives page', 'flexpay'),
				'default'  => __('yes', 'flexpay'),
				'id'       => 'flexpay_btn_single',
				'type'     => 'checkbox',
				'desc'     => __('Show', 'flexpay'),
			);

			$settings_slider[] = array(
				'name'     => __('Flexpay Booking Days', 'flexpay'),
				'desc_tip' => __('Change the product booking days', 'flexpay'),
				'default'  => __('60', 'flexpay'),
				'id'       => 'flexpay_booking_days',
				'type'     => 'number',
				'desc'     => __('Days', 'flexpay'),
			);

			$settings_slider[] = array(
				'name'     => __('Flexpay API Key', 'flexpay'),
				'desc_tip' => __('Flexpay API Key', 'flexpay'),
				'default'  => __('', 'flexpay'),
				'id'       => 'flexpay_api_key',
				'type'     => 'text',
			);

			$settings_slider[] = array(
				'name'     => __('Flexpay API Secret', 'flexpay'),
				'desc_tip' => __('API Secret', 'flexpay'),
				'default'  => __('', 'flexpay'),
				'id'       => 'flexpay_api_secret',
				'type'     => 'text',
			);

			$settings_slider[] = array(
				'name'     => __('Flexpay Environment', 'flexpay'),
				// 'desc_tip' => __('API', 'flexpay'),
				'default'  => __('staging', 'flexpay'),
				'required' => true,
				'id'       => 'flexpay_environment',
				'type'     => 'radio',
				'options' => [
					'live' => 'Live',
					'staging' => 'Sandbox'
				]
			);


			$settings_slider[] = array(
				'name'     => __('Flexpay Css', 'flexpay'),
				'desc_tip' => __('Flexpay Button CSS override', 'flexpay'),
				'placeholder'  => __('//.home .flex_lpp_btn span{
	//display: none
//}
				   ', 'flexpay'),
				'required' => true,
				'id'       => 'flexpay_button_css',
				'type'     => 'textarea',

			);

			$settings_slider[] = array(
				'name'     => __('Header Text', 'flexpay'),
				'desc_tip' => __('Flexpay Header Text', 'flexpay'),
				'default'  => __('Lipia Polepole at 0% interest', 'flexpay'),
				'id'       => 'flexpay_header_text',
				'type'     => 'text',
			);

			$settings_slider[] = array(
				'name'     => __('Description', 'flexpay'),
				'desc_tip' => __('Flexpay Description Text', 'flexpay'),
				'default'  => __('Flexpay allows you to book an item/service and pay over a period of time maximum 90 days at no extra cost. Start with a minimum deposit of atleast ksh. 500 pay over time and collect/have it delivered after making the full payment.', 'flexpay'),
				'id'       => 'flexpay_header_description',
				'type'     => 'textarea',
			);


			$settings_slider[] = array('type' => 'sectionend', 'id' => 'flex_lpp');
			return $settings_slider;

			/**
			 * If not, return the standard settings
			 **/
		} else {
			return $settings;
		}
	}
}



class flex_actions
{
	public static function custom_css($plugin_name)
	{

		echo get_option('flexpay_button_css');
	}
	public static function showButton()
	{
		$productID = get_the_ID();
		$buttonText = (get_option('flexpay_btn_icon') ? '<i class="fa fa-' . get_option('flexpay_btn_icon') . '"></i>' : '') . (get_option('flexpay_btn_title') ? '<span>' . get_option('flexpay_btn_title') . '</span>' : '');


		if (get_option('flex_lpp_auto_insert') === 'yes') {

			if (get_post_meta($productID, 'disable_lpp_button', true) !== 'yes') {

				echo '<button product_id="' . $productID . '" class="flex_lpp_btn" title="' . get_option('flexpay_btn_title') . '">' . $buttonText . '</button>';
			} else {
			}
		} else {
			//check if enabled on the single product
			if (get_post_meta($productID, 'enable_lpp_button', true) === 'yes') {
				echo '<button product_id="' . $productID . '" class="flex_lpp_btn" title="' . get_option('flexpay_btn_title') . '">' . $buttonText . '</button>';
			}
		}
	}
	public static function lpp_button()
	{



		if (!get_option('flexpay_api_secret') || !get_option('flexpay_api_key')) {
			return false;
		}

		if ((get_option('flexpay_btn_frontpage') === 'yes') && is_front_page()) {
			self::showButton();
		}
		if ((is_archive() || is_product_category()) && (get_option('flexpay_btn_archives') === 'yes')) {
			self::showButton();
		}

		if ((is_product()) && (get_option('flexpay_btn_single') === 'yes')) {
			self::showButton();
		}
	}
}
