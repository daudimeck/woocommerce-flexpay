<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://unqode.com
 * @since      1.0.0
 *
 * @package    Woo_Flexpay
 * @subpackage Woo_Flexpay/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woo_Flexpay
 * @subpackage Woo_Flexpay/public
 * @author     David Meck <meckamsee@gmail.com>
 */
class Woo_Flexpay_Public
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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{


		wp_enqueue_style('font-awesome', '//stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', array(), null, 'all');

		wp_enqueue_style('woo-flexpay', plugin_dir_url(__FILE__) . 'css/woo-flexpay-public.css', array(), $this->version, 'all');

		// wp_register_style($this->plugin_name . '-inline-style', false);
		// wp_enqueue_style($this->plugin_name . '-inline-style', false);
		// $custom_css = get_option('flexpay_button_css', '');

		wp_add_inline_style('woo-flexpay', get_option('flexpay_button_css'));
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script('jquery_modal', plugin_dir_url(__FILE__) . 'js/jquery.modal.js', array('jquery'));
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/woo-flexpay-public.js', array('jquery'), $this->version, false);

		wp_localize_script($this->plugin_name, 'vars', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'end_point' => get_option('flex_environment') === 'staging' ? FLEX_SANDBOX_ENDPOINT : FLEX_LIVE_ENDPOINT,
			'plugin_url' => plugin_dir_url(dirname(__FILE__))
		]);
	}
}


class frontend_display
{
	public function loadProduct()
	{

		global $WCFM;


		if (!$_GET['product']) {
			//info missing
			exit(wp_send_json_error('Product id Missing', 422));
		}

		$_product = wc_get_product($_GET['product']);


		if (!$_product->managing_stock() && !$_product->is_in_stock()) {
			exit(wp_send_json_error('Product is out of stock', 422));
		}

		$price = $_product->get_price();
		$title = $_product->get_title();

		var_dump((class_exists('WCV_Vendor_Shop') && method_exists('WCV_Vendor_Shop', 'template_loop_sold_by')));

		if (class_exists('WCV_Vendor_Shop') && method_exists('WCV_Vendor_Shop', 'template_loop_sold_by')) {

			if (get_option('wcvendors_display_label_sold_by_enable') == 'yes') {
				ob_start();
				echo '<div class="mf-summary-meta">';
				WCV_Vendor_Shop::template_loop_sold_by($_product->get_id());
				echo '</div>';
				$output = ob_get_clean();

				$title += $output;
			}
		}

		$image = $_product->get_image('woocommerce_thumbnail', ['class' => 'product_image']);


		if ($_product->is_type('variable') && !$_GET['variation']) {
			exit(wp_send_json_error('Please choose a variation', 422));
		}

		if ($_product->is_type('variable')) {
			// $_product = new WC_Product_Variable($_GET['product']);
			$p = new WC_Product_Variation($_GET['variation']);

			$image = wp_get_attachment_image($p->get_image_id(), 'woocommerce_thumbnail', false,  ['class' => 'product_image']);
			$attributes = implode(',', $p->get_variation_attributes());

			$title .= ' : ' . $attributes;

			$price = $p->get_price();
		}
?>

		<div class="flexpay">
			<div class="row">
				<div class="column product_image">

					<div class="image">
						<?= $image ?>


					</div>
				</div>
				<div class="column description">
					<h2><?= $title ?></h2>

					<div class="price">
						<?= get_woocommerce_currency_symbol(); ?>
						<?= $price; ?>
					</div>

					<h4 class="intro_header"><?= get_option('flexpay_header_text') ?></h4>
					<span class="intro_description">
						<?= get_option('flexpay_header_description') ?>
					</span>

					<form action="" class="lpp_booking" method="post">
						<input type="hidden" name="product" value="<?= $_GET['product']; ?>">
						<input type="hidden" name="variation" value="<?= $_GET['variation']; ?>">
						<div class="grid">
							<label for="fullname">Full name
								<input type="text" class="flex_field" placeholder="Full Name:(e.g John Smith)" name="fullname">
							</label>
							<label for="email">Email
								<input type="email" class="flex_field" required placeholder="Your Email" name="email">
							</label>
						</div>
						<label for="phone">Phone Number
							<input type="text" class="flex_field" required placeholder="Phone Number e.g 0712***" name="phone">
						</label>

						<label for="initial_deposit">Initial Deposit
							<span>Kshs<input type="number" class="flex_field" required placeholder="Initial deposit to kickstart atleast Ksh 500" name="initial_deposit"></span>
						</label>
						<label for="terms">
							<input type="checkbox" name="terms" required style="width: auto; display:inline" />
							I Have accepted the <a href="https://www.flexpay.co.ke/terms-conditions/">Terms and Conditions</a>
						</label>
						<div class="links">
							<button type="submit">Make Booking</button>
							<a href="#close-modal" rel="modal:close">Close</a>
						</div>
					</form>

					<a href="https://flexpay.co.ke" class="powered-by" style="color:#155594;font-size:11px;float:right;display:inline-block;margin-top:10px">Powered by Flexpay</a>
				</div>
			</div>
		</div>

<?php
		exit;
	}

	public function lppMakeBooking()
	{


		if (!$_POST['product']) {
			//info missing
			exit(wp_send_json_error('Product id Missing', 422));
		}

		$_product = wc_get_product($_POST['product']);

		if (get_option('flex_lpp_auto_insert') === 'yes' && get_post_meta($_product->ID, 'disable_lpp_button') === 'yes') {
			exit(wp_send_json_error('Flexpay Lipia Pole Pole not allowed on this product', 422));
		}

		if (get_option('flex_lpp_auto_insert') !== 'yes' && get_post_meta($_product->ID, 'enable_lpp_button') === 'no') {
			exit(wp_send_json_error('Flexpay Lipia Pole Pole not allowed on this product', 422));
		}
		if (is_nan(intval($_POST['initial_deposit'])) || intval($_POST['initial_deposit']) > $_product->get_price()) {
			exit(wp_send_json_error('Initial deposit can not be greater than the price', 422));
		}

		$name = explode(' ', $_POST['fullname']);

		$title = $_product->get_title();
		$price = $_product->get_price();

		if ($_product->is_type('variable')) {
			if (!$_POST['variation']) {
				exit(wp_send_json_error('Variation is required', 422));
			}
			$p = new WC_Product_Variation($_POST['variation']);
			$attributes = implode(',', $p->get_variation_attributes());

			$title .= ' : ' . $attributes;
			$price = $p->get_price();
		}

		if (!$name[1]) {
			$name[1] = '';
		}

		$apiKey = get_option('flexpay_api_key');
		$apiSecret = get_option('flexpay_api_secret');

		global $WCFM;
		if ($WCFM) {
			$vendor_id = $WCFM->wcfm_vendor_support->wcfm_get_vendor_id_from_product($_POST['product']);

			$profileInfo = (get_user_meta($vendor_id, 'wcfmvm_custom_infos'));


			if (is_array($profileInfo) && $profileInfo[0]  && $profileInfo[0]['api-key'] && $profileInfo[0]['api-secret']) {

				// exit(wp_send_json_error('API Key and secret not specificied for this vendor. Please complete setup', 422));
				$apiKey = $profileInfo[0]['api-key'];
				$apiSecret = $profileInfo[0]['api-secret'];
			}
		}

		echo json_encode([
			'apiKey' => $apiKey,
			'apiSecret' => $apiSecret,
			'productName' => $title,
			'productPrice' =>  $price,
			'phoneNumber' => $_POST['phone'],
			'productDeposit' => $_POST['initial_deposit'],
			'bookingDays' => get_option('flexpay_booking_days'),
			'paymentType' => 'MPESA',
			'email' => $_POST['email'],
			'firstName' => $name[0],
			'lastName' => $name[1],
		]);

		exit;
	}
}
