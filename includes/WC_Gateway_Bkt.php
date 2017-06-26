<?php
class WC_Gateway_Bkt extends WC_Payment_Gateway {

	
	public $version;
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
		
		$this->title            	= $this->get_option( 'title' );
		$this->response_url	    	= add_query_arg( 'wc-api', 'WC_Gateway_BKT', home_url( '/' ) );
		$this->send_debug_email 	= 'yes' === $this->get_option( 'send_debug_email' );
		$this->description      	= $this->get_option( 'description' );
		$this->enabled          	= $this->is_valid_for_use() ? 'yes': 'no';
		$this->enable_logging   	= 'yes' === $this->get_option( 'enable_logging' );

		add_action( 'woocommerce_api_wc_gateway_bkt', array( $this, 'check_bank_response' ) );
		add_action( 'woocommerce_receipt_bkt', array( $this, 'receipt_page' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	public static function bkt_response_codes( $code = false ){
		
		$response_codes = array(
			'00' => __( 'Approved', 'woocommerce-bkt' ),
			'01' => __( 'Refer to card issuer', 'woocommerce-bkt' ),
			'02' => __( 'Refer to card issuer (Special Conditio,n)', 'woocommerce-bkt' ),
			'03' => __( 'Invalid mercha,nt or service provider', 'woocommerce-bkt' ),
			'04' => __( 'Pick-up card', 'woocommerce-bkt' ),
			'05' => __( 'Not Approved', 'woocommerce-bkt' ),
			'06' => __( 'File Update Error', 'woocommerce-bkt' ),
			'07' => __( 'Pick up card (Special Condition)', 'woocommerce-bkt' ),
			'08' => __( 'Examine Identity', 'woocommerce-bkt' ),
			'09' => __( 'Try again', 'woocommerce-bkt' ),
			'11' => __( 'Approved (VIP)', 'woocommerce-bkt' ),
			'12' => __( 'Invalid Transaction', 'woocommerce-bkt' ),
			'13' => __( 'Invalid amount', 'woocommerce-bkt' ),
			'14' => __( 'Invalid Account Number', 'woocommerce-bkt' ),
			'15' => __( 'No Such Issuer', 'woocommerce-bkt' ),
			'25' => __( 'Unable to locate record on file', 'woocommerce-bkt' ),
			'28' => __( 'Original is Denied', 'woocommerce-bkt' ),
			'29' => __( 'Original not found', 'woocommerce-bkt' ),
			'30' => __( 'Format error (switch generated)', 'woocommerce-bkt' ),
			'33' => __( 'Expired card, pick-up', 'woocommerce-bkt' ),
			'36' => __( 'Restricted card, pick-up', 'woocommerce-bkt' ),
			'38' => __( 'Allowable PIN tries ,exceeded', 'woocommerce-bkt' ),
			'41' => __( 'Lost card, Pick-up', 'woocommerce-bkt' ),
			'43' => __( 'Stolen card, pick-up,', 'woocommerce-bkt' ),
			'51' => __( 'Insufficient funds', 'woocommerce-bkt' ),
			'52' => __( 'No checking account', 'woocommerce-bkt' ),
			'53' => __( 'No savings acc,ount', 'woocommerce-bkt' ),
			'54' => __( 'Expired card', 'woocommerce-bkt' ),
			'55' => __( 'Incorrect PIN', 'woocommerce-bkt' ),
			'56' => __( 'Unsupported Card', 'woocommerce-bkt' ),
			'57' => __( 'Transaction not permitted to card', 'woocommerce-bkt' ),
			'58' => __( 'POS doesn\'t have permission for this transaction', 'woocommerce-bkt' ),
			'61' => __( 'Exceeds withdrawal amount limit', 'woocommerce-bkt' ),
			'62' => __( 'Restricted card', 'woocommerce-bkt' ),
			'63' =>	__( 'Security violation', 'woocommerce-bkt' ),
			'65' =>	__( 'Cash Limit Exceeded', 'woocommerce-bkt' ),
			'75' =>	__( 'Allowable number of PIN tries exceed', 'woocommerce-bkt' ),
			'76' =>	__( 'Key synchronization error', 'woocommerce-bkt' ),
			'77' =>	__( 'Decline of Request, No script', 'woocommerce-bkt' ),
			'78' =>	__( 'Unsafe PIN', 'woocommerce-bkt' ),
			'79' =>	__( 'ARQC failed', 'woocommerce-bkt' ),
			'81' =>	__( 'Device Version Incompatible', 'woocommerce-bkt' ),
			'85' =>	__( 'Approval of request (for PIN man)', 'woocommerce-bkt' ),
			'91' =>	__( 'Issuer or switch is inoperative', 'woocommerce-bkt' ),
			'92' =>	__( 'Financial institution unknown', 'woocommerce-bkt' ),
			'95' =>	__( 'POS Negotiation Error', 'woocommerce-bkt' ),
			'96' =>	__( 'System malfunction', 'woocommerce-bkt' ),
			'98' =>	__( 'Duplicate Reversal', 'woocommerce-bkt ')
		);

		return ( $code && array_key_exists( $code, $response_codes ) ) ? $response_codes[$code] : $response_codes;
	}

	public static function application_error_codes( $code = false ){
		
		$error_codes = array(
			'M001' => __( 'Bonus amount can not be greater than order amount', 'woocommerce-bkt' ),
			'M002' => __( 'Currency Code Is Invalid', 'woocommerce-bkt' ),
			'M003' => __( 'Currency Code Is Missing', 'woocommerce-bkt' ),
			'M004' => __( 'Zero or empty amount or wrong.', 'woocommerce-bkt' ),
			'M005' => __( 'Cvv2 must be represent but it is empty now.', 'woocommerce-bkt' ),
			'M006' => __( 'Expiry Date Is Missing Or Wrong Length Must be 4 characters.', 'woocommerce-bkt' ),
			'M007' => __( 'Fail Url Is Missing', 'woocommerce-bkt' ),
			'M008' => __( 'Pan Is Missing Or Invalid Length Of Pan.Must be 13 >= Pan <= 19', 'woocommerce-bkt' ),
			'M009' => __( 'Password match not succeed please confirm your password: {0}', 'woocommerce-bkt' ),
			'M010' => __( 'Ok Url Is Missing', 'woocommerce-bkt' ),
			'M011' => __( 'Pareq could not prepared.', 'woocommerce-bkt' ),
			'M012' => __( 'Purchase Amount Is Missing Or Wrong Length', 'woocommerce-bkt' ),
			'M013' => __( 'Mbr Id is missing.', 'woocommerce-bkt' ),
			'M014' => __( 'Unknown Secure Type', 'woocommerce-bkt' ),
			'M015' => __( 'Unknown Txn Type', 'woocommerce-bkt' ),
			'M016' => __( 'User Code Is Missing', 'woocommerce-bkt' ),
			'M017' => __( 'User Pass Is Missing', 'woocommerce-bkt' ),
			'M018' => __( 'Wrong Cvv2 {0}', 'woocommerce-bkt' ),
			'M019' => __( 'Purchase amount must be 0,01-200.000', 'woocommerce-bkt' ),
			'M020' => __( 'Can not make hire-purchase to using full bonus transactions.', 'woocommerce-bkt' ),
			'M021' => __( 'Invalid securetype for this transaction type', 'woocommerce-bkt' ),
			'M022' => __( 'Original transaction order id can not be emtpy for this transaction type', 'woocommerce-bkt' ),
			'M023' => __( 'Original transaction order id must not be sent for this transaction type', 'woocommerce-bkt' ),
			'M024' => __( 'Merchant Id Is Missing', 'woocommerce-bkt' ),
			'M025' => __( '{0} column wrong length Request : {1} Actual : {2}', 'woocommerce-bkt' ),
			'M026' => __( 'Wrong secure type for this txn type', 'woocommerce-bkt' ),
			'M027' => __( 'Record not found which you\'re looking for', 'woocommerce-bkt' ),
			'M028' => __( 'Order id can not be emtpy for this transaction type', 'woocommerce-bkt' ),
			'M030' => __( 'HASH mismatch', 'woocommerce-bkt' ),
			'M038' => __( 'OkURL must be maximum 200 characters', 'woocommerce-bkt' ),
			'M041' => __( 'Invalid card number', 'woocommerce-bkt' ),
			'M042' => __( 'Plugin Not Found', 'woocommerce-bkt' ),
			'M043' => __( 'Request form can not be empty', 'woocommerce-bkt' ),
			'M044' => __( 'Error during Mpi Post', 'woocommerce-bkt' ),
			'M045' => __( 'Max Amount Error', 'woocommerce-bkt' ),
			'M046' => __( 'Error during report request', 'woocommerce-bkt' ),
			'M047' => __( 'HASH mismatch', 'woocommerce-bkt' ),
			'V001' => __( 'Merchant Find Error', 'woocommerce-bkt' ),
			'V002' => __( 'Could not connect to DS or Resolve Error {0}', 'woocommerce-bkt' ),
			'V003' => __( 'System Closed', 'woocommerce-bkt' ),
			'V004' => __( 'User could not be verified', 'woocommerce-bkt' ),
			'V005' => __( 'Unknown Card Type', 'woocommerce-bkt' ),
			'V006' => __( 'User is not permitted for this transaction type', 'woocommerce-bkt' ),
			'V007' => __( 'Terminal is not active', 'woocommerce-bkt' ),
			'V008' => __( 'Merchant could not be found', 'woocommerce-bkt' ),
			'V009' => __( 'Merchant is not active', 'woocommerce-bkt' ),
			'V010' => __( 'Terminal is not authorized for this type of transaction', 'woocommerce-bkt' ),
			'V011' => __( 'Transaction type is not permitted for this terminal', 'woocommerce-bkt' ),
			'V012' => __( 'Pareq Error {0}', 'woocommerce-bkt' ),
		);

		return ( $code && array_key_exists( $code, $error_codes ) ) ? $error_codes[$code] : $error_codes;

	}

	public function is_application_error( $error_code ){
		
		if ( array_key_exists( $error_code, self::application_error_codes() ) )
			return true;

		return false;

	}

	public function get_bank_error_message( $error_code ){

		$error_message = $this->bkt_response_codes( $error_code );
		if ( is_string( $error_message ) )
			return $error_message;

		$error_message = $this->application_error_codes( $error_code );
		if ( is_string( $error_message ) )
			return $error_message;

		return false;
	}


	public function log( $message ) {
		if ( 'yes' === $this->get_option( 'testmode' ) || $this->enable_logging ) {
			if ( empty( $this->logger ) ) {
				$this->logger = new WC_Logger();
			}
			$this->logger->add( 'bkt', $message );
		}
	}

	public function check_bank_response() {

		$data = stripslashes_deep( $_POST );
		
		$this->log( "\n" . '----------' . "\n" . 'BKT call received' );
		$this->log( 'BKT Data: ' . print_r( $data, true ) );

		$debug_email    = $this->get_option( 'debug_email', get_option( 'admin_email' ) );
		
		$vendor_name    = get_bloginfo( 'name' );
		$redirect_url   = home_url( '/' );

		$order_id       = absint( $data['order_id'] );
		$order_key      = wc_clean( $session_id );
		$order          = wc_get_order( $order_id );

		if ( !$order || false === $data ){
			$this->log( __( 'Bad access of page', 'woocommerce-bkt' ) );
			wp_redirect( $redirect_url );
			die;
		}
		
		if ( $data['3DStatus'] != '1' )
			$this->log( __( '3D User Authentication Failed', 'woocommerce-bkt' ) );

		if ( $data['ProcReturnCode'] == '00' ) {
			
			$order->add_order_note( __( 'Payment completed', 'woocommerce-bkt' ), true );
			$this->log( __( 'Payment completed', 'woocommerce-bkt' ) );
			
			// Add order meta
			update_post_meta( $order->get_id(), '_bkt_status', 'Approved' );
			update_post_meta( $order->get_id(), '_bkt_transaction_id', $data['AuthCode'] );
			update_post_meta( $order->get_id(), '_bkt_transaction_card_type', $data['CardType'] );
			update_post_meta( $order->get_id(), '_bkt_card_mask', $data['CardMask'] );
			update_post_meta( $order->get_id(), '_bkt_transaction_date', $data['ReqDate'] );

			$order->payment_complete();
			$redirect_url = $this->get_return_url( $order );

		} else{

			$message = $this->get_bank_error_message( $data['ProcReturnCode'] );
			
			$this->log( $message );
			$order->add_order_note( $message );
			
			if ( !$this->is_application_error( $data['ProcReturnCode'] ) ){
				$order->add_order_note( $message, true );
			}

			$order->update_status( 'failed', $error_message );
			$redirect_url = $this->get_return_url( $order );
		}

		wp_redirect( $redirect_url );
		die;
	}

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
				'default' => 'yes',
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

	
	public function receipt_page( $order ) {
		echo '<p>' . __( 'Thank you for your order, please click the button below to pay with BKT.', 'woocommerce-bkt' ) . '</p>';
		echo $this->generate_bkt_form( $order );
	}


	public function set_hash( $order_id ){
		
		$order = wc_get_order( $order_id );

		if ( !$order )
			return;

		$hashstr = implode('',[
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

		$hash = base64_encode( pack( 'H*', sha1( $hashstr ) ) );
		return $hash;
	}
	
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
			'OkUrl'						=> $this->response_url,
			'FailUrl'					=> $this->response_url,
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