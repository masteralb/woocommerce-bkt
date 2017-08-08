<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?php _e( 'Invoice', 'woocommerce-bkt' ); ?></title>
	<style type="text/css">		
		
		body{
			font-family: Verdana, Arial, Helvetica;
		}

		table{
			border-collapse: collapse;
			max-width: 800px;
			width: 100%;
		}

		td, th{
			padding: 3px 10px;
			text-align: left;
			font-weight: normal;
			font-size: 12px;
		}

		.heading{
			font-weight: bold;
			border-bottom: 1px solid black;
		}

		.has-borders td, .has-borders th{
			border: 1px solid #dedede;
		}

		.invoice-heading td{
			font-weight: bold;
		}

		.footer-notes{
			font-size: 11px;
		}

		.invoice-number span{
			background: #dedede;
			padding: 0 10px;
		}

	</style>
</head>
<body>

<?php 
global $wpdb, $order_id;
$order 		= wc_get_order( $order_id );

// Get the payment gateway
$payment_gateway = wc_get_payment_gateway_by_order( $order );

// Get line items
$line_items          = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
$line_items_fee      = $order->get_items( 'fee' );
$line_items_shipping = $order->get_items( 'shipping' );

if ( wc_tax_enabled() ) {
	$order_taxes      = $order->get_taxes();
	$tax_classes      = WC_Tax::get_tax_classes();
	$classes_options  = wc_get_product_tax_class_options();
	$show_tax_columns = sizeof( $order_taxes ) === 1;
}

// Get payment details
$_bkt_status 				= get_post_meta( $order_id, '_bkt_status', true );
$_bkt_transaction_id 		= get_post_meta( $order_id, '_bkt_transaction_id', true );
$_bkt_transaction_card_type = get_post_meta( $order_id, '_bkt_transaction_card_type', true );
$_bkt_card_mask 			= get_post_meta( $order_id, '_bkt_card_mask', true );
$_bkt_transaction_dat 		= get_post_meta( $order_id, '_bkt_transaction_date', true );

// Get gateway settings
$settings 	= (object) get_option( 'woocommerce_bkt_settings' , false );

$business_name 		= $settings->business_name;
$nipt_number 		= $settings->business_nipt;
$business_address 	= $settings->business_address;
$invoice_number		= '#'.$order_id;
$support_email		= $settings->support_phone_number;
$support_number		= $settings->support_email_address;
?>
<table>
	
	<tbody class="invoice-heading">
		<tr>
			<td><?php echo $business_name; ?></td>
		</tr>
		<tr>
			<td><?php _e( 'Nipt', 'woocommerce-bkt' ); ?>: <?php echo $nipt_number; ?></td>
		</tr>
		<tr>
			<td><?php _e( 'Address', 'woocommerce-bkt' ); ?>: <?php echo $business_address; ?></td>
		</tr>		
		<tr>
			<td><?php _e( 'Support', 'woocommerce-bkt' ); ?>: <?php echo $support_email; ?> / <?php echo $support_number; ?></td>
		</tr>
		<tr>
			<td class="invoice-number">Invoice: 
			<span><?php echo $invoice_number; ?></span>, <?php echo date('d/m/Y', strtotime($order->get_date_paid()) ) ?>
		</td>
		</tr>
		<tr><td colspan="5">&nbsp;</td></tr>
	</tbody>

	<!-- Order items heading -->
	<tbody>
		<tr>
			<td colspan="5" class="heading"><?php _e( 'Order Items', 'woocommerce-bkt' ); ?></td>
		</tr>
		<tr class="has-borders">
			<td><?php _e( 'Item', 'woocommerce' ); ?></td>
			<td><?php _e( 'Cost', 'woocommerce' ); ?></td>
			<td><?php _e( 'Qty', 'woocommerce' ); ?></td>
			<td><?php _e( 'Total', 'woocommerce' ); ?></td>
			<?php
			if ( ! empty( $order_taxes ) ) :
				foreach ( $order_taxes as $tax_id => $tax_item ) :
					$tax_class      = wc_get_tax_class_by_tax_id( $tax_item['rate_id'] );
					$tax_class_name = isset( $classes_options[ $tax_class ] ) ? $classes_options[ $tax_class ] : __( 'Tax', 'woocommerce' );
					$column_label   = ! empty( $tax_item['label'] ) ? $tax_item['label'] : __( 'Tax', 'woocommerce' );
					$column_tip     = sprintf( esc_html__( '%1$s (%2$s)', 'woocommerce' ), $tax_item['name'], $tax_class_name );
					?>
					<td>
					<?php echo esc_attr( $column_label ); ?>
					</td>
				<?php
				endforeach;
			endif;
			?>
		</tr>
	</tbody>
	
	<!-- Order items -->
	<tbody class="has-borders">
	<?php foreach ( $line_items as $item_id => $item ): $product = $item->get_product(); ?>
		<tr>
			<td>
				<?php
				echo esc_html( $item->get_name()) . '<br/>'; 

				if ( $product && $product->get_sku() ) {
					echo  __( 'SKU: ', 'woocommerce' ) . esc_html( $product->get_sku() ) . ', ';
				}

				if ( $item->get_variation_id() ) {
					
					echo __( 'variation: ', 'woocommerce' );
					if ( 'product_variation' === get_post_type( $item->get_variation_id() ) ) {
						echo esc_html( $item->get_variation_id() );
					} else {
						printf( esc_html__( '%s (No longer exists)', 'woocommerce' ), $item->get_variation_id() );
					}
				}?>
			</td>
			<td width="1%">
				<?php
					echo wc_price( $order->get_item_total( $item, false, true ), array( 'currency' => $order->get_currency() ) );
					if ( $item->get_subtotal() !== $item->get_total() ) {
					echo wc_price( wc_format_decimal( $order->get_item_subtotal( $item, false, false ) - $order->get_item_total( $item, false, false ), '' ), array( 'currency' => $order->get_currency() ) );
					}
				?>
			</td>
			<td width="1%">
				<?php
					echo esc_html( $item->get_quantity() );
					if ( $refunded_qty = $order->get_qty_refunded_for_item( $item_id ) ) {
						echo ( $refunded_qty * -1 );
					}
				?>
			</td>
			<td width="1%">
				<?php
				echo wc_price( $item->get_total(), array( 'currency' => $order->get_currency() ) );

				if ( $item->get_subtotal() !== $item->get_total() ) {
					echo wc_price( wc_format_decimal( $item->get_subtotal() - $item->get_total(), '' ), array( 'currency' => $order->get_currency() ) );
				}

				if ( $refunded = $order->get_total_refunded_for_item( $item_id ) ) {
					echo wc_price( $refunded, array( 'currency' => $order->get_currency() ) );
				}
				?>
			</td>

			<?php
			if ( ( $tax_data = $item->get_taxes() ) && wc_tax_enabled() ):
				foreach ( $order_taxes as $tax_item ):
					$tax_item_id       = $tax_item->get_rate_id();
					$tax_item_total    = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';
					$tax_item_subtotal = isset( $tax_data['subtotal'][ $tax_item_id ] ) ? $tax_data['subtotal'][ $tax_item_id ] : '';
					?>
					<td width="1%">
						<?php
						if ( '' != $tax_item_total ) {
							echo wc_price( wc_round_tax_total( $tax_item_total ), array( 'currency' => $order->get_currency() ) );
						} else {
							echo '&ndash;';
						}

						if ( $item->get_subtotal() !== $item->get_total() ) {
							if ( '' === $tax_item_total ) {
								echo '&ndash;';
							} else {
								echo wc_price( wc_round_tax_total( $tax_item_subtotal - $tax_item_total ), array( 'currency' => $order->get_currency() ) );
							}
						}

						if ( $refunded = $order->get_tax_refunded_for_item( $item_id, $tax_item_id ) ) {
							echo wc_price( $refunded, array( 'currency' => $order->get_currency() ) );
						}
						?>
					</td>
				<?php endforeach; ?>
			<?php endif;?>
		</tr>
	<?php endforeach; ?>
	</tbody>
	
	<!-- Shipping costs -->
	<tbody>
		<tr><td colspan="5">&nbsp;</td></tr>
		<tr>
			<td colspan="5" class="heading"><?php _e( 'Shipping', 'woocommerce' ) ?></td>
		</tr>
		<?php 
		$shipping_methods = WC()->shipping() ? WC()->shipping->load_shipping_methods() : array();
		foreach ( $line_items_shipping as $item_id => $item ): ?>
			<tr class="has-borders">
				<td colspan="3">
						<?php echo esc_html( $item->get_name() ? $item->get_name() : __( 'Shipping', 'woocommerce' ) ); ?>
				</td>
				<td width="1%">
					<?php
						echo wc_price( $item->get_total(), array( 'currency' => $order->get_currency() ) );
						if ( $refunded = $order->get_total_refunded_for_item( $item_id, 'shipping' ) ) {
							echo wc_price( $refunded, array( 'currency' => $order->get_currency() ) );
						}
					?>
				</td>

				<?php
				if ( ( $tax_data = $item->get_taxes() ) && wc_tax_enabled() ) {
					foreach ( $order_taxes as $tax_item ) {
						$tax_item_id    = $tax_item->get_rate_id();
						$tax_item_total = isset( $tax_data['total'][ $tax_item_id ] ) ? $tax_data['total'][ $tax_item_id ] : '';
						?>
						<td width="1%">
							<?php
								echo ( '' !== $tax_item_total ) ? wc_price( wc_round_tax_total( $tax_item_total ), array( 'currency' => $order->get_currency() ) ) : '&ndash;';

								if ( $refunded = $order->get_tax_refunded_for_item( $item_id, $tax_item_id, 'shipping' ) ) {
									echo wc_price( $refunded, array( 'currency' => $order->get_currency() ) );
								}
							?>
						</td>
						<?php
					}
				}
				?>				
			</tr>
		<?php endforeach; ?>
	</tbody>
	
	<!-- Invoide Totals -->
	<tbody>
		<tr><td colspan="5">&nbsp;</td></tr>
		<tr>
			<td colspan="5" class="heading"><?php _e( 'Total' , 'woocommerce' ) ?></td>
		</tr>
		<tr class="has-borders">
			<td colspan="3" class="text-right"><?php _e( 'Discount:', 'woocommerce' ); ?></td>
			<td colspan="2">
				<?php echo wc_price( $order->get_total_discount(), array( 'currency' => $order->get_currency() ) ); ?>
			</td>
		</tr>
		<tr class="has-borders">
			<td colspan="3" class="text-right"><?php _e( 'Shipping:', 'woocommerce' ); ?></td>
			<td colspan="2"><?php
				if ( ( $refunded = $order->get_total_shipping_refunded() ) > 0 ) {
				echo '<del>' . strip_tags( wc_price( $order->get_shipping_total(), array( 'currency' => $order->get_currency() ) ) ) . '</del> <ins>' . wc_price( $order->get_shipping_total() - $refunded, array( 'currency' => $order->get_currency() ) ) . '</ins>';
				} else {
				echo wc_price( $order->get_shipping_total(), array( 'currency' => $order->get_currency() ) );
				}
				?>			
			</td>
		</tr>
		<?php if ( wc_tax_enabled() ) : ?>
			<?php foreach ( $order->get_tax_totals() as $code => $tax ) : ?>
			<tr class="has-borders">
				<td colspan="3" class="text-right"><?php echo $tax->label; ?>:</td>
				<td colspan="2">
				<?php
				if ( ( $refunded = $order->get_total_tax_refunded_by_rate_id( $tax->rate_id ) ) > 0 ) {
				echo '<del>' . strip_tags( $tax->formatted_amount ) . '</del> <ins>' . wc_price( WC_Tax::round( $tax->amount, wc_get_price_decimals() ) - WC_Tax::round( $refunded, wc_get_price_decimals() ), array( 'currency' => $order->get_currency() ) ) . '</ins>';
				} else {
				echo $tax->formatted_amount;
				}
				?>
				</td>
			</tr>
			<?php endforeach; ?>
		<?php endif; ?>
		<tr class="has-borders">
			<td colspan="3" class="text-right"><strong><?php _e( 'Order total', 'woocommerce' ); ?>:</strong></td>
			<td colspan="2">
				<strong><?php echo $order->get_formatted_order_total(); ?></strong>
			</td>
		</tr>
	</tbody>	
	
	<!-- Bank payment details -->
	<tbody>		
		<tr><td colspan="5">&nbsp;</td></tr>
		<tr>
			<td colspan="5" class="heading"><?php _e( 'Payment Details' , 'woocommerce-bkt' ) ?></td>
		</tr>
		<tr>
			<td colspan="5" style="padding: 0">
			<table class="has-borders">				
				<tr>
					<td width="150px"><?php _e( 'Status' , 'woocommerce-bkt' ) ?>:</td>
					<td><?php echo $_bkt_status; ?></td>
				</tr>
				<tr>
					<td><?php _e( 'Transaction id', 'woocommerce-bkt' ) ?>:</td>
					<td><?php echo $_bkt_transaction_id; ?></td>
				</tr>
				<tr>
					<td><?php _e( 'Card type', 'woocommerce-bkt' ) ?>:</td>
					<td><?php echo $_bkt_transaction_card_type; ?></td>
				</tr>
				<tr>
					<td><?php _e( 'Card', 'woocommerce-bkt' ) ?>:</td>
					<td><?php echo $_bkt_card_mask; ?></td>
				</tr>
				<tr>
					<td><?php _e( 'Transaction date', 'woocommerce-bkt' ) ?>:</td>
					<td><?php echo date('d-m-Y', strtotime($_bkt_transaction_dat) ); ?></td>
				</tr>
				
			</table>
		</tr>
	</tbody>
	
	<!-- Cuctomer contact details -->
	<tbody>
		<tr><td colspan="5">&nbsp;</td></tr>
		<tr>
			<td colspan="5" class="heading"><?php _e( 'Customer contact details', 'woocommerce-bkt' ); ?></td>
		</tr>
		<tr>
			<td colspan="5" style="padding: 0">
			<table class="has-borders">				
				<tr>
					<td width="150px"><?php _e( 'Phone' , 'woocommerce-bkt' ) ?>:</td>
					<td><?php echo $order->get_billing_phone(); ?></td>
				</tr>
				<tr>
					<td><?php _e( 'Email' , 'woocommerce-bkt' ) ?>:</td>
					<td><?php echo $order->get_billing_email(); ?></td>
				</tr>
			</table>
		</tr>
	</tbody>
	
	<!-- Billing and shipping address-->
	<tbody>
		<tr><td colspan="5">&nbsp;</td></tr>
		<tr>
			<td colspan="5" class="heading"><?php _e( 'Billing/Shipping', 'woocommerce-bkt' ); ?></td>
		</tr>
		<tr>
			<td colspan="5" style="padding: 0">
			<table>
				<tr>
					<td><?php echo $order->get_formatted_billing_address(); ?></td>
					<td><?php echo $order->get_formatted_shipping_address(); ?></td>
				</tr>
			</table>
		</tr>
	</tbody>
	
	<!-- Footer Notes -->
	<tbody>
		<tr><td colspan="5">&nbsp;</td></tr>
		<tr><td colspan="5">&nbsp;</td></tr>
		<tr>
			<td colspan="5" class="footer-notes">
				<?php if ( property_exists( $settings, 'footer_notes' ) && $settings->footer_notes ): ?>
					<?php echo $settings->footer_notes ?>
				<?php endif ?>
			</td>
		</tr>
	</tbody>

</table>
</body>
</html>
