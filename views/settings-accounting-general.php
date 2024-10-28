<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<table width="90%">
  <tr>
    <td colspan="4">
      <h3><?php _e( 'Order statuses' , 'accounting-for-woocommerce'); ?></h3>
    </td>
  </tr>
  <tr>
    <td><strong><?php _e( 'Order status' , 'accounting-for-woocommerce'); ?></strong></td>
    <td><strong><?php _e( 'Book name' , 'accounting-for-woocommerce'); ?></strong></td>
    <td colspan="2"><strong><?php _e( 'Account number' , 'accounting-for-woocommerce'); ?></strong></td>
  </tr>

      <?php
      $order_statuses = wc_get_order_statuses();
      $status_checked = get_option('woocommerce_accounting_status');
      $status_code = get_option('woocommerce_accounting_status_code');
      $status_account = get_option('woocommerce_accounting_status_account');
      $header = get_option('woocommerce_accounting_columns_headers');
      $maps = get_option('woocommerce_accounting_colorder');
      if(is_numeric(array_keys($maps)[0])){
          $maps = array_flip($maps);
      }

      $columns = woocommerce_accounting_get_columns();
      $column_name_headers = array_flip($columns);

      foreach ($order_statuses as $key_status => $order_status) {
        ?>
        <tr>
          <td>
            <input type="checkbox" name="woocommerce_accounting_status[<?php echo ($key_status ?? '') ;?>]" id="woocommerce_accounting_status_<?php echo ($key_status ?? '') ;?>" value="<?php echo $key_status ;?>" <?php  if(is_array ($status_checked)) { if (in_array($key_status,$status_checked)) { echo 'checked' ;} } ?>/><?php echo $order_status ; ?>
          </td>
          <td>
            <input type="text" name="woocommerce_accounting_status_code[<?php echo ($key_status ?? '') ;?>]" id="woocommerce_accounting_status_code_<?php echo ($key_status ?? '') ;?>" value="<?php echo ($status_code[$key_status] ?? ''); ?>">
          </td>
          <td colspan="2">
            <input type="text" name="woocommerce_accounting_status_account[<?php echo ($key_status ?? '') ;?>]" id="woocommerce_accounting_status_account_<?php echo $key_status ;?>" value="<?php echo ($status_account[$key_status] ?? ''); ?>">
          </td>
        </tr>
      <?php } ?>

    <!--New products accounts-->
    <tr>
      <td width="200">
        <h3><?php _e( 'Products' , 'accounting-for-woocommerce'); ?></h3>
      </td>
    </tr>
    <tr valign="middle">
      <th width="150" scope="row"><label for="woocommerce_accounting_generic_prod_accounting_account"><?php _e( 'Account code - Product' , 'accounting-for-woocommerce'); ?></label></th>
      <td width="50">
        <input type="text" name="woocommerce_accounting_generic_prod_accounting_account" id="woocommerce_accounting_generic_prod_accounting_account" placeholder="ex : 707" value="<?php echo esc_attr(get_option('woocommerce_accounting_generic_prod_accounting_account')); ?>" /><br/><span class="description"></span>
      </td>
      <th width="150" scope="row"><label for="woocommerce_accounting_generic_prod_analytic_account"><?php _e( 'Cost center - Product' , 'accounting-for-woocommerce'); ?></label></th>
      <td width="50">
        <input type="text" name="woocommerce_accounting_generic_prod_analytic_account" id="woocommerce_accounting_generic_prod_analytic_account" placeholder="ex : 9101" value="<?php echo esc_attr(get_option('woocommerce_accounting_generic_prod_analytic_account')); ?>" /><br/><span class="description"></span>
      </td>
      <?php do_action('woocommerce_accounting:setting_form:general:product'); ?>
    </tr>

    <!--New customers accounts-->
    <tr>
      <td width="200">
        <h3><?php _e( 'Customers' , 'accounting-for-woocommerce'); ?></h3>
      </td>
    </tr>
    <tr valign="middle">
      <th width="150" scope="row"><label for="woocommerce_accounting_generic_cust_accounting_account"><?php _e( 'Account code - Customer' , 'accounting-for-woocommerce'); ?></label></th>
      <td width="50">
        <input type="text" name="woocommerce_accounting_generic_cust_accounting_account" id="woocommerce_accounting_generic_cust_accounting_account" placeholder="ex : 411COM" value="<?php echo esc_attr(get_option('woocommerce_accounting_generic_cust_accounting_account')); ?>" /><br/><span class="description"></span>
      </td>
      <?php do_action('woocommerce_accounting:setting_form:general:customer'); ?>
    </tr>


    <!--Taxes  accounts-->
    <tr>
      <td width="200">
        <h3><?php _e( 'Taxes' , 'accounting-for-woocommerce'); ?></h3>
      </td>
    </tr>
    <tr valign="middle">
      <th width="150" scope="row"><label for="woocommerce_accounting_generic_tax_accounting_account"><?php _e( 'Account code - Tax' , 'accounting-for-woocommerce'); ?></label></th>
      <td width="50">
        <input type="text" name="woocommerce_accounting_generic_tax_accounting_account" id="woocommerce_accounting_generic_tax_accounting_account" placeholder="ex : 445" value="<?php echo esc_attr(get_option('woocommerce_accounting_generic_tax_accounting_account')); ?>" />
      </td>
      
      <?php do_action('woocommerce_accounting:setting_form:general:tax'); ?>
    </tr>
    
    <!--Shipping accounts-->
    <tr>
      <td width="200">
        <h3><?php _e( 'Shipping and delivery' , 'accounting-for-woocommerce'); ?></h3>
      </td>
    </tr>
    <tr valign="top">
      <th width="150" scope="row"><label for="woocommerce_accounting_generic_fdp_accounting_account"><?php _e( 'Account code - Delivery options' , 'accounting-for-woocommerce'); ?></label></th>
      <td width="50">
        <input type="text" name="woocommerce_accounting_generic_fdp_accounting_account" id="woocommerce_accounting_generic_fdp_accounting_account" placeholder="ex : 708" value="<?php echo esc_attr(get_option('woocommerce_accounting_generic_fdp_accounting_account')); ?>" />
      </td>
      <th width="150" scope="row"><label for="woocommerce_accounting_generic_fdp_analytic_account"><?php _e( 'Cost center - Delivery options' , 'accounting-for-woocommerce'); ?></label></th>
      <td width="50">
        <input type="text" name="woocommerce_accounting_generic_fdp_analytic_account" id="woocommerce_accounting_generic_fdp_analytic_account" placeholder="ex : 901" value="<?php echo esc_attr(get_option('woocommerce_accounting_generic_fdp_analytic_account')); ?>" />
      </td>
        <?php do_action('woocommerce_accounting:setting_form:general:shipping'); ?>
      </tr>
      
    <!--Coupon accounts-->
    <tr>
      <td width="200">
        <h3><?php _e( 'Discount' , 'accounting-for-woocommerce'); ?></h3>
      </td>
    </tr>
    <tr valign="middle">
      <th width="150" scope="row"><label for="woocommerce_accounting_generic_discount_accounting_account"><?php _e( 'Account code - Discount' , 'accounting-for-woocommerce'); ?></label></th>
      <td width="50">
        <input type="text" name="woocommerce_accounting_generic_discount_accounting_account" id="woocommerce_accounting_generic_discount_accounting_account" placeholder="ex : 445" value="<?php echo esc_attr(get_option('woocommerce_accounting_generic_discount_accounting_account')); ?>" />
      </td>
      <th width="150" scope="row"><label for="woocommerce_accounting_generic_discount_analytic_account"><?php _e( 'Cost center - Discount' , 'accounting-for-woocommerce'); ?></label></th>
      <td width="50">
        <input type="text" name="woocommerce_accounting_generic_discount_analytic_account" id="woocommerce_accounting_generic_discount_analytic_account" placeholder="ex : 708" value="<?php echo esc_attr(get_option('woocommerce_accounting_generic_discount_analytic_account')); ?>" />
      </td>
    </tr>
    
    <tr valign="top">
      <th width="150" scope="row"><label for="woocommerce_accounting_generic_discount_product_sku"><?php _e( 'Product SKU - Discount' , 'accounting-for-woocommerce'); ?></label></th>
      <td width="50">
        <input type="text" name="woocommerce_accounting_generic_discount_product_sku" id="woocommerce_accounting_generic_discount_product_sku" placeholder="ex : 901" value="<?php echo esc_attr(get_option('woocommerce_accounting_generic_discount_product_sku')); ?>" />
      </td>
      <th width="150" scope="row"></th>
      <td width="50">
      </td>
      <?php do_action('woocommerce_accounting:setting_form:general:coupons'); ?>
    </tr>

    <!--Journal Code-->
    <tr>
      <td width="200">
        <h3><?php _e( 'Book reference number' , 'accounting-for-woocommerce'); ?></h3>
      </td>
    </tr>
    <tr>
    <th width="150" scope="row"><label for="woocommerce_accounting_book_code_order"><?php _e( 'Sales journal reference number' , 'accounting-for-woocommerce'); ?></label></th>
    <td width="50">
      <input type="text" name="woocommerce_accounting_book_code_order" id="woocommerce_accounting_book_code_order" placeholder="ex : VT" value="<?php echo esc_attr(get_option('woocommerce_accounting_book_code_order')); ?>" />
    </td>
  </tr>
  <tr valign="top">
    <th width="150" scope="row"></th>
    <td width="50">
    </td>
  </tr>
  <tr>
    <td width="200">
      <h3><?php _e( 'Description Prefix' , 'accounting-for-woocommerce'); ?></h3>
    </td>
  </tr>

  <!--Label prefix-->
  <tr>
      <th width="150" scope="row"><label for="woocommerce_accounting_lib_prefix"><?php _e( 'Description prefix' , 'accounting-for-woocommerce'); ?></label></th>
      <td width="50">
        <input type="text" name="woocommerce_accounting_lib_prefix" id="woocommerce_accounting_lib_prefix" placeholder="ex : SELL" value="<?php echo esc_attr(get_option('woocommerce_accounting_lib_prefix')); ?>" />
      </td>
    </tr>
    <tr valign="top">
      <th width="150" scope="row"></th>
      <td width="50">
      </td>
    </tr>
    <!-- Exceptionnal accounts -->
    <tr>
      <td width="200">
        <h3><?php _e( 'Exceptional incomes and costs' , 'accounting-for-woocommerce'); ?></h3>
      </td>
    </tr>
    <tr valign="middle">
      <th width="150" scope="row"><label for="woocommerce_accounting_generic_exptcred_accounting_account"><?php _e( 'Exceptional incomes' , 'accounting-for-woocommerce'); ?></label></th>
      <td width="50">
        <input type="text" name="woocommerce_accounting_generic_exptcred_accounting_account" id="woocommerce_accounting_generic_exptcred_accounting_account" placeholder="ex : 401" value="<?php echo esc_attr(get_option('woocommerce_accounting_generic_exptcred_accounting_account')); ?>" /><br/><span class="description"></span>
      </td>
    </tr>
    <tr valign="middle">
      <th width="150" scope="row"><label for="woocommerce_accounting_generic_exptchar_accounting_account"><?php _e( 'Exceptional costs' , 'accounting-for-woocommerce'); ?></label></th>
      <td width="50">
        <input type="text" name="woocommerce_accounting_generic_exptchar_accounting_account" id="woocommerce_accounting_generic_exptchar_accounting_account" placeholder="ex : 658" value="<?php echo esc_attr(get_option('woocommerce_accounting_generic_exptchar_accounting_account')); ?>" /><br/><span class="description"></span>
      </td>
    </tr>
</table>
<!-- Columns order -->
<h3><?php _e( 'Columns Name & Order' , 'accounting-for-woocommerce'); ?></h3>

<ul name="woocommerce_accounting_colorder_col" id="woocommerce_accounting_colorder_col"/>
<?php foreach ($maps as $key => $map): ?>
    <li id="<?php echo esc_attr($key); ?>">
        <span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
        <input type="text" name="woocommerce_accounting_colorder[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($map); ?>" class="woocommerce_accounting_colorder">
        <input type="text" name="woocommerce_accounting_columns_headers[<?php echo esc_attr($column_name_headers[$key]); ?>]" id="woocommerce_accounting_columns_headers_<?php echo esc_attr($column_name_headers[$key]); ?>" value="<?php echo esc_attr((isset($column_name_headers[$key]) && isset($header[$column_name_headers[$key]])) ? $header[$column_name_headers[$key]] : ''); ?>" />
        <?php _e(  $key , 'accounting-for-woocommerce'); ?>
    </li>
<?php endforeach; ?>
</ul>

<div id="woocommerce_accounting_colorder_notice"><div class="inline"></div></div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $( '#woocommerce_accounting_colorder_col' ).sortable({
        opacity: 0.6,
        axis: 'y',
        update: function() {
            var col_order = 0;
            jQuery('#woocommerce_accounting_colorder_col .woocommerce_accounting_colorder').each(function(){
                col_order++;
                jQuery(this).val(col_order);
            });
        }
    });
});
</script>
<style>
    #woocommerce_accounting_colorder_col { list-style-type: none; margin: 0; padding: 0; width: 60%; }
    #woocommerce_accounting_colorder_col li { position: relative; margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 2em; font-size: 1.2em; background-color:#e1e1e1; color:#333; cursor: grab;}
    #woocommerce_accounting_colorder_col li span.ui-icon { position: absolute; margin-left: -1.3em; top: 0.8em; }
    .woocommerce_accounting_colorder{
        all:unset !important;
        border: none !important;
        background: transparent !important;
        width: 30px !important;
        color: #333 !important;
    }
</style>
<?php // translators: %s: link to pro addon ?>
<?php echo apply_filters('woocommerce_accounting:export_form:pro_features', '<p>'.sprintf(__('More options available with the <a href="%s" target="_blank">pro addon</a>.', 'accounting-for-woocommerce'), 'https://apps.avecnous.eu/produit/woocommerce-accounting/?mtm_campaign=wp-plugin&mtm_kwd=accounting-for-woocommerce').'</p>'); ?>
