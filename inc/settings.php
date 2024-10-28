<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action( 'admin_init', 'register_woocommerce_accounting_settings' );
add_action( 'woocommerce_settings_save_accounting', 'woocommerce_accounting_update_settings_fields' );
register_woocommerce_accounting_settings_hooks();

add_action( 'load-woocommerce_page_wc-settings', 'woocommerce_accounting_add_help_tab' );
add_filter( 'woocommerce_settings_tabs_array', 'woocommerce_accounting_add_settings_tab', 50 );

add_filter('woocommerce_get_sections_accounting',  'woocommerce_accounting_add_sections', 10, 1 );

add_action( 'woocommerce_sections_accounting', 'woocommerce_accounting_output_sections' );
add_action( 'woocommerce_settings_accounting', 'woocommerce_accounting_output_settings' );

add_filter( 'woocommerce_accounting:settings:save:woocommerce_accounting_colorder', 'woocommerce_accounting_parse_setting_colorder', 50 );

/**
 * Add help tab in settings
 * 
 * @return void
 */
function woocommerce_accounting_add_help_tab () {
    $screen = get_current_screen();

    ob_start(function($str){
        return $str;
    });
    include dirname(__DIR__)."/views/settings-accounting-welcome.php";
    $help = ob_get_clean();

    $screen->add_help_tab( array(
        'id'    => 'accounting-for-woocommerce',
        'title' => __('Accounting', 'accounting-for-woocommerce'),
        'content'   => $help,
    ) );
}

/**
 * Get default columns
 * 
 * @see `woocommerce_accounting_columns`filter
 * 
 * @return array
 */
function woocommerce_accounting_get_columns(){
    return apply_filters('woocommerce_accounting_columns', array(
        'journal' =>'Journal',
        'date' =>'Date',
        'number' =>'Inv.Number',
        'code' =>'Acc.Code',
        'label' =>'Label',
        'outcome' =>'Outcome',
        'income' =>'Income',
        'center' =>'Cost Center',
        'empty1' =>sprintf('Empty %s', 1),
        'empty2' =>sprintf('Empty %s', 2),
        'empty3' =>sprintf('Empty %s', 3),
        'empty4' =>sprintf('Empty %s', 4),
    ));
}

/**
 * Get default column mapping
 * 
 * @see `woocommerce_accounting_default_col_mapping` filter
 * 
 * @return [type] [description]
 */
function woocommerce_accounting_get_default_mapping(){
    return apply_filters('woocommerce_accounting_default_col_mapping', array_flip(array_values(woocommerce_accounting_get_columns())));
}

/**
 * Parse column order settings and clean it up.
 * 
 * @param array $color_order
 * 
 * @return array
 */
function woocommerce_accounting_parse_setting_colorder($color_order){
    if(is_numeric(array_keys($color_order)[0])){
        $color_order = array_flip($color_order);
    }
    $default_col_order = array_flip(woocommerce_accounting_get_columns());
    foreach($color_order as $col=>$order){
        if(!isset($default_col_order[$col])){
            unset($color_order[$col]);
        }
    }
    return $color_order;
}

/**
 * Add Accounting tab to the settings page.
 *
 * @param array $setting_tabs The setting tabs array
 * 
 * @uses woocommerce_settings_tabs_array` filter
 * 
 * @return array $setting_tabs The setting tabs array
 */
function woocommerce_accounting_add_settings_tab( $setting_tabs ) {
  $setting_tabs['accounting'] = __( 'Accounting', 'accounting-for-woocommerce' );
  return $setting_tabs;
}

/**
 * Returns an array of associated sections/settings
 * 
 * @see woocommerce_accounting_section_settings` filter
 * 
 * @return array of sections / settings
 */
function woocommerce_accounting_get_settings_section(){
  return apply_filters('woocommerce_accounting_section_settings', array(
    'accounting-general' => array(
      'woocommerce_accounting_status',
      'woocommerce_accounting_status_code',
      'woocommerce_accounting_status_account',
      'woocommerce_accounting_generic_prod_accounting_account',
      'woocommerce_accounting_generic_prod_analytic_account',
      'woocommerce_accounting_generic_tax_accounting_account',
      'woocommerce_accounting_generic_fdp_accounting_account',
      'woocommerce_accounting_generic_fdp_analytic_account',
      'woocommerce_accounting_generic_discount_accounting_account',
      'woocommerce_accounting_generic_discount_analytic_account',
      'woocommerce_accounting_generic_discount_product_sku',
      'woocommerce_accounting_generic_cust_accounting_account',
      'woocommerce_accounting_generic_prod_accounting_account',
      'woocommerce_accounting_generic_prod_analytic_account',
      'woocommerce_accounting_generic_exptcred_accounting_account',
      'woocommerce_accounting_generic_exptchar_accounting_account',
      'woocommerce_accounting_book_code_order',
      'woocommerce_accounting_columns_headers',
      'woocommerce_accounting_lib_prefix',
      'woocommerce_accounting_colorder',
      'woocommerce_accounting_status',
      'woocommerce_accounting_status_code',
      'woocommerce_accounting_status_account',
    ),
  ));
}

function woocommerce_accounting_add_sections($sections){
    $sections['accounting-general'] = __('General', 'accounting-for-woocommerce');

    return $sections;
}

/**
 * Update Woocommerce Accounting settings.
 */
function woocommerce_accounting_update_settings_fields(){
  $section = filter_input(INPUT_POST, 'section');
  $sections = array_keys(apply_filters('woocommerce_get_sections_accounting', array()));
  if(!in_array($section, $sections)){
    return;
  }

  $settings = woocommerce_accounting_get_settings($section);

  foreach($settings as $setting){
    $setting_value = apply_filters("woocommerce_accounting:settings:save:{$setting}", isset($_POST[$setting]) ? woocommerce_accounting_recursive_sanitize_text_field($_POST[$setting]) : '');
    update_option( $setting, $setting_value);
  }
}

/**
 * Output sections.
 */
function woocommerce_accounting_output_sections() {
	global $current_section;

	$sections = apply_filters( 'woocommerce_get_sections_accounting', array() );

	if ( empty( $sections ) || 1 === sizeof( $sections ) ) {
		return;
	}

	echo '<ul class="subsubsub">';

	$array_keys = array_keys( $sections );

	foreach ( $sections as $id => $label ) {
		echo '<li><a href="' . admin_url( 'admin.php?page=wc-settings&tab=accounting&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
	}

	echo '</ul><br class="clear" />';
}

/**
 * Displays settings form
 * 
 * @see woocommerce_accounting_setting_template filter
 * 
 * @return void
 */
function woocommerce_accounting_output_settings(){
  global $current_section;
  if(empty($current_section)){
    $current_section = 'accounting-general';
  }
  echo '<input type="hidden" name="section" value="'.$current_section.'">';
  $template = apply_filters('woocommerce_accounting_setting_template', dirname(__DIR__)."/views/settings-{$current_section}.php", $current_section);
  if(file_exists($template)){
    include $template;
  }
}

/**
 * Gets current setting(s)
 * 
 * @param string $section
 * 
 * @return mixed string or array
 */
function woocommerce_accounting_get_settings($section=null){
  $section_settings =  woocommerce_accounting_get_settings_section();

  if($section && isset($section_settings[$section])){
    return $section_settings[$section];
  }

  $settings = array();
  foreach ($section_settings as $_settings){
    $settings = $settings+$_settings;
  }
  return $settings;
}

/**
 * Register hooks
 * 
 * @return void
 */
function register_woocommerce_accounting_settings_hooks() {
    $sections = woocommerce_accounting_get_settings_section();
    foreach($sections as $section=>$settings){
          add_action( 'woocommerce_settings_save_'.$section, 'woocommerce_accounting_update_settings_fields' );
    }
}

/**
 * Register settings
 * 
 * @return void
 */
function register_woocommerce_accounting_settings() {
  $settings = woocommerce_accounting_get_settings();
  foreach($settings as $setting){
    add_option($setting);
    register_setting( 'woocommerce-accounting-settings-group', $setting);
  }
}

/**
 * Recursive sanitization for an array
 * 
 * @param array|string $array
 * 
 * @return mixed array|string
 */
function woocommerce_accounting_recursive_sanitize_text_field($array) {
  if(is_array($array)){
    foreach ( $array as $key => &$value ) {
        if ( is_array( $value ) ) {
            $value = woocommerce_accounting_recursive_sanitize_text_field($value);
        }
        else {
            $value = sanitize_text_field( $value );
        }
    }
  }
  return $array;
}
