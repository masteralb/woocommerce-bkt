<?php

if (!defined('ABSPATH')) {
	exit;
}

return array(
    'enabled' => array(
        'title'       => __('Enable/Disable', 'woocommerce-bkt'),
        'label'       => __('Enable BKT', 'woocommerce-bkt'),
        'type'        => 'checkbox',
        'description' => __('This controls whether or not this gateway is enabled within WooCommerce.', 'woocommerce-bkt'),
        'default'     => 'yes',
        'desc_tip'    => true,
    ),

    'title' => array(
        'title'       => __('Title', 'woocommerce-bkt'),
        'type'        => 'text',
        'description' => __('This controls the title which the user sees during checkout.', 'woocommerce-bkt'),
        'default'     => __('BKT', 'woocommerce-bkt'),
        'desc_tip'    => true,
    ),

    'description' => array(
        'title'       => __('Description', 'woocommerce-bkt'),
        'type'        => 'text',
        'description' => __('This controls the description which the user sees during checkout.', 'woocommerce-bkt'),
        'default'     => '',
        'desc_tip'    => true,
    ),

    'testmode' => array(
        'title'       => __('BKT Sandbox', 'woocommerce-bkt'),
        'type'        => 'checkbox',
        'description' => __('Place the payment gateway in development mode.', 'woocommerce-bkt'),
        'default'     => 'yes',
    ),

    'lang' => array(
        'title'       => __('Language', 'woocommerce-bkt'),
        'type'        => 'text',
        'description' => __('User Language information. (Turkish: TR, English: EN)', 'woocommerce-bkt'),
        'default'     => 'EN',
    ),


    'member_id' => array(
        'title'       => __('Member ID', 'woocommerce-bkt'),
        'type'        => 'text',
        'description' => __('This is the member ID, received from BKT.', 'woocommerce-bkt'),
        'default'     => '9',
    ),

    'merchant_id' => array(
        'title'       => __('Merchant ID', 'woocommerce-bkt'),
        'type'        => 'text',
        'description' => __('This is the merchant ID, received from BKT.', 'woocommerce-bkt'),
        'default'     => '',
    ),

    'merchant_pass' => array(
        'title'       => __('Merchant Password', 'woocommerce-bkt'),
        'type'        => 'text',
        'description' => __('This is the merchant password, received from BKT.', 'woocommerce-bkt'),
        'default'     => '',
    ),

    'user_code' => array(
        'title'       => __('User code', 'woocommerce-bkt'),
        'type'        => 'text',
        'description' => __('This is the user code, received from BKT.', 'woocommerce-bkt'),
        'default'     => '',
    ),

    'user_pass' => array(
        'title'       => __('User password', 'woocommerce-bkt'),
        'type'        => 'password',
        'description' => __('This is the user password, received from BKT.', 'woocommerce-bkt'),
        'default'     => '',
    ),

    'user_pass' => array(
        'title'       => __('User password', 'woocommerce-bkt'),
        'type'        => 'text',
        'description' => __('This is the user password, received from BKT.', 'woocommerce-bkt'),
        'default'     => '',
    ),

    'secure_type' => array(
        'title'       => __('Secure type', 'woocommerce-bkt'),
        'type'        => 'text',
        'description' => __('Represents the Security type of the transaction. ( NonSecure, 3Dpay, 3DModel, 3DHost )', 'woocommerce-bkt'),
        'default'     => '3DHost',
    ),

    'transaction_type' => array(
        'title'       => __('Translation type', 'woocommerce-bkt'),
        'type'        => 'text',
        'description' => __('Transaction type. “Auth” should be sent for provision. ( Sales:Auth, PreAuthorization:PreAuth, PreAuthorizationClosing:PostAuth, Refund, Void, PointInquiry, OrderInquiry:OrderInq, BatchClose)', 'woocommerce-bkt'),
        'default'     => 'Auth',
    ),

    'installment_count' => array(
        'title'       => __('Installment count', 'woocommerce-bkt'),
        'type'        => 'number',
        'description' => __('Represents the number of installments. This number should be greater than 1 if this transaction is to be accepted as a transaction with installment. If this is a number smaller than 0 or a non-numeric symbol, this number is returned as 0', 'woocommerce-bkt'),
        'default'     => '0',
    ),

    'currency' => array(
        'title'       => __('Currency', 'woocommerce-bkt'),
        'type'        => 'number',
        'description' => __('949 = TL, 840 = USD, 978 = EUR, 8 = ALL', 'woocommerce-bkt'),
        'default'     => '978',
    ),

    'send_debug_email' => array(
        'title'   => __('Send Debug Emails', 'woocommerce-bkt'),
        'type'    => 'checkbox',
        'label'   => __('Send debug e-mails for transactions through the BKT gateway (sends on successful transaction as well).', 'woocommerce-bkt'),
        'default' => 'yes',
    ),

    'debug_email' => array(
        'title'       => __('Who Receives Debug E-mails?', 'woocommerce-bkt'),
        'type'        => 'text',
        'description' => __('The e-mail address to which debugging error e-mails are sent when in test mode.', 'woocommerce-bkt'),
        'default'     => get_option('admin_email'),
    ),

    'enable_logging' => array(
        'title'   => __('Enable Logging', 'woocommerce-bkt'),
        'type'    => 'checkbox',
        'label'   => __('Enable transaction logging for gateway.', 'woocommerce-bkt'),
        'default' => 'yes',
    ),

    'template_type' => array(
        'title'   => __('Template type', 'woocommerce-bkt'),
        'type'    => 'number',
        'description'   => __('Template related with Billing and Shipping', 'woocommerce-bkt'),
        'default' => 1,
    ),

    'payment_post_url' => array(
        'title'   => __('Payment post url', 'woocommerce-bkt'),
        'type'    => 'text',
        'description'   => __('Post url', 'woocommerce-bkt'),
        'default' => 'https://payfortestbkt.cordisnetwork.com/Mpi/3DHost.aspx',
    ),

    'order_inquiry_url' => array(
        'title'   => __('Order inquiry url', 'woocommerce-bkt'),
        'type'    => 'text',
        'description'   => __('Inquiry url', 'woocommerce-bkt'),
        'default' => 'https://payfortestbkt.cordisnetwork.com/Mpi/Default.aspx',
    ),

    'business_name' => array(
        'title'   => __('Business Name', 'woocommerce-bkt'),
        'type'    => 'text',
        'description'   => __('Will be displayed at invoice header', 'woocommerce-bkt'),
        'default' => get_bloginfo('name'),
    ),

    'business_nipt' => array(
        'title'   => __('Business NIPT ( Tax ID Number )', 'woocommerce-bkt'),
        'type'    => 'text',
        'description'   => __('Will be displayed at invoice header', 'woocommerce-bkt'),
        'default' => '',
    ),

    'business_address' => array(
        'title'   => __('Business Address', 'woocommerce-bkt'),
        'type'    => 'text',
        'description'   => __('Will be displayed at invoice header', 'woocommerce-bkt'),
        'default' => '',
    ),

    'support_phone_number' => array(
        'title'   => __('Support phone number', 'woocommerce-bkt'),
        'type'    => 'text',
        'description'   => __('Will be displayed at invoice footer', 'woocommerce-bkt'),
        'default' => '',
    ),

    'support_email_address' => array(
        'title'   => __('Support email address', 'woocommerce-bkt'),
        'type'    => 'text',
        'description'   => __('Will be displayed at invoice footer', 'woocommerce-bkt'),
        'default' => '',
    ),

    'footer_notes' => array(
        'title'   => __('Footer Notes', 'woocommerce-bkt'),
        'type'    => 'textarea',
        'description'   => __('Will be displayed at invoice footer', 'woocommerce-bkt'),
        'default' => '',
        'css' => 'width: 800px; height: 180px;'
    )
);
