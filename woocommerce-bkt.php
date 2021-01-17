<?php

/**
 * Plugin Name:     Woocommerce BKT
 * Plugin URI:      http://masteralb.com
 * Description:     BKT ( Banka Kombetare Tregetare ) payment gateway for woocommerce
 * Author:          Linuxoid ( p.metaj@gmail.com )
 * Author URI:      http://masteralb.com
 * Text Domain:     woocommerce-bkt
 * Domain Path:     /languages
 * Version:         2.0.0
 *
 * @package         Woocommerce_Bkt
 */

if (!defined('ABSPATH'))
	exit;

require_once(plugin_basename('vendor/autoload.php'));

define('WC_GATEWAY_BKT_VERSION', '2.0.0');
define('WC_GATEWAY_BKT_PLUGIN_FILE',  __FILE__);
define('WC_GATEWAY_BKT_PLUGIN_PATH',  untrailingslashit(plugin_dir_path(WC_GATEWAY_BKT_PLUGIN_FILE)));

function woocommerce_bkt_load_plugin_textdomain()
{
	$locale = is_admin() && function_exists('get_user_locale') ? get_user_locale() : get_locale();
	$locale = apply_filters('plugin_locale', $locale, 'woocommerce-bkt');
	unload_textdomain('woocommerce-bkt');
	load_textdomain('woocommerce-bkt', WP_LANG_DIR . '/woocommerce-bkt/woocommerce-bkt-' . $locale . '.mo');
	load_plugin_textdomain('woocommerce-bkt', false, plugin_basename(dirname(WC_GATEWAY_BKT_PLUGIN_FILE)) . '/languages');
}

function wc_bkt_init()
{
	if (defined('WC_GATEWAY_BKT_LOADED') && WC_GATEWAY_BKT_LOADED) {
		return;
	}

	if (!function_exists('is_plugin_active')) {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
	}

	/**
	 * If WooCommerce is not active, let users know.
	 */
	if (!is_plugin_active('woocommerce/woocommerce.php')) {
		add_action(
			'admin_notices',
			function () {
				echo '<div class="error notice">
					<p>' . esc_html__('Bkt payment gateway for WooCommerce: WooCommerce plugin should be enabled.', 'woocommerce-bkt') . '</p>
				</div>';
			}
		);
		return;
	}

	if (!is_plugin_active('woocommerce/woocommerce.php')) {
		return;
	}

	add_action('plugins_loaded', 'woocommerce_bkt_init_payment_gateway');
	add_action('plugins_loaded', 'woocommerce_bkt_load_plugin_textdomain');
	add_filter('woocommerce_payment_gateways', 'woocommerce_bkt_add_gateway');

	define('WC_GATEWAY_BKT_LOADED', true);
}

function woocommerce_bkt_init_payment_gateway()
{
	require_once __DIR__ . '/includes/class-wc-bkt-invoice.php';
	require_once __DIR__ . '/includes/class-wc-payment-gateway-bkt.php';
}

function woocommerce_bkt_add_gateway($methods)
{
	$methods[] = 'WC_Payment_Gateway_Bkt';
	return $methods;
}

wc_bkt_init();
