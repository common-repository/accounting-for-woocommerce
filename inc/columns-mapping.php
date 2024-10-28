<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('admin_init','woocommerce_accounting_col_mapping_install');
function woocommerce_accounting_col_mapping_install() {
    $col_map_opt = get_option('woocommerce_accounting_colorder');
    if (empty($col_map_opt)){
        $col_map_list = woocommerce_accounting_get_default_mapping();
        update_option('woocommerce_accounting_colorder', $col_map_list);
    }
}
