<?php
/*
Plugin Name: WooCommerce Accounting
Text Domain: accounting-for-woocommerce
Description: All you need to transfer accounting data from Woocommerce to accounting softwares!
Author: N.O.U.S. Open Useful and Simple
Author URI: https://apps.avecnous.eu/?mtm_campaign=wp-plugin&mtm_kwd=accounting-for-woocommerce&mtm_medium=dashboard&mtm_source=author
Requires Plugins: woocommerce
Version: 1.6.5
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
    Forked from Ro_meow's WooCommerce Book-keeper

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2 and later, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

add_action('init', function(){
    // Check if WC is active
    if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
        return;
    }
    load_plugin_textdomain( 'accounting-for-woocommerce' );
    $inc_path = plugin_dir_path( __FILE__ ) . 'inc';
    $includes = scandir($inc_path);
    foreach($includes as $include){
        if(is_file("{$inc_path}/{$include}")){
            include "{$inc_path}/{$include}";
        }
    }
    add_action('admin_init', 'woocommerce_accounting_install');
    add_action ('admin_menu', 'woocommerce_accounting_exporter_page', 50);

    //actions for export
    add_action('admin_post_woocommerce_accounting_export','woocommerce_accounting_export_data');
    add_action('admin_post_woocommerce_accounting_refunds_export','woocommerce_accounting_export_refunds_data');


    add_filter('plugin_action_links_'.plugin_basename( __FILE__ ), 'woocommerce_accounting_plugin_settings_link' );
    add_filter('plugin_row_meta', 'woocommerce_accounting_plugin_row_meta', 10, 4 );
});

/**
 * Create the admin pages links
 * 
 * @return void
 */
function woocommerce_accounting_exporter_page() {
    //Menu pages
    add_submenu_page ('woocommerce', __( 'Accounting Export' , 'accounting-for-woocommerce' ), __( 'Accounting Export' , 'accounting-for-woocommerce' ), 'manage_woocommerce', 'woocommerce_accounting_exporter', 'woocommerce_accounting_exporter');
}

/**
 * Enqueue scripts and style
 * 
 * @return [type] [description]
 */
function woocommerce_accounting_load_export_scripts(){
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-sortable');
    wp_register_style('jquery-ui-css', plugins_url('assets/jquery-ui.css', __FILE__));
    wp_enqueue_style('jquery-ui-css');
}

/**
 * Displays export page
 * 
 * @return void
 */
function woocommerce_accounting_exporter() {
    if (! current_user_can('manage_woocommerce')) {
        wp_die( __('Insuffisant permissions', 'accounting-for-woocommerce') );
    }
    woocommerce_accounting_load_export_scripts();
    require ( dirname(__FILE__).'/views/export.php');
}

/**
 * Displays refund export page
 * 
 * @return void
 */
function woocommerce_accounting_exporter_refunds_export() {
    if (! current_user_can('manage_woocommerce')) {
        wp_die( __('Insuffisant permissions', 'accounting-for-woocommerce') );
    }
    woocommerce_accounting_load_export_scripts();
    require ( dirname(__FILE__).'/views/refunds-page.php');
}

/**
 * Called at plugin install.
 * Create default options
 * 
 * @return void
 */
function woocommerce_accounting_install() {
    $order_status = get_option('woocommerce_accounting_status');
    if (empty($order_status)){
        $order_status_install = array(
        'wc-completed',
        'wc-refunded'
        );
        update_option('woocommerce_accounting_status', $order_status_install);
    }
    //register our export settings
    add_option( 'woocommerce_accounting_export_start_date');
    add_option( 'woocommerce_accounting_export_end_date');
    add_option( 'woocommerce_accounting_export_date_format');
    add_option( 'woocommerce_accounting_export_separator');
    add_option( 'woocommerce_accounting_export_date_opt');
    add_option( 'woocommerce_accounting_export_pay_opt');
    add_option( 'woocommerce_accounting_export_prod_opt');
    add_option( 'woocommerce_accounting_export_taxes_opt');
    add_option( 'woocommerce_accounting_export_ship_opt');
    add_option( 'woocommerce_accounting_export_factnum_opt');
    add_option( 'woocommerce_accounting_export_cust_opt');
    add_option( 'woocommerce_accounting_export_decimal_separator');
    add_option( 'woocommerce_accounting_export_zero_opt');
    add_option( 'woocommerce_accounting_export_coupon_opt');

    //register our export settings
    add_option( 'woocommerce_accounting_refunds_export_start_date');
    add_option( 'woocommerce_accounting_refunds_export_end_date');
    add_option( 'woocommerce_accounting_export_refunds_date_format');
    add_option( 'woocommerce_accounting_refunds_export_separator');
    add_option( 'woocommerce_accounting_refunds_export_date_opt');
    add_option( 'woocommerce_accounting_refunds_export_pay_opt');
    add_option( 'woocommerce_accounting_refunds_export_prod_opt');
    add_option( 'woocommerce_accounting_refunds_export_taxes_opt');
    add_option( 'woocommerce_accounting_refunds_export_ship_opt');
    add_option( 'woocommerce_accounting_refunds_export_factnum_opt');
    add_option( 'woocommerce_accounting_refunds_export_cust_opt');
    add_option( 'woocommerce_accounting_refunds_export_decimal_separator');
    add_option( 'woocommerce_accounting_refunds_export_zero_opt');
}

/**
 * Settings link on the plugins page
 * 
 * @param array $plugin_links
 */
function woocommerce_accounting_plugin_settings_link( $plugin_links ) {
    $plugin_links['settings'] = '<a href="'.add_query_arg(array(
        'page'=>'wc-settings',
        'tab'=>'accounting',
    ), 'admin.php').'">'.__('Settings', 'accounting-for-woocommerce').'</a>';
    return $plugin_links;
}

/**
 * Add settings link in the plugins list
 * 
 * @param array $plugin_meta
 * @param string $plugin_file
 * @param array $plugin_data
 * @param string $status
 * 
 * @see `woocommerce_accounting:export_form:pro_features` filter
 * 
 * @return array
 */
function woocommerce_accounting_plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {
    if($plugin_file == plugin_basename( __FILE__ )){
        $pro_link = apply_filters('woocommerce_accounting:export_form:pro_features', '<a href="https://apps.avecnous.eu/produit/woocommerce-accounting/?mtm_campaign=wp-plugin&mtm_kwd=accounting-for-woocommerce" target="_blank">' . __( 'Pro addon', 'accounting-for-woocommerce' ) . '</a>');
        if($pro_link){
            $plugin_meta['pro_link'] = $pro_link;
        }
    }
    return $plugin_meta;
}

/**
 * [woocommerce_accounting_date_format description]
 * 
 * @param string $date
 * @param integer $format
 * 
 * @return string
 */
function woocommerce_accounting_date_format($date, $format){
    $formatted_date = $date;
    $time = strtotime( $formatted_date );
    //Date Format
    $order_year = date_i18n( 'Y', $time );
    $order_month = date_i18n( 'm', $time );
    $order_day = date_i18n ( 'd', $time ) ;
    if ($format == "1") {
    	$formatted_date = $order_day . '-' . $order_month . '-' . $order_year;
    } elseif ($format == "2") {
    	$formatted_date = $order_month . '-' . $order_day . '-' . $order_year;
    } elseif ($format == "3") {
    	$formatted_date = $order_year . '-' . $order_month . '-' . $order_day;
    } elseif ($format == "4") {
    	$formatted_date = $order_day . '/' . $order_month . '/' . $order_year;
    } elseif ($format == "5") {
    	$formatted_date = $order_month . '/' . $order_day . '/' . $order_year;
    } elseif ($format == "6") {
    	$formatted_date = $order_year . '/' . $order_month . '/' . $order_day;
    }

    return $formatted_date;
}

add_action('woocommerce_accounting:export_form:before_title', function (){
    if('no_orders' === filter_input(INPUT_GET, 'error')){
    ?>
       <div id='message' class='error fade'>
           <p>
               <?php _e('No data to export. Please try with another period.', 'accounting-for-woocommerce'); ?>
           </p>
       </div>
    <?php
    }
});
