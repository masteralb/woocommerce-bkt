<?php

if (!defined('ABSPATH')) {
	exit;
}

use Dompdf\Dompdf;

class WC_Bkt_Invoice
{
	/**
	 * @var WC_Order
	 */
	private $order;

	public function __construct(WC_Order $order)
	{
		$this->order = $order;
	}

	/**
	 * @return array
	 */
	public function maybe_upload_and_save_to_the_order()
	{
		if ($this->is_uploaded_and_saved()) {
			return $this->get_invoice_from_order();
		};

		return $this->upload_and_save_to_the_order();
	}

	public function get_invoice_from_order()
	{
		if ($bkt_invoice = $this->order->get_meta('bkt_invoice')) {
			$bkt_invoice_array = json_decode($bkt_invoice, true);
			if ($bkt_invoice_array) {
				return $bkt_invoice_array;
			}
		}
		return null;
	}

	public function upload_and_save_to_the_order()
	{
		$invoice = $this->upload();
		$this->order->update_meta_data('bkt_invoice', json_encode($invoice));
		$this->order->update_meta_data('bkt_invoice_url', $invoice['url']);
		$this->order->save();
		return $invoice;
	}

	public function is_uploaded_and_saved()
	{
		return $this->is_saved() && $this->is_uploaded();
	}

	private function is_uploaded()
	{
		return file_exists($this->get_invoice_path());
	}

	private function is_saved()
	{
		if (!$this->get_invoice_from_order()) {
			return false;
		};

		return true;
	}

	private function get_invoice_path()
	{
		$this->add_fix_url_for_wp_stateles_filter();
		$upload_dir       = wp_get_upload_dir();
		$this->remove_fix_url_for_wp_stateles_filter();
		return trailingslashit($upload_dir['basedir'] . '/bkt_invoice') . $this->fileName();
	}

	/**
	 * @return array
	 */
	public function upload()
	{
		$this->add_fix_url_for_wp_stateles_filter();
	
		$upload_dir       = wp_get_upload_dir();
		$invoice_basedir  = $upload_dir['basedir'] . '/bkt_invoice';
		$invoice_baseurl  = $upload_dir['baseurl'] . '/bkt_invoice';
		$invoice_filename = $this->fileName();

		$invoice = [
			'url'  => trailingslashit($invoice_baseurl) . $invoice_filename,
			'path' => trailingslashit($invoice_basedir) . $invoice_filename,
			'name' => $invoice_filename,
		];

		if (wp_mkdir_p($invoice_basedir) && !file_exists($invoice['path'])) {
			$file_handle = @fopen($invoice['path'], 'wb'); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen
			if ($file_handle) {
				fwrite($file_handle, $this->pdf()); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
				fclose($file_handle); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
			}
		}

		$this->remove_fix_url_for_wp_stateles_filter();

		return $invoice;
	}

	public function fileName()
	{
		$invoice_salt = defined('NONCE_SALT')
			? NONCE_SALT
			: md5($this->order->get_date_created()->getTimestamp() . ":" . $this->order->get_billing_email());

		return sprintf('invoice_%s.pdf', sha1($this->order->get_id() . $invoice_salt));
	}

	/**
	 * @return string|null
	 */
	public function pdf()
	{
		$dompdf = new Dompdf();
		$dompdf->loadHtml($this->html());
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();

		return $dompdf->output();
	}

	/**
	 * @return string
	 */
	public function html()
	{
		return wc_get_template_html(
			'order/bkt-invoice.php',
			[
				'order'               => $this->order,
				'order_id'            => $this->order->get_order_number(),
				'settings'            => (object) get_option('woocommerce_bkt_settings', false),
			],
			'',
			WC_GATEWAY_BKT_PLUGIN_PATH . '/templates/'
		);
	}

	private function add_fix_url_for_wp_stateles_filter()
	{
		add_filter('wp_stateless_handle_root_dir', [$this, 'fix_url_for_wp_stateles_filter']);
	}

	private function remove_fix_url_for_wp_stateles_filter()
	{
		remove_filter('wp_stateless_handle_root_dir', [$this, 'fix_url_for_wp_stateles_filter']);
	}

	function fix_url_for_wp_stateles_filter($root_dir)
	{
		if (!is_plugin_active('wp-stateless/wp-stateless-media.php')) {
			return $root_dir;
		}

		if (!function_exists('ud_get_stateless_media')) {
			return $root_dir;
		}

		$wildcard_year_month = '%date_year/date_month%';
		$use_year_month = (strpos(ud_get_stateless_media()->get('sm.root_dir'), $wildcard_year_month) !== false) ?: false;
		if ($use_year_month) {
			$root_dir = trim(str_replace(date('Y') . '/' . date('m'), '', $root_dir), '/');
		}

		return $root_dir;
	}
}
