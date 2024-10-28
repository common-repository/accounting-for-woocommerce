<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$export_formats = apply_filters('woocommerce_accounting_export_formats', array(
	'basic'=>__('Basic', 'accounting-for-woocommerce'),
	'refunds'=>__('Refunds', 'accounting-for-woocommerce'),
));
$export_page = filter_input(INPUT_GET, 'format');
if(!$export_page){
	$export_page = array_keys($export_formats)[0];
}

$hpos = get_option('woocommerce_custom_orders_table_enabled');
$wccutdse = get_option('woocommerce_custom_orders_table_data_sync_enabled');
?>
<div class="wrap">
	<?php do_action('woocommerce_accounting:export_form:before_title'); ?>
	<h2><?php _e( 'Accounting Export' , 'accounting-for-woocommerce'); ?></h2>

	<?php if($hpos === 'no' || $wccutdse === 'no'): ?>
		<div class="notice notice-warning ">
			<?php if($hpos === 'no'): ?>
			<p>
				<?php _e("High-performance order storage is recommanded.", 'accounting-for-woocommerce-pro-addon'); ?>
			</p>
			<?php endif; ?>
			<?php if($wccutdse === 'no'): ?>
			<p>
				<?php _e("Enabling compatibility mode is highly recommanded for accounting exports.", 'accounting-for-woocommerce-pro-addon'); ?>
			</p>
			<?php endif; ?>
			<p>
				<a href="<?php echo esc_url(add_query_arg( array('page'=>'wc-settings', 'tab'=>'advanced', 'section'=>'features'), admin_url('admin.php'))); ?>">
					<?php _e('Go to WooCommerce settings', 'accounting-for-woocommerce-pro-addon'); ?>
				</a>
			</p>
		</div>
	<?php endif; ?>

	<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
	<?php foreach($export_formats as $name=>$label): ?>
		<?php if($name == $export_page): ?>
			<strong class="nav-tab nav-tab-active">
				<?php echo $label; ?>
			</strong>
		<?php else: ?>
			<a href="<?php echo add_query_arg('format', $name); ?>" class="nav-tab ">
				<?php echo $label; ?>
			</a>
		<?php endif; ?>
	<?php endforeach; ?>
	</nav>

    <?php
    $filename = apply_filters('woocommerce_accounting_view_export_page', plugin_dir_path( __FILE__ ) . "export-{$export_page}.php", $export_page);
    include $filename;
    ?>
</div>
