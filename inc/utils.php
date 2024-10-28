<?php

function woocommerce_accounting_export_select_options($options, $option_name){
	$value = get_option("woocommerce_accounting_{$option_name}");
	$options = apply_filters("woocommerce_accounting_options_{$option_name}", $options);
	$html = '';
	foreach ($options as $key => $label) {
			$html.= '<option value="'.esc_attr($key).'" '.selected($key, $value, true).'>'.$label.'</option>';
	}
	return $html;
}

/**
 * Format number for export
 * 
 * @var $number float
 * 
 * @return string
 */
function woocommerce_accounting_format_number($number){
	global $rounding_precision;
	$dec_separator = get_option('woocommerce_accounting_export_decimal_separator');
	$zero_opt = get_option('woocommerce_accounting_export_zero_opt');
	if(empty($number)){
		if($zero_opt != 1){
			return '';
		}
		$number = 0.00;
	}
	return str_replace('.', $dec_separator, round((float) $number, $rounding_precision));
}

/**
 * Unformat number for export
 * 
 * @var $number float
 * 
 * @return float
 */
function woocommerce_accounting_unformat_number($string){
	return (float) str_replace(',', '.', $string);
}

function woocommerce_accounting_get_tax_id_from_order_item($order_item){
	$tax_id = '';
	$taxes = $order_item->get_taxes();
	if(isset($taxes['total']) && !empty($taxes['total'])){
		foreach($taxes['total'] as $tax_id => $tax_amount){
			if(!empty($tax_amount)){
				return $tax_id;
			}
		}
	}
	return $tax_id;
}

function woocommerce_accounting_add_line($output, $line, $separator, $force=false){
	global $total_income, $total_outcome;
	$col_map = get_option('woocommerce_accounting_colorder');
	$col_outcome = $col_map['Outcome'];
	$col_income = $col_map['Income'];
	$val_income = woocommerce_accounting_unformat_number($line[$col_income]);
	$total_income += $val_income;
	$val_outcome = woocommerce_accounting_unformat_number($line[$col_outcome]);
	$total_outcome += $val_outcome;
	if(abs($val_income)+abs($val_outcome) == 0 && !$force){
		return;
	}
	if(wp_get_environment_type() == 'local'){
		echo '<tr><td>'.implode('</td><td>', $line).'</td></tr>';
		return;
	}
	fputcsv($output, $line, $separator);
}


/**
 * Get orders
 * Uses high-performance order storage if enabled or falls back to WP_Query
 * 
 * @param array $args
 * 
 * @return array [WC_Order]
 */
function woocommerce_accounting_get_orders($args){
	
	$hpos = get_option('woocommerce_custom_orders_table_enabled');
	$wccutdse = get_option('woocommerce_custom_orders_table_data_sync_enabled');
	$wcOrders = [];

	if($hpos === 'yes' && $wccutdse === 'yes'){
		$wcOrders = wc_get_orders($args);
	}
	else{
		wp_trigger_error('woocommerce_accounting_get_orders', 'High-performance order storage and compatibility mode is required for accounting exports.', E_USER_NOTICE);

		$arg_mapping = [
			'limit' => 'posts_per_page',
			'type' => 'post_type',
			'status' => 'post_status',
			'orderby' => 'orderby',
			'order' => 'order',
			'meta_query' => 'meta_query',
		];

		$wp_args = [];

		foreach($arg_mapping as $key => $value){
			if(isset($args[$key])){
				$wp_args[$value] = $args[$key];
			}
		}

		$wp_date_fields = [
			'date_created' => 'post_date',
			'date_modified' => 'post_modified',
		];
		foreach($wp_date_fields as $date_field => $wp_date_field){
			if(isset($args[$date_field])){
				$range = explode('...', $args[$date_field]);
				$wp_args['date_query'][] = [
					'column' => $wp_date_field,
					'after' => $range[0],
					'before' => $range[1],
				];
			}
		}

		$wc_date_fields = [
			'date_paid' => '_paid_date',
			'date_completed' => '_completed_date',
		];
		foreach($wc_date_fields as $date_field => $wc_date_field){
			if(isset($args[$date_field])){
				$range = explode('...', $args[$date_field]);
				$wp_args['meta_query'][] = [
					'key' => $wc_date_field,
					'value' => $range,
					'compare' => 'BETWEEN',
                	'type' => 'DATE',
				];
			}
		}

		$posts = get_posts($wp_args);
		foreach($posts as $post){
			$wcOrders[] = wc_get_order($post->ID);
		}
	}

	return $wcOrders;
}