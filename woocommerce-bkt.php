<?php
/**
 * Plugin Name:     Woocommerce BKT
 * Plugin URI:      http://masteralb.com
 * Description:     BKT ( Banka Kombetare Tregetare ) payment gateway for woocommerce
 * Author:          Linuxoid ( p.metaj@gmail.com )
 * Author URI:      http://masteralb.com
 * Text Domain:     woocommerce-bkt
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         Woocommerce_Bkt
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

require_once( plugin_basename( 'vendor/autoload.php' ) ); 

define( 'WC_GATEWAY_BKT_VERSION', '1.0.0' );

function woocommerce_bkt_init() {
	
	if ( ! class_exists( 'WC_Payment_Gateway' ) )
		return;

	require_once( plugin_basename( 'includes/WC_Gateway_Bkt.php' ) );
	load_plugin_textdomain( 'woocommerce-bkt', false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ) );
	add_filter( 'woocommerce_payment_gateways', 'woocommerce_bkt_add_gateway' );

}

add_action( 'plugins_loaded', 'woocommerce_bkt_init', 0 );

function woocommerce_bkt_add_gateway( $methods ) {
	$methods[] = 'WC_Gateway_Bkt';
	return $methods;
}