<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_Payment_Gateway_Bkt extends WC_Payment_Gateway
{
	public $version;
	protected $data_to_send = array();

	public function __construct()
	{
		$this->version 		= WC_GATEWAY_BKT_VERSION;
		$this->id 			= 'bkt';
		$this->method_title	= __('Credit Card, BKT ( Banka Kombetare Tregetare )', 'woocommerce-bkt');

		$this->method_description = sprintf(
			__('BKT Gateway works by sending the user to %1$sBKT%2$s to enter their payment information.', 'woocommerce-bkt'),
			'<a href="http://bkt.com.al/">',
			'</a>'
		);

		$this->icon               	= WP_PLUGIN_URL . '/' . plugin_basename(dirname(dirname(__FILE__))) . '/images/icon.png';
		$this->debug_email        	= get_option('admin_email');
		$this->available_countries  = array('AL');
		$this->available_currencies = array('ALL', 'USD', 'EUR', 'TL');

		// Supported functionality
		$this->supports = array('products');

		$this->init_form_fields();
		$this->init_settings();

		// Setup default merchant data.
		$this->member_id			= $this->get_option('member_id');
		$this->merchant_id      	= $this->get_option('merchant_id');
		$this->merchant_pass     	= $this->get_option('merchant_pass');
		$this->user_code			= $this->get_option('user_code');
		$this->user_pass			= $this->get_option('user_pass');
		$this->secure_type 			= $this->get_option('secure_type');
		$this->transaction_type		= $this->get_option('transaction_type');
		$this->installment_count	= $this->get_option('installment_count');
		$this->currency				= $this->get_option('currency');
		$this->lang					= $this->get_option('lang');
		$this->template_type		= $this->get_option('template_type');
		$this->random_number		= microtime();

		$this->payment_post_url		= $this->get_option('payment_post_url');
		$this->order_inquiry_url	= $this->get_option('order_inquiry_url');

		$this->title            	= $this->get_option('title');
		$this->response_url	    	= add_query_arg('wc-api', 'WC_Gateway_BKT', home_url('/'));
		$this->send_debug_email 	= 'yes' === $this->get_option('send_debug_email');
		$this->description      	= $this->get_option('description');
		$this->enabled          	= $this->is_valid_for_use() ? 'yes' : 'no';
		$this->enable_logging   	= 'yes' === $this->get_option('enable_logging');

		add_action('woocommerce_api_wc_gateway_bkt', array($this, 'check_bank_response'));
		add_action('woocommerce_receipt_bkt', array($this, 'receipt_page'));
		add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
		add_filter('woocommerce_email_attachments', array($this, 'attach_invoice_pdf_to_email'), 300, 3);
	}

	public function attach_invoice_pdf_to_email($attachments, $type, $object)
	{
		if (
			in_array($type, ['customer_processing_order', 'customer_invoice'])
			&& $object instanceof WC_Order
			&& $object->get_payment_method() === $this->id
		) {
			$invoice = (new WC_Bkt_Invoice($object))->maybe_upload_and_save_to_the_order();
			$attachments[] = $invoice['path'];
		}

		return $attachments;
	}

	public function is_application_error($error_code)
	{
		if (array_key_exists($error_code,  WC_Bkt_Config::application_error_codes())) {
			return true;
		}

		return false;
	}

	public function get_bank_error_message($error_code)
	{
		$error_message = WC_Bkt_Config::bkt_response_codes($error_code);
		if (is_string($error_message))
			return $error_message;

		$error_message =  WC_Bkt_Config::application_error_codes($error_code);
		if (is_string($error_message))
			return $error_message;

		return false;
	}

	public function log($message)
	{
		if ('yes' === $this->get_option('testmode') || $this->enable_logging) {
			if (empty($this->logger)) {
				$this->logger = new WC_Logger();
			}
			$this->logger->add('bkt', $message);
		}
	}

	public function check_bank_response()
	{
		$data = stripslashes_deep($_POST);
		$this->log("\n" . '----------' . "\n" . 'BKT call received');
		$this->log('BKT Data: ' . print_r($data, true));
		$redirect_url   = home_url('/');

		$order_id       = absint($data['order_id']);
		$order          = wc_get_order($order_id);

		if (!$order || false === $data) {
			$this->log(__('Bad access of page', 'woocommerce-bkt'));
			wp_redirect($redirect_url);
			die;
		}

		if ($data['3DStatus'] != '1')
			$this->log(__('3D User Authentication Failed', 'woocommerce-bkt'));

		if ($data['ProcReturnCode'] == '00') {

			$order->add_order_note(__('Payment completed', 'woocommerce-bkt'), true);
			$this->log(__('Payment completed', 'woocommerce-bkt'));

			// Add order meta
			update_post_meta($order->get_id(), '_bkt_status', 'Approved');
			update_post_meta($order->get_id(), '_bkt_transaction_id', $data['AuthCode']);
			update_post_meta($order->get_id(), '_bkt_transaction_card_type', $data['CardType']);
			update_post_meta($order->get_id(), '_bkt_card_mask', $data['CardMask']);
			update_post_meta($order->get_id(), '_bkt_transaction_date', $data['ReqDate']);

			$order->payment_complete();
			$redirect_url = $this->get_return_url($order);
		} else {

			$message = $this->get_bank_error_message($data['ProcReturnCode']);

			$this->log($message);
			$order->add_order_note($message);

			if (!$this->is_application_error($data['ProcReturnCode'])) {
				$order->add_order_note($message, true);
			}

			$order->update_status('failed', $message);
			$redirect_url = $this->get_return_url($order);
		}

		wp_redirect($redirect_url);
		die;
	}

	public function init_form_fields()
	{
		$this->form_fields = include __DIR__ . '/bkt-payment-form-fields.php';
	}

	public function is_valid_for_use()
	{

		$is_available          = false;
		$is_available_currency = in_array(get_woocommerce_currency(), $this->available_currencies);

		if ($is_available_currency && $this->merchant_id && $this->merchant_pass) {
			$is_available = true;
		}

		return $is_available;
	}

	public function receipt_page($order)
	{
		echo '<p>' . __('Thank you for your order, please click the button below to pay with BKT.', 'woocommerce-bkt') . '</p>';
		echo $this->generate_bkt_form($order);
	}

	public function set_hash($order_id)
	{

		$order = wc_get_order($order_id);

		if (!$order)
			return;

		$hashstr = implode('', [
			$this->member_id,
			$order->get_order_number(),
			$order->get_total(),
			$this->response_url,
			$this->response_url,
			$this->transaction_type,
			$this->installment_count,
			$this->random_number,
			$this->merchant_pass
		]);

		$hash = base64_encode(pack('H*', sha1($hashstr)));
		return $hash;
	}

	public function generate_bkt_form($order_id)
	{

		$order = wc_get_order($order_id);

		// Construct variables for post
		$this->data_to_send = array(
			'MbrId'						=> $this->member_id,
			'MerchantID'				=> $this->merchant_id,
			'UserCode'					=> $this->user_code,
			'UserPass'					=> $this->user_pass,
			'SecureType'				=> $this->secure_type,
			'TxnType'					=> $this->transaction_type,
			'InstallmentCount'			=> $this->installment_count,
			'Currency'					=> $this->currency,
			'OkUrl'						=> $this->response_url,
			'FailUrl'					=> $this->response_url,
			'Rnd'						=> $this->random_number,
			'Lang'						=> $this->lang,
			'TemplateType'				=> $this->template_type,

			'OrderId'					=> $order->get_order_number(),
			'OrgOrderId'				=> '',
			'PurchAmount'				=> $order->get_total(),

			'ShippingNameSurname'		=> sprintf('%s %s', self::get_order_prop($order, 'shipping_first_name'), self::get_order_prop($order, 'shipping_last_name')),
			'ShippingEmail'				=> '',
			'ShippingPhone'				=> '',
			'ShippingNationalId'		=> self::get_order_prop($order, 'shipping_country'),
			'ShippingCompanyName'		=> self::get_order_prop($order, 'shipping_company'),
			'ShippingTaxOffice'			=> self::get_order_prop($order, 'shipping_first_name'),
			'ShippingTaxNo'				=> self::get_order_prop($order, 'shipping_first_name'),
			'ShippingAddress'			=> self::get_order_prop($order, 'shipping_address_1'),
			'ShippingTown'				=> self::get_order_prop($order, 'shipping_state'),
			'ShippingCity'				=> self::get_order_prop($order, 'shipping_city'),
			'ShippingZipCode'			=> self::get_order_prop($order, 'shipping_postcode'),
			'ShippingCountry'			=> self::get_order_prop($order, 'shipping_country'),

			'BillingNameSurname'		=> sprintf('%s %s', self::get_order_prop($order, 'billing_first_name'), self::get_order_prop($order, 'billing_last_name')),
			'BillingEmail'				=> self::get_order_prop($order, 'billing_email'),
			'BillingPhone'				=> self::get_order_prop($order, 'billing_phone'),
			'BillingNationalId'			=> self::get_order_prop($order, 'billing_country'),
			'BillingCompanyName'		=> self::get_order_prop($order, 'billing_company'),
			'BillingTaxOffice'			=> self::get_order_prop($order, 'billing_first_name'),
			'BillingTaxNo'				=> self::get_order_prop($order, 'billing_first_name'),
			'BillingAddress'			=> self::get_order_prop($order, 'billing_address_1'),
			'BillingTown'				=> self::get_order_prop($order, 'billing_state'),
			'BillingCity'				=> self::get_order_prop($order, 'billing_city'),
			'BillingZipCode'			=> self::get_order_prop($order, 'billing_postcode'),
			'BillingCountry'			=> self::get_order_prop($order, 'billing_country'),

			'Hash'						=> $this->set_hash($order->get_order_number()),

			'item_description' 			=> sprintf(__('New order from %s', 'woocommerce-bkt'), get_bloginfo('name')),
			'order_key'      			=> self::get_order_prop($order, 'order_key'),
			'script_version'      		=> 'WooCommerce/' . WC_VERSION . '; ' . get_site_url(),
			'order_id'      			=> self::get_order_prop($order, 'id'),
			'source'           			=> 'WooCommerce_Bkt_Plugin_' . $this->version,

		);

		$_order_args = array();
		foreach ($this->data_to_send as $key => $value) {
			$_order_args[] = '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
		}

		return '<form action="' . esc_url($this->payment_post_url) . '" method="post" id="payfast_payment_form">
				' . implode('', $_order_args) . '
				<input type="submit" class="button button-alt" id="submit_bkt_payment_form" value="' . __('Pay via BKT', 'woocommerce-bkt') . '" /> 
				<a class="button cancel" href="' . $order->get_cancel_order_url() . '">' . __('Cancel order &amp; restore cart', 'woocommerce-bkt') . '</a>
				<script type="text/javascript">
					jQuery(function(){
						jQuery("body").block(
							{
								message: "' . __('Thank you for your order. We are now redirecting you to BKT to make payment.', 'woocommerce-bkt') . '",
								overlayCSS:
								{
									background: "#fff",
									opacity: 0.6
								},
								css: {
									padding:        20,
									textAlign:      "center",
									color:          "#555",
									border:         "3px solid #aaa",
									backgroundColor:"#fff",
									cursor:         "wait"
								}
							});
						jQuery( "#submit_bkt_payment_form" ).click();
					});
				</script>
			</form>';
	}

	function process_payment($order_id)
	{
		$order = wc_get_order($order_id);
		return array(
			'result' 	 => 'success',
			'redirect'	 => $order->get_checkout_payment_url(true),
		);
	}

	public static function get_order_prop($order, $prop)
	{
		switch ($prop) {
			case 'order_total':
				$getter = array($order, 'get_total');
				break;
			default:
				$getter = array($order, 'get_' . $prop);
				break;
		}

		return is_callable($getter) ? call_user_func($getter) : $order->{$prop};
	}
}
