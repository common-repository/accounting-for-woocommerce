<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<h3><?php _e( 'Export settings' , 'accounting-for-woocommerce'); ?></h3>
<p><?php _e( 'Configure your export and export refunds.' , 'accounting-for-woocommerce'); ?></p>

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
	<input type="hidden" name="action" value="woocommerce_accounting_refunds_export" />
	<!-- Add Nonce -->
	<?php wp_nonce_field('check_nonce_refunds_export','_check_refunds_export');
	$today = date_i18n( 'Y-m-d');
	?>

	<table width="90%">
		<tr>
			<td width="200">
				<h3><?php _e( 'Date' , 'accounting-for-woocommerce'); ?></h3>
			</td>
		</tr>
		<tr valign="middle">
			<td width="50">
				<h4><?php _e( 'From:' , 'accounting-for-woocommerce'); ?></h4>
				<input type="text" class="custom_date" name="woocommerce_accounting_refunds_export_start_date" id="woocommerce_accounting_refunds_export_start_date" placeholder="<?php _e( 'Start', 'accounting-for-woocommerce'); ?>" value="<?php echo esc_attr(get_option('woocommerce_accounting_refunds_export_end_date')); ?>"/>
			</td>
			<td width="50">
				<h4><?php _e( 'To:' , 'accounting-for-woocommerce'); ?></h4>
				<input type="text" class="custom_date" name="woocommerce_accounting_refunds_export_end_date" id="woocommerce_accounting_refunds_export_end_date" placeholder="<?php _e( 'End', 'accounting-for-woocommerce'); ?>" value="<?php echo $today; ?>"/>
			</td>
			<td width="50">
				<h4><?php _e( 'Format' , 'accounting-for-woocommerce'); ?></h4>
				<select name="woocommerce_accounting_export_refunds_date_format" id="woocommerce_accounting_export_refunds_date_format"/>
				<option value="0" <?php if( esc_attr(get_option('woocommerce_accounting_export_refunds_date_format'))==0){ echo "selected";} ?>> <?php _e( 'Standard' , 'accounting-for-woocommerce'); ?> </option>
				<option value="1" <?php if( esc_attr(get_option('woocommerce_accounting_export_refunds_date_format'))==1){ echo "selected";} ?>> <?php _e( 'D-M-Y' , 'accounting-for-woocommerce'); ?> </option>
				<option value="2" <?php if( esc_attr(get_option('woocommerce_accounting_export_refunds_date_format'))==2){ echo "selected";} ?>> <?php _e( 'M-D-Y' , 'accounting-for-woocommerce'); ?> </option>
				<option value="3" <?php if( esc_attr(get_option('woocommerce_accounting_export_refunds_date_format'))==3){ echo "selected";} ?>> <?php _e( 'Y-M-D' , 'accounting-for-woocommerce'); ?> </option>
				<option value="4" <?php if( esc_attr(get_option('woocommerce_accounting_export_refunds_date_format'))==4){ echo "selected";} ?>> <?php _e( 'D/M/Y' , 'accounting-for-woocommerce'); ?> </option>
				<option value="5" <?php if( esc_attr(get_option('woocommerce_accounting_export_refunds_date_format'))==5){ echo "selected";} ?>> <?php _e( 'M/D/Y' , 'accounting-for-woocommerce'); ?> </option>
				<option value="6" <?php if( esc_attr(get_option('woocommerce_accounting_export_refunds_date_format'))==6){ echo "selected";} ?>> <?php _e( 'Y/M/D' , 'accounting-for-woocommerce'); ?> </option>
			</td>
		</tr>
	</table>

	<details>
		<summary class="button-link">
		<?php _e( 'Export options' , 'accounting-for-woocommerce'); ?>
		</summary>
	<table width="90%">
		<tr valign="middle">
			<td width="45">
				<h4><?php _e( 'Date' , 'accounting-for-woocommerce'); ?></h4>
				<select name="woocommerce_accounting_refunds_export_date_opt" id="woocommerce_accounting_refunds_export_date_opt"/>
				<option value="0" <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_date_opt'))==0){ echo "selected";} ?>> <?php _e( 'Refund' , 'accounting-for-woocommerce'); ?> </option>
				<?php  if ( in_array( 'woocommerce-pdf-ips-pro/woocommerce-pdf-ips-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) { ?>
				<option value="1" <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_date_opt'))==1){ echo "selected";} ?>> <?php _e( 'Credit note' , 'accounting-for-woocommerce'); ?> </option>
				<?php } ?>
				</select>
			</td>
			<td width="45">
				<h4><?php _e( 'Display Gateway?' , 'accounting-for-woocommerce'); ?></h4>
				<select name="woocommerce_accounting_refunds_export_pay_opt" id="woocommerce_accounting_refunds_export_pay_opt"/>
				<option value="0" <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_pay_opt'))==0){ echo "selected";} ?>> <?php _e( 'Manual (No display)' , 'accounting-for-woocommerce'); ?> </option>
				<option value="1" <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_pay_opt'))==1){ echo "selected";} ?>> <?php _e( 'Same as Parent (General)' , 'accounting-for-woocommerce'); ?> </option>
				<option value="2" <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_pay_opt'))==2){ echo "selected";} ?>> <?php _e( 'Same as Parent (Personalised)' , 'accounting-for-woocommerce'); ?> </option>
				</select>
			</td>
			<td width="45">
				<h4><?php _e( 'Piece Number' , 'accounting-for-woocommerce'); ?></h4>
				<select name="woocommerce_accounting_refunds_export_factnum_opt" id="woocommerce_accounting_refunds_export_factnum_opt"/>
				<option value="0" <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_factnum_opt'))==0){ echo "selected";} ?>> <?php _e( 'Refund Invoice number (if exists)' , 'accounting-for-woocommerce'); ?> </option>
				<option value="1" <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_factnum_opt'))==1){ echo "selected";} ?>> <?php _e( 'Parent Order number (Refund Invoice num in Lib)' , 'accounting-for-woocommerce'); ?> </option>
				</select>
			</td>
		</tr>
	</table>
	<table>
		<tr valign="middle">
			<td width="15%">
				<h4><?php _e( 'Customers' , 'accounting-for-woocommerce'); ?></h4>
				<select name="woocommerce_accounting_refunds_export_cust_opt" id="woocommerce_accounting_refunds_export_cust_opt"/>
				<option value="0" <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_cust_opt'))==0){ echo "selected";} ?>> <?php _e( 'by Customer' , 'accounting-for-woocommerce'); ?> </option>
				<option value="1" <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_cust_opt'))==1){ echo "selected";} ?>> <?php _e( 'by Gateway type' , 'accounting-for-woocommerce'); ?> </option>
				</select>
			</td>
			<td width="15%">
				<h4><?php _e( 'Products' , 'accounting-for-woocommerce'); ?></h4>
				<select name="woocommerce_accounting_refunds_export_prod_opt" id="woocommerce_accounting_refunds_export_prod_opt"/>
				<option value="0" <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_prod_opt'))==0){ echo "selected";} ?>> <?php _e( 'Simple' , 'accounting-for-woocommerce'); ?> </option>
				<option value="1" <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_prod_opt'))==1){ echo "selected";} ?>> <?php _e( 'Detailed' , 'accounting-for-woocommerce'); ?> </option>
				<option value="2" <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_prod_opt'))==2){ echo "selected";} ?>> <?php _e( 'Simple by Country' , 'accounting-for-woocommerce'); ?> </option>
				</select>
			</td>
			<td width="15%">
				<h4><?php _e( 'Taxes' , 'accounting-for-woocommerce'); ?></h4>
				<select name="woocommerce_accounting_refunds_export_taxes_opt" id="woocommerce_accounting_refunds_export_taxes_opt"/>
				<option value="0" <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_taxes_opt'))==0){ echo "selected";} ?>> <?php _e( 'Simple' , 'accounting-for-woocommerce'); ?> </option>
				<option value="1" <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_taxes_opt'))==1){ echo "selected";} ?>> <?php _e( 'Detailed' , 'accounting-for-woocommerce'); ?> </option>
				</select>
			</td>
			<td width="15%">
				<h4><?php _e( 'Delivery Options' , 'accounting-for-woocommerce'); ?></h4>
				<select name="woocommerce_accounting_refunds_export_ship_opt" id="woocommerce_accounting_refunds_export_ship_opt"/>
				<option value="0" <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_ship_opt'))==0){ echo "selected";} ?>> <?php _e( 'Simple' , 'accounting-for-woocommerce'); ?> </option>
				<option value="1" <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_ship_opt'))==1){ echo "selected";} ?>> <?php _e( 'Detailed' , 'accounting-for-woocommerce'); ?> </option>
				</select>
			</td>
			<td width="15%">
				<h4><?php _e( 'Fill opposite amount with :' , 'accounting-for-woocommerce'); ?></h4>
				<select name="woocommerce_accounting_refunds_export_zero_opt" id="woocommerce_accounting_refunds_export_zero_opt"/>
				<option value="0" <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_zero_opt'))==0){ echo "selected";} ?>> <?php _e( 'Nothing' , 'accounting-for-woocommerce'); ?> </option>
				<option value="1" <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_zero_opt'))==1){ echo "selected";} ?>> <?php _e( 'Zero' , 'accounting-for-woocommerce'); ?> </option>
			</td>
		</tr>
		<tr>
			<td width="200">
				<h3><?php _e( 'Decimal Separator' , 'accounting-for-woocommerce'); ?></h3>
			</td>
			<td width="200">
				<h3><?php _e( 'CSV Separator' , 'accounting-for-woocommerce'); ?></h3>
			</td>
		</tr>
		<tr valign="middle">
			<td width="50">
				<select name="woocommerce_accounting_refunds_export_decimal_separator" id="woocommerce_accounting_refunds_export_decimal_separator"/>
					<option value="." <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_decimal_separator'))=="."){ echo "selected";} ?>> <?php _e( 'Period' , 'accounting-for-woocommerce'); ?> </option>
					<option value="," <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_decimal_separator'))==","){ echo "selected";} ?>> <?php _e( 'Comma' , 'accounting-for-woocommerce'); ?> </option>
				</select>
			</td>
			<td width="50">
				<select name="woocommerce_accounting_refunds_export_separator" id="woocommerce_accounting_refunds_export_separator"/>
				<option value="," <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_separator'))==","){ echo "selected";} ?>> <?php _e( 'Comma' , 'accounting-for-woocommerce'); ?> </option>
				<option value=";" <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_separator'))==";"){ echo "selected";} ?>> <?php _e( 'Semi-Colon' , 'accounting-for-woocommerce'); ?> </option>
				<option value="t" <?php if( esc_attr(get_option('woocommerce_accounting_refunds_export_separator'))=="t"){ echo "selected";} ?>> <?php _e( 'Tab' , 'accounting-for-woocommerce'); ?> </option>
				</select>
			</td>
			<td width="200">
			</td>
		</tr>
	</table>
	</details>
<?php submit_button( __( 'Export!' , 'accounting-for-woocommerce') ); ?>
<?php do_action('woocommerce_accounting:refunds_export_form:after_submit'); ?>
</form>

