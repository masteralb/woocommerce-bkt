<?php
class WC_Gateway_Bkt extends WC_Payment_Gateway {

	/**
	 * Version
	 *
	 * @var string
	 */
	public $version;

	/**
	 * @access protected
	 * @var array $data_to_send
	 */
	protected $data_to_send = array();

	public function __construct() {
		
		$this->version 		= WC_GATEWAY_BKT_VERSION;
		$this->id 			= 'bkt';
		$this->method_title	= __( 'BKT ( Banka Kombetare Tregetare )', 'woocommerce-bkt' );

		$this->method_description = sprintf( 
			__( 'BKT Gateway works by sending the user to %1$sBKT%2$s to enter their payment information.', 'woocommerce-bkt' ), 
			'<a href="http://bkt.com.al/">', '</a>' 
		);
		
		$this->icon               	= WP_PLUGIN_URL . '/' . plugin_basename( dirname( dirname( __FILE__ ) ) ) . '/images/icon.png';
		$this->debug_email        	= get_option( 'admin_email' );
		$this->available_countries  = array( 'AL' );
		$this->available_currencies = array( 'ALL', 'USD', 'EUR', 'TL' );

		// Supported functionality
		$this->supports = array( 'products' );

		$this->init_form_fields();
		$this->init_settings();

		// Setup default merchant data.
		$this->member_id			= $this->get_option( 'member_id' );
		$this->merchant_id      	= $this->get_option( 'merchant_id' );
		$this->merchant_pass     	= $this->get_option( 'merchant_pass' );
		$this->user_code			= $this->get_option( 'user_code' );
		$this->user_pass			= $this->get_option( 'user_pass' );		
		$this->secure_type 			= $this->get_option( 'secure_type' );
		$this->transaction_type		= $this->get_option( 'transaction_type' );
		$this->installment_count	= $this->get_option( 'installment_count' );
		$this->currency				= $this->get_option( 'currency' );
		$this->lang					= $this->get_option( 'lang' );
		$this->template_type		= $this->get_option( 'template_type' );
		$this->random_number		= microtime();

		$this->payment_post_url		= $this->get_option( 'payment_post_url' );
		$this->order_inquiry_url	= $this->get_option( 'order_inquiry_url' );		
		
		$this->title            = $this->get_option( 'title' );
		$this->response_url	    = add_query_arg( 'wc-api', 'WC_Gateway_BKT', home_url( '/' ) );
		$this->send_debug_email = 'yes' === $this->get_option( 'send_debug_email' );
		$this->description      = $this->get_option( 'description' );
		$this->enabled          = $this->is_valid_for_use() ? 'yes': 'no'; // Check if the base currency supports this gateway.
		$this->enable_logging   = 'yes' === $this->get_option( 'enable_logging' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_receipt_bkt', array( $this, 'receipt_page' ) );

		/*
		if ( 'yes' === $this->get_option( 'testmode' ) ) {
			$this->url          = 'https://sandbox.payfast.co.za/eng/process?aff=woo-free';
			$this->validate_url = 'https://sandbox.payfast.co.za/eng/query/validate';
			$this->add_testmode_admin_settings_notice();
		} else {
			$this->send_debug_email = false;
		}

		add_action( 'woocommerce_api_wc_gateway_payfast', array( $this, 'check_itn_response' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_receipt_payfast', array( $this, 'receipt_page' ) );
		add_action( 'woocommerce_scheduled_subscription_payment_' . $this->id, array( $this, 'scheduled_subscription_payment' ), 10, 2 );
		add_action( 'woocommerce_subscription_status_cancelled', array( $this, 'cancel_subscription_listener' ) );
		add_action( 'wc_pre_orders_process_pre_order_completion_payment_' . $this->id, array( $this, 'process_pre_order_payments' ) );
		*/
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 *
	 * @since 1.0.0
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			
			'enabled' => array(
				'title'       => __( 'Enable/Disable', 'woocommerce-bkt' ),
				'label'       => __( 'Enable BKT', 'woocommerce-bkt' ),
				'type'        => 'checkbox',
				'description' => __( 'This controls whether or not this gateway is enabled within WooCommerce.', 'woocommerce-bkt' ),
				'default'     => 'yes',
				'desc_tip'    => true,
			),

			'title' => array(
				'title'       => __( 'Title', 'woocommerce-bkt' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-bkt' ),
				'default'     => __( 'BKT', 'woocommerce-bkt' ),
				'desc_tip'    => true,
			),

			'description' => array(
				'title'       => __( 'Description', 'woocommerce-bkt' ),
				'type'        => 'text',
				'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-bkt' ),
				'default'     => '',
				'desc_tip'    => true,
			),

			'testmode' => array(
				'title'       => __( 'BKT Sandbox', 'woocommerce-bkt' ),
				'type'        => 'checkbox',
				'description' => __( 'Place the payment gateway in development mode.', 'woocommerce-bkt' ),
				'default'     => 'yes',
			),

			'lang' => array(
				'title'       => __( 'Language', 'woocommerce-bkt' ),
				'type'        => 'text',
				'description' => __( 'User Language information. (Turkish: TR, English: EN)', 'woocommerce-bkt' ),
				'default'     => 'EN',
			),


			'member_id' => array(
				'title'       => __( 'Member ID', 'woocommerce-bkt' ),
				'type'        => 'text',
				'description' => __( 'This is the member ID, received from BKT.', 'woocommerce-bkt' ),
				'default'     => '9',
			),
			
			'merchant_id' => array(
				'title'       => __( 'Merchant ID', 'woocommerce-bkt' ),
				'type'        => 'text',
				'description' => __( 'This is the merchant ID, received from BKT.', 'woocommerce-bkt' ),
				'default'     => '',
			),
			
			'merchant_pass' => array(
				'title'       => __( 'Merchant Password', 'woocommerce-bkt' ),
				'type'        => 'text',
				'description' => __( 'This is the merchant password, received from BKT.', 'woocommerce-bkt' ),
				'default'     => '',
			),

			'user_code' => array(
				'title'       => __( 'User code', 'woocommerce-bkt' ),
				'type'        => 'text',
				'description' => __( 'This is the user code, received from BKT.', 'woocommerce-bkt' ),
				'default'     => '',
			),

			'user_pass' => array(
				'title'       => __( 'User password', 'woocommerce-bkt' ),
				'type'        => 'password',
				'description' => __( 'This is the user password, received from BKT.', 'woocommerce-bkt' ),
				'default'     => '',
			),

			'user_pass' => array(
				'title'       => __( 'User password', 'woocommerce-bkt' ),
				'type'        => 'text',
				'description' => __( 'This is the user password, received from BKT.', 'woocommerce-bkt' ),
				'default'     => '',
			),

			'secure_type' => array(
				'title'       => __( 'Secure type', 'woocommerce-bkt' ),
				'type'        => 'text',
				'description' => __( 'Represents the Security type of the transaction. ( NonSecure, 3Dpay, 3DModel, 3DHost )', 'woocommerce-bkt' ),
				'default'     => '3DHost',
			),

			'transaction_type' => array(
				'title'       => __( 'Translation type', 'woocommerce-bkt' ),
				'type'        => 'text',
				'description' => __( 'Transaction type. “Auth” should be sent for provision. ( Sales:Auth, PreAuthorization:PreAuth, PreAuthorizationClosing:PostAuth, Refund, Void, PointInquiry, OrderInquiry:OrderInq, BatchClose)', 'woocommerce-bkt' ),
				'default'     => 'Auth',
			),
			
			'installment_count' => array(
				'title'       => __( 'Installment count', 'woocommerce-bkt' ),
				'type'        => 'number',
				'description' => __( 'Represents the number of installments. This number should be greater than 1 if this transaction is to be accepted as a transaction with installment. If this is a number smaller than 0 or a non-numeric symbol, this number is returned as 0', 'woocommerce-bkt' ),
				'default'     => '0',
			),

			'currency' => array(
				'title'       => __( 'Currency', 'woocommerce-bkt' ),
				'type'        => 'number',
				'description' => __( '949 = TL, 840 = USD, 978 = EUR, 8 = ALL', 'woocommerce-bkt' ),
				'default'     => '978',
			),
			
			'send_debug_email' => array(
				'title'   => __( 'Send Debug Emails', 'woocommerce-bkt' ),
				'type'    => 'checkbox',
				'label'   => __( 'Send debug e-mails for transactions through the BKT gateway (sends on successful transaction as well).', 'woocommerce-bkt' ),
				'default' => 'yes',
			),

			'debug_email' => array(
				'title'       => __( 'Who Receives Debug E-mails?', 'woocommerce-bkt' ),
				'type'        => 'text',
				'description' => __( 'The e-mail address to which debugging error e-mails are sent when in test mode.', 'woocommerce-bkt' ),
				'default'     => get_option( 'admin_email' ),
			),

			'enable_logging' => array(
				'title'   => __( 'Enable Logging', 'woocommerce-bkt' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable transaction logging for gateway.', 'woocommerce-bkt' ),
				'default' => 'no',
			),

			'template_type' => array(
				'title'   => __( 'Template type', 'woocommerce-bkt' ),
				'type'    => 'number',
				'description'   => __( 'Template related with Billing and Shipping', 'woocommerce-bkt' ),
				'default' => 1,
			),

			'payment_post_url' => array(
				'title'   => __( 'Payment post url', 'woocommerce-bkt' ),
				'type'    => 'text',
				'description'   => __( 'Post url', 'woocommerce-bkt' ),
				'default' => 'https://payfortestbkt.cordisnetwork.com/Mpi/3DHost.aspx',
			),

			'order_inquiry_url' => array(
				'title'   => __( 'Order inquiry url', 'woocommerce-bkt' ),
				'type'    => 'text',
				'description'   => __( 'Inquiry url', 'woocommerce-bkt' ),
				'default' => 'https://payfortestbkt.cordisnetwork.com/Mpi/Default.aspx',
			),

			

			
		);
	}

	public function is_valid_for_use() {
		
		$is_available          = false;
		$is_available_currency = in_array( get_woocommerce_currency(), $this->available_currencies );

		if ( $is_available_currency && $this->merchant_id && $this->merchant_pass ) {
			$is_available = true;
		}

		return $is_available;
	}

	/**
	 * Reciept page.
	 *
	 * Display text and a button to direct the user to PayFast.
	 *
	 * @since 1.0.0
	 */
	public function receipt_page( $order ) {
		echo '<p>' . __( 'Thank you for your order, please click the button below to pay with BKT.', 'woocommerce-bkt' ) . '</p>';
		echo $this->generate_bkt_form( $order );
	}


	public function set_hash( $order_id ){
		
		$order = wc_get_order( $order_id );

		$hashstr = implode('',[
			$this->member_id, 
			$order->get_order_number(),
			$order->get_total(),
			$this->get_return_url( $order ),
			$this->get_return_url( $order ),
			$this->transaction_type,
			$this->installment_count,
			$this->random_number,
			$this->merchant_pass
		]);

		$hash = base64_encode( pack( 'H*', sha1( $hashstr ) ) );
		return $hash;
	}


	/**
	 * Generate the PayFast button link.
	 *
	 * @since 1.0.0
	 */
	public function generate_bkt_form( $order_id ) {
		
		$order = wc_get_order( $order_id );
		
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
			'OkUrl'						=> $this->get_return_url( $order ),
			'FailUrl'					=> $this->get_return_url( $order ),
			'Rnd'						=> $this->random_number,
			'Lang'						=> $this->lang,
			'TemplateType'				=> $this->template_type,
			
			'OrderId'					=> $order->get_order_number(),
			'OrgOrderId'				=> '',
			'PurchAmount'				=> $order->get_total(),
			
			'ShippingNameSurname'		=> sprintf( '%s %s', self::get_order_prop( $order, 'shipping_first_name' ) , self::get_order_prop( $order, 'shipping_last_name' ) ),
			'ShippingEmail'				=> '',
			'ShippingPhone'				=> '',
			'ShippingNationalId'		=> self::get_order_prop( $order, 'shipping_country' ),
			'ShippingCompanyName'		=> self::get_order_prop( $order, 'shipping_company' ),
			'ShippingTaxOffice'			=> self::get_order_prop( $order, 'shipping_first_name' ),
			'ShippingTaxNo'				=> self::get_order_prop( $order, 'shipping_first_name' ),
			'ShippingAddress'			=> self::get_order_prop( $order, 'shipping_address_1' ),
			'ShippingTown'				=> self::get_order_prop( $order, 'shipping_state' ),
			'ShippingCity'				=> self::get_order_prop( $order, 'shipping_city' ),
			'ShippingZipCode'			=> self::get_order_prop( $order, 'shipping_postcode' ),
			'ShippingCountry'			=> self::get_order_prop( $order, 'shipping_country' ),

			'BillingNameSurname'		=> sprintf( '%s %s', self::get_order_prop( $order, 'billing_first_name' ) , self::get_order_prop( $order, 'billing_last_name' ) ),
			'BillingEmail'				=> self::get_order_prop( $order, 'billing_email' ),
			'BillingPhone'				=> self::get_order_prop( $order, 'billing_phone' ),
			'BillingNationalId'			=> self::get_order_prop( $order, 'billing_country' ),
			'BillingCompanyName'		=> self::get_order_prop( $order, 'billing_company' ),
			'BillingTaxOffice'			=> self::get_order_prop( $order, 'billing_first_name' ),
			'BillingTaxNo'				=> self::get_order_prop( $order, 'billing_first_name' ),
			'BillingAddress'			=> self::get_order_prop( $order, 'billing_address_1' ),
			'BillingTown'				=> self::get_order_prop( $order, 'billing_state' ),
			'BillingCity'				=> self::get_order_prop( $order, 'billing_city' ),
			'BillingZipCode'			=> self::get_order_prop( $order, 'billing_postcode' ),
			'BillingCountry'			=> self::get_order_prop( $order, 'billing_country' ),
			'Hash'						=> $this->set_hash( $order->get_order_number() ),

			'item_description' 			=> sprintf( __( 'New order from %s', 'woocommerce-bkt' ), get_bloginfo( 'name' ) ),
			'order_key'      			=> self::get_order_prop( $order, 'order_key' ),
			'script_version'      		=> 'WooCommerce/' . WC_VERSION . '; ' . get_site_url(),
			'order_id'      			=> self::get_order_prop( $order, 'id' ),
			'source'           			=> 'WooCommerce_Bkt_Plugin_' . $this->version,

		);

		$_order_args = array();
		foreach ( $this->data_to_send as $key => $value ) {
			$_order_args[] = '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
		}

		return '<form action="' . esc_url( $this->payment_post_url ) . '" method="post" id="payfast_payment_form">
				' . implode( '', $_order_args ) . '
				<input type="submit" class="button-alt" id="submit_bkt_payment_form" value="' . __( 'Pay via BKT', 'woocommerce-bkt' ) . '" /> 
				<a class="button cancel" href="' . $order->get_cancel_order_url() . '">' . __( 'Cancel order &amp; restore cart', 'woocommerce-bkt' ) . '</a>
				<script type="text/javascript">
					jQuery(function(){
						jQuery("body").block(
							{
								message: "' . __( 'Thank you for your order. We are now redirecting you to BKT to make payment.', 'woocommerce-bkt' ) . '",
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

	function process_payment( $order_id ) {
	    
		$order = wc_get_order( $order_id );
		return array(
			'result' 	 => 'success',
			'redirect'	 => $order->get_checkout_payment_url( true ),
		);

	}

	public static function get_order_prop( $order, $prop ) {
		switch ( $prop ) {
			case 'order_total':
				$getter = array( $order, 'get_total' );
				break;
			default:
				$getter = array( $order, 'get_' . $prop );
				break;
		}

		return is_callable( $getter ) ? call_user_func( $getter ) : $order->{ $prop };
	}

}