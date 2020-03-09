<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://unqode.com
 * @since      1.0.0
 *
 * @package    Woo_Flexpay
 * @subpackage Woo_Flexpay/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_Flexpay
 * @subpackage Woo_Flexpay/admin
 * @author     David Meck <meckamsee@gmail.com>
 */
class Woo_Flexpay_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woo_Flexpay_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_Flexpay_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/woo-flexpay-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woo_Flexpay_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_Flexpay_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/woo-flexpay-admin.js', array('jquery'), $this->version, false);
	}

	public function missing_settings()
	{
		if (!get_option('flexpay_api_secret') || !get_option('flexpay_api_key')) {
			$class = 'notice notice-error';
			$message = __('Flexpay Woocommerce API Keys and Secret are not set. Click here to complete setup');

			printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
		}
	}


	public function flexpay_lpp_button()
	{
		if (get_option('flex_lpp_auto_insert') === 'yes') {
			$args = array(
				'id' => 'disable_lpp_button',
				'label' => __('Disable LPP Button', 'woo-flexpay'),
				'class' => 'flex-custom-field',
				'desc_tip' => true,
				// 'description' => __('Disable Lipia Pole Pole on this product', 'woo-flexpay'),
			);
			woocommerce_wp_checkbox($args);
		} else {
			$args = array(
				'id' => 'enable_lpp_button',
				'label' => __('Enable LPP Button', 'woo-flexpay'),
				'class' => 'flex-custom-field',
				// 'desc_tip' => true,
				'description' => __('To enable on all products please set Auto insert in Woocommerce settings', 'woo-flexpay'),
			);
			woocommerce_wp_checkbox($args);
		}
	}

	public function flexpay_save_lpp($post_id ) {
		$product = wc_get_product( $post_id );
		$set = isset( $_POST['disable_lpp_button'] ) ? $_POST['disable_lpp_button'] : '';
		$product->update_meta_data( 'disable_lpp_button',  $set  );
		$product->save();
	}

	public function flexpay_save_lpp_enable($post_id ) {
		$product = wc_get_product( $post_id );
		$set = isset( $_POST['enable_lpp_button'] ) ? $_POST['enable_lpp_button'] : '';
		$product->update_meta_data( 'enable_lpp_button',  $set  );
		$product->save();
	}
}
