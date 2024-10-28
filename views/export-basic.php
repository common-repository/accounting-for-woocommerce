<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<h3><?php _e( 'Export settings' , 'accounting-for-woocommerce'); ?></h3>
<p><?php _e( 'Configure your export and export completed orders.' , 'accounting-for-woocommerce'); ?></p>

<!--custom_date datepicker.-->
<script type="text/javascript">
jQuery(document).ready(function($) {
	$(' .custom_date').datepicker({
		dateFormat :'yy-mm-dd'
	});
});
</script>
<!--Form-->
<form method="post" action="admin-post.php">
	<!--triggering for action-->
	<input type="hidden" name="action" value="woocommerce_accounting_export" />
	<!-- Add Nonce -->
	<?php wp_nonce_field('check_nonce_export','_check_export');
	$today = date_i18n( 'Y-m-d');
	?>

  <h3><?php _e( 'Date range' , 'accounting-for-woocommerce'); ?></h3>
	<table width="90%">
		<tr valign="middle">
			<td>
				<h4><?php _e( 'From:' , 'accounting-for-woocommerce'); ?></h4>
				<input type="text" class="custom_date" name="woocommerce_accounting_export_start_date" id="woocommerce_accounting_export_start_date" placeholder="<?php _e( 'Start', 'accounting-for-woocommerce'); ?>" value="<?php echo esc_attr(get_option('woocommerce_accounting_export_end_date')); ?>"/>
			</td>
			<td>
				<h4><?php _e( 'To:' , 'accounting-for-woocommerce'); ?></h4>
				<input type="text" class="custom_date" name="woocommerce_accounting_export_end_date" id="woocommerce_accounting_export_end_date" placeholder="<?php _e( 'End', 'accounting-for-woocommerce'); ?>" value="<?php echo $today; ?>"/>
			</td>
		</tr>
  </table>
  <details>
    <summary class="button-link">
    <?php _e( 'Export options' , 'accounting-for-woocommerce'); ?>
  </summary>
  <table width="90%">
		<tr valign="middle">
			<td>
				<h4><?php _e( 'Date value' , 'accounting-for-woocommerce'); ?></h4>
				<select name="woocommerce_accounting_export_date_opt" id="woocommerce_accounting_export_date_opt"/>
				<?php
				echo woocommerce_accounting_export_select_options(
					array(
						0 => __( 'Order', 'accounting-for-woocommerce'),
						1 => __( 'Validation', 'accounting-for-woocommerce'),
						3 => __( 'Paid', 'accounting-for-woocommerce'),
					),
					'export_date_opt'
				);
				?>
				</select>
			</td>
			<td>
				<h4><?php _e( 'Date Format' , 'accounting-for-woocommerce'); ?></h4>
				<select name="woocommerce_accounting_export_date_format" id="woocommerce_accounting_export_date_format"/>
				<?php
				echo woocommerce_accounting_export_select_options(
					array(
						0 => __( 'Standard', 'accounting-for-woocommerce'),
						1 => __( 'D-M-Y', 'accounting-for-woocommerce'),
						2 => __( 'M-D-Y', 'accounting-for-woocommerce'),
						3 => __( 'Y-M-D', 'accounting-for-woocommerce'),
						4 => __( 'D/M/Y', 'accounting-for-woocommerce'),
						5 => __( 'M/D/Y', 'accounting-for-woocommerce'),
						6 => __( 'Y/M/D', 'accounting-for-woocommerce'),
					),
					'export_date_format'
				);
				?>
				</select>
			</td>
			<td>
				<h4><?php _e( 'Payment Method' , 'accounting-for-woocommerce'); ?></h4>
				<select name="woocommerce_accounting_export_pay_opt" id="woocommerce_accounting_export_pay_opt"/>
				<?php
				echo woocommerce_accounting_export_select_options(
					array(
						0 => __( 'No', 'accounting-for-woocommerce'),
						1 => __( 'General', 'accounting-for-woocommerce'),
						2 => __( 'Personalised', 'accounting-for-woocommerce'),
					),
					'export_pay_opt'
				);
				?>
				</select>
			</td>
			<td>
				<h4><?php _e( 'Piece Number' , 'accounting-for-woocommerce'); ?></h4>
				<select name="woocommerce_accounting_export_factnum_opt" id="woocommerce_accounting_export_factnum_opt"/>
				<?php
				echo woocommerce_accounting_export_select_options(
					array(
						0 => __( 'Invoice number (if exists)', 'accounting-for-woocommerce'),
						1 => __( 'Order number (Invoice num in Lib)', 'accounting-for-woocommerce'),
					),
					'export_factnum_opt'
				);
				?>
				</select>
			</td>
		</tr>
		<tr valign="middle">
		<td>
			<h4><?php _e( 'Customers' , 'accounting-for-woocommerce'); ?></h4>
			<select name="woocommerce_accounting_export_cust_opt" id="woocommerce_accounting_export_cust_opt"/>
			<?php
			echo woocommerce_accounting_export_select_options(
				array(
					1 => __( 'by Gateway type', 'accounting-for-woocommerce'),
				),
				'export_cust_opt'
			);
			?>
			</select>
		</td>
		<td>
			<h4><?php _e( 'Products' , 'accounting-for-woocommerce'); ?></h4>
			<select name="woocommerce_accounting_export_prod_opt" id="woocommerce_accounting_export_prod_opt"/>
			<?php
			echo woocommerce_accounting_export_select_options(
				array(
					0 => __( 'Simple', 'accounting-for-woocommerce'),
					2 => __( 'Simple by Country', 'accounting-for-woocommerce'),
				),
				'export_prod_opt'
			);
			?>
			</select>
		</td>
		<td>
			<h4><?php _e( 'Coupon Export' , 'accounting-for-woocommerce'); ?></h4>
			<select name="woocommerce_accounting_export_coupon_opt" id="woocommerce_accounting_export_coupon_opt"/>
			<?php
			echo woocommerce_accounting_export_select_options(
				array(
					0 => __( 'Simple', 'accounting-for-woocommerce'),
				),
				'export_coupon_opt'
			);
			?>
			</select>
		</td>
		<td>
			<h4><?php _e( 'Taxes' , 'accounting-for-woocommerce'); ?></h4>
			<select name="woocommerce_accounting_export_taxes_opt" id="woocommerce_accounting_export_taxes_opt"/>
			<?php
			echo woocommerce_accounting_export_select_options(
				array(
					0 => __( 'Simple', 'accounting-for-woocommerce'),
				),
				'export_taxes_opt'
			);
			?>
			</select>
		</td>
		<td>
			<h4><?php _e( 'Delivery Options' , 'accounting-for-woocommerce'); ?></h4>
			<select name="woocommerce_accounting_export_ship_opt" id="woocommerce_accounting_export_ship_opt"/>
			<?php
			echo woocommerce_accounting_export_select_options(
				array(
					0 => __( 'Simple', 'accounting-for-woocommerce'),
				),
				'export_ship_opt'
			);
			?>
			</select>
		</td>
	</tr>
	<tr valign="middle">
		<td>
			<h4><?php _e( 'Decimal Separator' , 'accounting-for-woocommerce'); ?></h4>
			<select name="woocommerce_accounting_export_decimal_separator" id="woocommerce_accounting_export_decimal_separator"/>
			<?php
			echo woocommerce_accounting_export_select_options(
				array(
					'.' => __( 'Period', 'accounting-for-woocommerce'),
					',' => __( 'Comma', 'accounting-for-woocommerce'),
				),
				'export_decimal_separator'
			);
			?>
			</select>
		</td>
		<td>
			<h4><?php _e( 'CSV Separator' , 'accounting-for-woocommerce'); ?></h4>
			<select name="woocommerce_accounting_export_separator" id="woocommerce_accounting_export_separator"/>
			<?php
			echo woocommerce_accounting_export_select_options(
				array(
					',' => __( 'Comma', 'accounting-for-woocommerce'),
					';' => __( 'Semi-Colon', 'accounting-for-woocommerce'),
					't' => __( 'Tab', 'accounting-for-woocommerce'),
				),
				'export_separator'
			);
			?>
			</select>
		</td>
		<td>
			<h4><?php _e( 'Fill opposite amount with' , 'accounting-for-woocommerce'); ?></h4>
			<select name="woocommerce_accounting_export_zero_opt" id="woocommerce_accounting_export_zero_opt"/>
			<?php
			echo woocommerce_accounting_export_select_options(
				array(
					0 => __( 'Nothing', 'accounting-for-woocommerce'),
					1 => __( 'Zero', 'accounting-for-woocommerce'),
				),
				'export_zero_opt'
			);
			?>
		</td>
		<td>
			<h4><?php _e( 'Column for coupons' , 'accounting-for-woocommerce'); ?></h4>
			<select name="woocommerce_accounting_export_coupon_col" id="woocommerce_accounting_export_coupon_col"/>
			<?php
			echo woocommerce_accounting_export_select_options(
				array(
					'i' => __( 'Negative Income', 'accounting-for-woocommerce'),
					'o' => __( 'Outcome', 'accounting-for-woocommerce'),
				),
				'export_coupon_col'
			);
			?>
		</td>
		<td>
			<h4><?php _e( 'Rounding precision' , 'accounting-for-woocommerce'); ?></h4>
			<select name="woocommerce_accounting_export_rounding_precision" id="woocommerce_accounting_export_rounding_precision"/>
			<?php
			echo woocommerce_accounting_export_select_options(
				array(
					2 => 2,
					1 => 1,
					0 => 0,
				),
				'export_rounding_precision'
			);
			?>
		</td>
		</tr>
	</table>
	</details>
	<?php // translators: link to pro addon ?>
	<?php echo apply_filters('woocommerce_accounting:export_form:pro_features', '<p>'.sprintf(__('More options available with the <a href="%s" target="_blank">pro addon</a>.', 'accounting-for-woocommerce'), 'https://apps.avecnous.eu/produit/woocommerce-accounting/?mtm_campaign=wp-plugin&mtm_kwd=accounting-for-woocommerce').'</p>'); ?>
	<?php do_action('woocommerce_accounting:export_form:before_submit'); ?>
	<?php submit_button( __( 'Export!' , 'accounting-for-woocommerce') ); ?>
	<?php do_action('woocommerce_accounting:export_form:after_submit'); ?>
</form>
