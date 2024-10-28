<?php 
if ( ! defined( 'ABSPATH' ) ) exit;
global $total_income;
global $total_outcome;
global $separator;
global $output;
global $small_number;
global $rounding_precision;

// Manage 2.1E-5 syntax
$small_number = 0.0001;

$rounding_precision = 2;

function woocommerce_accounting_export_data() {
    global $woocommerce, $post;
    global $total_income, $total_outcome, $separator, $output;
    global $small_number;
    global $rounding_precision;
    
    //test nonce
    if (!isset( $_POST['_check_export'] ) || ! wp_verify_nonce( $_POST['_check_export'], 'check_nonce_export')){
        wp_die('Sorry, your nonce did not verify.');
        exit;
    }

    $separator = sanitize_text_field($_POST['woocommerce_accounting_export_separator']);
    $dec_separator = sanitize_text_field($_POST['woocommerce_accounting_export_decimal_separator']);
    $ts1 = sanitize_text_field($_POST['woocommerce_accounting_export_start_date']);
    $ts2 = sanitize_text_field($_POST['woocommerce_accounting_export_end_date']);
    $date_opt = sanitize_text_field($_POST['woocommerce_accounting_export_date_opt']);
    $date_format_opt = sanitize_text_field($_POST['woocommerce_accounting_export_date_format']);
    $cust_opt = sanitize_text_field($_POST['woocommerce_accounting_export_cust_opt']);
    $pay_opt = sanitize_text_field($_POST['woocommerce_accounting_export_pay_opt']);
    $prod_opt = sanitize_text_field($_POST['woocommerce_accounting_export_prod_opt']);
    $taxes_opt = sanitize_text_field($_POST['woocommerce_accounting_export_taxes_opt']);
    $ship_opt = sanitize_text_field($_POST['woocommerce_accounting_export_ship_opt']);
    $factnum_opt = sanitize_text_field($_POST['woocommerce_accounting_export_factnum_opt']);
    $zero_opt = sanitize_text_field($_POST['woocommerce_accounting_export_zero_opt']);
    $coupon_col = sanitize_text_field($_POST['woocommerce_accounting_export_coupon_col']);
    $coupon_opt = sanitize_text_field($_POST['woocommerce_accounting_export_coupon_opt']);
    $rounding_precision = (int) sanitize_text_field($_POST['woocommerce_accounting_export_rounding_precision']);

    update_option( 'woocommerce_accounting_export_start_date',$ts1);
    update_option( 'woocommerce_accounting_export_end_date',$ts2);
    update_option( 'woocommerce_accounting_export_date_format',$date_format_opt);
    update_option( 'woocommerce_accounting_export_separator',$separator);
    update_option( 'woocommerce_accounting_export_date_opt',$date_opt);
    update_option( 'woocommerce_accounting_export_pay_opt',$pay_opt);
    update_option( 'woocommerce_accounting_export_prod_opt',$prod_opt);
    update_option( 'woocommerce_accounting_export_taxes_opt',$taxes_opt);
    update_option( 'woocommerce_accounting_export_ship_opt',$ship_opt);
    update_option( 'woocommerce_accounting_export_factnum_opt',$factnum_opt);
    update_option( 'woocommerce_accounting_export_cust_opt',$cust_opt);
    update_option( 'woocommerce_accounting_export_decimal_separator',$dec_separator);
    update_option( 'woocommerce_accounting_export_zero_opt',$zero_opt);
    update_option( 'woocommerce_accounting_export_coupon_col',$coupon_col);
    update_option( 'woocommerce_accounting_export_coupon_opt',$coupon_opt);
    update_option( 'woocommerce_accounting_export_rounding_precision',$rounding_precision);

    if ($separator == "t") {
        $separator = "\t";
    }

    $order_status = get_option('woocommerce_accounting_status');

    // Use WC Order Query style
    // https://github.com/woocommerce/woocommerce/wiki/wc_get_orders-and-WC_Order_Query#general

    $date_options = array(
        '0' => 'date_created',
        '1' => 'date_completed',
        '3' => 'date_paid',
    );

    $order_args = array(
        'limit' => -1,
		'type' => 'shop_order',
        'status' => $order_status,
        'orderby' => 'ID',
        'order' => 'ASC',
    );

    if(isset($date_options[$date_opt])){
        $order_args[$date_options[$date_opt]] = "{$ts1}...{$ts2}";
    }

    $order_args = apply_filters('woocommerce_accounting:export:get_orders_args', $order_args, $date_opt, $order_status, array($ts1, $ts2));
    $orders = woocommerce_accounting_get_orders($order_args);
    do_action('woocommerce_accounting:export:launch', $orders);
    $compiledOrders = array();

    if(!empty($orders)){
        //Generic accounts
        $csv_headers = get_option ('woocommerce_accounting_columns_headers');
        $original_book_code = get_option('woocommerce_accounting_book_code_order');
        $book_code_status = get_option('woocommerce_accounting_status_code');
        $book_account_status = get_option('woocommerce_accounting_status_account');
        $gen_account_cust = get_option('woocommerce_accounting_generic_cust_accounting_account');
        $gen_account_prod = get_option ('woocommerce_accounting_generic_prod_accounting_account');
        $gen_account_fdp = get_option ('woocommerce_accounting_generic_fdp_accounting_account');
        $gen_account_tax = get_option ('woocommerce_accounting_generic_tax_accounting_account');
        $gen_analytic_prod = get_option ('woocommerce_accounting_generic_prod_analytic_account');
        $gen_analytic_fdp = get_option('woocommerce_accounting_generic_fdp_analytic_account');
        $gen_account_discount = get_option('woocommerce_accounting_generic_discount_accounting_account');
        $gen_analytic_discount = get_option('woocommerce_accounting_generic_discount_analytic_account');
        $gen_sku_discount = get_option('woocommerce_accounting_generic_discount_product_sku');
        $order_shipping_acc_list = get_option ('woocommerce_accounting_shipping_methods_accounting_account');
        $pdfoptions = get_option('wpo_wcpdf_template_settings');
        $gateways_libs = get_option('woocommerce_accounting_gateways_libs');
        $lib_prefix = get_option ('woocommerce_accounting_lib_prefix');
        $order_country_acc = get_option('woocommerce_accounting_countries_acc');

    }

    foreach ($orders as $wcOrder)
    {
        // skip order if it is a refund
        if($wcOrder instanceof \WC_Order_Refund){
            continue;
        }
        $order = (object) array();
        $order->WCOrder = $wcOrder;
        $order->ID = $wcOrder->get_id();
        $order->status = 'wc-'.$wcOrder->get_status();
        $order->date_created = $wcOrder->get_date_created();
        $order->date_completed = $wcOrder->get_date_completed();
        $order->date_paid = $wcOrder->get_date_paid();

        if ($date_opt == "0") {
            $order->piecedate =  $order->date_created;
        }
        elseif ($date_opt == "1"){
            $order->piecedate = $order->date_completed;
        }
        elseif ($date_opt == "3"){
            $order->piecedate = $order->date_paid;
        }
        else {
            $invoice_date = $wcOrder->get_meta('_wcpdf_invoice_date_formatted', true);
            if (!empty($invoice_date)) {
                $order->piecedate = $invoice_date;
            }
            else {
                $order->piecedate = $wcOrder->get_date_created();
            }
        }
        $order->original_date = $order->piecedate;

        $order->total_tax = (float) $wcOrder->get_total_tax();
        $order->shipping_tax = (float) $wcOrder->get_shipping_tax();

        $order->outcome = round($wcOrder->get_total(), $rounding_precision);
        $order->income_tax = round($order->total_tax > $small_number ?  $order->total_tax : 0, $rounding_precision);
        $order->income_fdpht = round($wcOrder->get_total_shipping(), $rounding_precision);
        $order->income_prodht = ( ($order->outcome) - ( ($order->income_tax) + ($order->income_fdpht) ) );
        //WC pre 2.3 bug on coupons correcting to match legal statements

        $order->order_discount = (float) $wcOrder->get_total_discount();

        $order->gatewayid = $wcOrder->get_payment_method();

        if (isset($wcOrder->bypass))
        {
            $order->bypass = $wcOrder->bypass;
        }

        $order->number = $order->ID;

        //Customers exports options
        $order->account_cust = $gen_account_cust;
        if ($cust_opt == "0") {
            $order->account_cust = $gen_account_cust;
        }
        else if ($cust_opt == "1") {
            if (!empty($gateways_libs[$order->gatewayid]['acc'])) {
                $order->account_cust = $gateways_libs[$order->gatewayid]['acc'];
            }
        }

        // Get the country only in label if prod by country
        if ($prod_opt == "2") {
            $country_label = $wcOrder->get_shipping_country();
        }
        else {
            $country_label = "";
        }
        //Payment gateway indication in label
        if ($pay_opt == "1") {
            $pay_meth = substr($order->gatewayid, 0, 1) . substr($order->gatewayid, 3, 1) . substr($order->gatewayid, -1);
            $order->lib = remove_accents (strtoupper($pay_meth) . ' ' .  $lib_prefix . ' ' . strtoupper($wcOrder->get_billing_company()) . ' ' . ucfirst($wcOrder->get_billing_last_name()) . ' ' . ucfirst($wcOrder->get_billing_first_name()) . ' ' . $country_label);
            $order->lib = trim($order->lib);
        }
        elseif ($pay_opt == "2") {
            if (!empty ($gateways_libs[$order->gatewayid]['lib'])) {
                $listed_pay_lib = $gateways_libs[$order->gatewayid]['lib'];
                $pay_meth = $listed_pay_lib;
            }
            else {
                $pay_meth = substr($order->gatewayid, 0, 1) . substr($order->gatewayid, 3, 1) . substr($order->gatewayid, -1);
            };
            $order->lib = remove_accents (strtoupper($pay_meth) . ' ' .  $lib_prefix . ' ' . strtoupper($wcOrder->get_billing_company()) . ' ' . ucfirst($wcOrder->get_billing_last_name()) . ' ' . ucfirst($wcOrder->get_billing_first_name()) . ' ' . $country_label);
            $order->lib = trim($order->lib);
        }
        else {
            $order->lib = remove_accents ($lib_prefix . ' ' . strtoupper($wcOrder->get_billing_company()) . ' ' . ucfirst($wcOrder->get_billing_last_name()) . ' ' . ucfirst($wcOrder->get_billing_first_name()) . ' ' . $country_label);
            $order->lib = trim($order->lib);
        }

        if ( $factnum_opt == 1 ){

            if  ( (!empty ($invoice_number) ) && ( empty ($invoice_number_data) ) ) {
                //Creating invoices numbers
                $prefix = $pdfoptions['invoice_number_formatting_prefix'];
                $suffix = $pdfoptions['invoice_number_formatting_suffix'];
                $padding = $pdfoptions['invoice_number_formatting_padding'];

                $order_year = date_i18n( 'Y', strtotime( $order->piecedate ) );
                $order_month = date_i18n( 'm', strtotime( $order->piecedate ) );
                $order_day = date_i18n( 'd', strtotime( $order->piecedate ) );
                $invoice_year = date_i18n( 'Y', strtotime( $invoice_date ) );
                $invoice_month = date_i18n( 'm', strtotime( $invoice_date ) );
                $invoice_day = date_i18n( 'd', strtotime( $invoice_date ) );


                $formats['prefix'] = isset($prefix)?$prefix:'';
                $formats['suffix'] = isset($suffix)?$suffix:'';
                $formats['padding'] = isset($padding)?$padding:'';

                // Invoices dates replacements in invoices
                foreach ($formats as $key => $value) {
                    $value = str_replace('[order_year]', $order_year, $value);
                    $value = str_replace('[order_month]', $order_month, $value);
                    $value = str_replace('[order_day]', $order_day, $value);
                    $value = str_replace('[invoice_year]', $invoice_year, $value);
                    $value = str_replace('[invoice_month]', $invoice_month, $value);
                    $value = str_replace('[invoice_day]', $invoice_day, $value);
                    $formats[$key] = $value;
                }

                // Padding
                if ( ctype_digit( (string)$formats['padding'] ) && $formats['padding'] > 3 ) {
                    $invoice_number = sprintf('%0'.$formats['padding'].'d', $invoice_number);
                }
                $formatted_invoice_number = $formats['prefix'] . $invoice_number . $formats['suffix'] ;
            }
            elseif ( !empty ($invoice_number)  ) {
                $formatted_invoice_number = $invoice_number;
            }

            if (isset($formatted_invoice_number)){
                $order->lib = $formatted_invoice_number . ' ' . $order->lib;
            }
        }

        //Date Format
        $order->piecedate = woocommerce_accounting_date_format($order->piecedate, $date_format_opt);
        $order->date_format_opt = $date_format_opt;

        if ($order->order_discount > 0) {
            $order->order_ex_discount = $order->order_discount + $order->outcome;

            //Calculating
            $order->prodht_exdisc = $order->order_ex_discount - $order->income_tax - $order->income_fdpht ;
            if($taxes_opt == "1"){
                //Shipment
                $quotient_fdp = $order->income_fdpht / $order->order_ex_discount;
                //Products
                $quotient_prod = $order->prodht_exdisc / $order->order_ex_discount;
                //Taxes
                $quotient_tax = $order->income_tax / $order->order_ex_discount;

                //Recalculate the right amounts
                //Products
                $order->income_prodht = $order->outcome * $quotient_prod ;
                //Shipment
                $order->income_fdpht = $order->outcome * $quotient_fdp ;
                //Taxes
               $order->income_tax = $order->outcome * $quotient_tax;
            }
        }


        $order = apply_filters('woocommerce_accounting:export:order', $order, $wcOrder);
        do_action_ref_array('woocommerce_accounting:export:order_values', array(&$order));

        $compiledOrders[] = $order;
    }

    //Collecting datas
    if (!empty($orders)) {
        $output = fopen("php://output",'w') or die ("Can't open php://output");
        //CSV Headers
        $filename = apply_filters('woocommerce_accounting:export:filename', 'woocommerce-accounting-export-'. parse_url( get_site_url(), PHP_URL_HOST ) . '-' . $ts1 . '-' . $ts2 . '-' .date('ymdHis') .'.csv', $ts1, $ts2);
        if(wp_get_environment_type() == 'local'){
            header( 'Content-Disposition: inline; filename='.$filename );
            header( 'Content-type: text/html' );
            echo '<table  width="100%" cellspacing="2px;">';
        }
        else{
            header( 'Content-type: application/csv' );
            header( 'Content-Disposition: attachment; filename='.$filename );
            header('Content-Transfer-Encoding: UTF-8');
            header( 'Pragma: no-cache' );
            header( 'Expires: 0' );
            echo "\xEF\xBB\xBF";
        }

        // CSV Columns headers
        $csv_journal = !empty($csv_headers['journal']) ? $csv_headers['journal'] : $csv_journal = 'Code_Journal';
        $csv_date = !empty($csv_headers['date']) ? $csv_headers['date'] : 'Date_de_piece';
        $csv_number = !empty($csv_headers['number']) ? $csv_headers['number'] : 'Numero_de_piece';
        $csv_code = !empty($csv_headers['code']) ? $csv_headers['code'] : 'Compte_Comptable';
        $csv_label = !empty($csv_headers['label']) ? $csv_headers['label'] : 'Libelle';
        $csv_outcome = !empty($csv_headers['outcome']) ? $csv_headers['outcome'] : 'Debit';
        $csv_income = !empty($csv_headers['income']) ? $csv_headers['income'] : 'Credit';
        $csv_center = !empty($csv_headers['center']) ? $csv_headers['center'] : 'Code_Analytique';

        $fillwith = $zero_opt == "1" ? 0.00 : '';

        $col_map = get_option('woocommerce_accounting_colorder');
        if(is_numeric(array_keys($col_map)[0])){
            $col_map = array_flip($col_map);
        }

        //Index table to order columns
        $header_line = array(
            $col_map['Journal'] =>    $csv_journal,
            $col_map['Date'] =>    $csv_date,
            $col_map['Inv.Number'] =>    $csv_number,
            $col_map['Acc.Code'] =>    $csv_code,
            $col_map['Label'] =>    $csv_label,
            $col_map['Outcome'] =>    $csv_outcome,
            $col_map['Income'] =>    $csv_income,
            $col_map['Cost Center'] =>    $csv_center,
            $col_map['Empty 1'] =>    "",
            $col_map['Empty 2'] =>    "",
            $col_map['Empty 3'] =>    "",
            $col_map['Empty 4'] =>    ""
        );

        ksort($header_line);
        woocommerce_accounting_add_line( $output, $header_line, $separator, true);

        do_action_ref_array('woocommerce_accounting:export:after_headline', array(&$output, $col_map, $separator));

        foreach($compiledOrders as $order) {
            $total_income = 0;
            $total_outcome = 0;
            $tax_line_amount = 0;
            $tax_displays = array ();
            if(isset($order->bypass) && $order->bypass){
                do_action_ref_array('woocommerce_accounting:export:after_order_output', array(&$output, $order, $separator));
                continue;
            }

            $get_order = $order->WCOrder;
            if(isset($book_code_status[$order->status]) && $book_code_status[$order->status] != ""){
                $book_code = $book_code_status[$order->status];
            }
            else{
                $book_code = $original_book_code;
            }
            /*-----------------------------------------------------------------------------*/
            /*----------------------- Ajout des taxes spécifiques -------------------------*/
            /*-----------------------------------------------------------------------------*/

            $tax_applied_check = ($order->total_tax > $small_number ?  $order->total_tax : 0) + ($order->shipping_tax > $small_number ?  $order->shipping_tax : 0);
            // Tax application test
            if ($tax_applied_check != 0 && $taxes_opt == "1"){
                // Finding the right order taxes
                foreach ($get_order->get_taxes() as $get_order_tax) {
                    $tax_rate_id = $get_order_tax['rate_id'];
                    $tax_line_amount = ((float) $get_order_tax['tax_amount'] > $small_number ? (float) $get_order_tax['tax_amount'] : 0) + ((float) $get_order_tax['shipping_tax_amount'] > $small_number ? (float) $get_order_tax['shipping_tax_amount'] : 0);
                    if($tax_line_amount==0){
                        continue;
                    }
                    // getting the taxe account if tax OK and tax account does exist
                    if ( (!empty(get_option('woocommerce_accounting_tax_rates_accounting_account')[$tax_rate_id])) && (!empty(get_option('woocommerce_accounting_tax_rates_accounting_account')[$tax_rate_id]['acc']) ) ) {
                        $tax_line_account = get_option ('woocommerce_accounting_tax_rates_accounting_account')[$tax_rate_id]['acc'];
                        $tax_displays [$tax_rate_id] = array (
                            'account' => $tax_line_account,
                            'amount' => $tax_line_amount,
                            'source' => 'custom',
                        );
                    }
                    // else generic tax account and tax line total
                    else {
                        $tax_line_account = $gen_account_tax;
                        $tax_displays [$tax_rate_id] = array (
                            'account' => $tax_line_account,
                            'amount' => $tax_line_amount,
                            'source' => 'default',
                        );
                    }
                }
                $order->tax_displays = apply_filters('woocommerce_accounting:export:tax_displays', $tax_displays, $order);
            }
            // else generic tax account and tax order total
            else {
                $tax_line_account = $gen_account_tax;
                $tax_line_amount = $order->income_tax;
            }
            
            /*-----------------------------------------------------------------------------*/
            /*------- Ajout des comptes comptables des méthodes d'expédition --------------*/
            /*-----------------------------------------------------------------------------*/

            $order_shipping_methods = $get_order->get_shipping_methods( );

            foreach ($order_shipping_methods as $order_shipping_method){
                if ($ship_opt == "1") {
                    // Shipment account existence test or apply generic
                    if (!empty($order_shipping_acc_list[$order_shipping_method['method_id']]['acc'])) {
                        $order_shipping_accounting_acc = $order_shipping_acc_list[$order_shipping_method['method_id']]['acc'];
                    }
                    else {
                        $order_shipping_accounting_acc = $gen_account_fdp;
                    }

                    //Shipment analytic code existence test or apply generic
                    $order_shipping_ana_list = get_option ('woocommerce_accounting_shipping_methods_analytic_account');
                    if (!empty($order_shipping_ana_list[$order_shipping_method['method_id']]['ana'])) {
                        $order_shipping_analytic_acc = $order_shipping_ana_list[$order_shipping_method['method_id']]['ana'];
                    }
                    else {
                        $order_shipping_analytic_acc = $gen_analytic_fdp;
                    }
                }
                else {
                    $order_shipping_accounting_acc = $gen_account_fdp;
                    $order_shipping_analytic_acc = $gen_analytic_fdp;
                }
            }
            /*-----------------------------------------------------------------------------*/
            /*--------------- Ajout des comptes comptables par produits -------------------*/
            /*-----------------------------------------------------------------------------*/
            $bef_tax = $order->order_discount;
            if ($prod_opt == "1") {
                $order_products = $get_order->get_items();

                $order_products_datas = array ();
                //Per product datas
                foreach ($order_products as $item_id => $order_product) {
                    $order_product_id = $order_product['product_id'];
                    $order_product_acc = get_post_meta($order_product_id,'woocommerce_accounting_product_accounting_account',true);
                    $order_product_ana = get_post_meta($order_product_id,'woocommerce_accounting_product_accounting_analytic',true);
                    // Generic account applied if specific product account not set
                    if (!empty ($order_product_acc)){
                        $order_prod_acc = $order_product_acc;
                    }
                    else {
                        $order_prod_acc = $gen_account_prod;
                    }
                    // Generic analytic code applied if specific product code not set
                    if (!empty ($order_product_ana)) {
                        $order_prod_ana = $order_product_ana;
                    }
                    else {
                        $order_prod_ana = $gen_analytic_prod;
                    }
                    //  Creating the table
                    $order_products_datas[$item_id] = apply_filters('woocommerce_accounting:export:order_products_data', array (
                        'acc'=> $order_prod_acc,
                        'ana'=> $order_prod_ana,
                        'line_sub'=> (float) $order_product['line_subtotal']
                    ), $order_product);
                }

                /*-----------------------------------------------------------------------------*/
                /*------------------ Calcul des bons montants si code promo --------------------*/
                /*-----------------------------------------------------------------------------*/
                //On choisit de faire apparaitre les codes promos comme des produits négatifs
                //Appliqué avant taxe (réduction et Code Promo) on raisonne en HT

                if ($bef_tax > 0) {
                    //Calculating ex.tax amount for coupons
                    // applied for each product
                    $bef_order_products_discounts = array();
                    foreach ($order_products as $key => $bef_order_product) {
                        $bef_order_product_id = $bef_order_product['product_id'];
                        $bef_discount_ht = ($bef_order_product['line_subtotal']) - ($bef_order_product['line_total']);
                        $bef_order_products_discounts[$key] = round($bef_discount_ht, $rounding_precision);
                    }
                }
            }

            // add exceptionnal income or outcome accounts
            $exptcred_acc = get_option('woocommerce_accounting_generic_exptcred_accounting_account');
            $exptchar_acc = get_option('woocommerce_accounting_generic_exptchar_accounting_account');


            //Customer account
            if ((!empty ($order->account_cust)) && ($order->outcome != 0))  {
                $cust_line = array(
                    $col_map['Journal'] =>    $book_code,
                    $col_map['Date'] =>    $order->piecedate,
                    $col_map['Inv.Number'] =>    $order->number,
                    $col_map['Acc.Code'] =>    $order->account_cust,
                    $col_map['Label'] =>    $order->lib,
                    $col_map['Outcome'] =>    woocommerce_accounting_format_number(round($order->outcome, $rounding_precision)),
                    $col_map['Income'] =>    $fillwith,
                    $col_map['Cost Center'] =>    "",
                    $col_map['Empty 1'] =>    "",
                    $col_map['Empty 2'] =>    "",
                    $col_map['Empty 3'] =>    "",
                    $col_map['Empty 4'] =>    ""
                );
                ksort($cust_line);
                $cust_line = apply_filters('woocommerce_accounting:export:order_output_cust_line_'.$ship_opt, $cust_line, $order);
                woocommerce_accounting_add_line( $output, $cust_line, $separator);
            }
            else if ($order->outcome != 0) {
                $cust_line = array(
                    $col_map['Journal'] =>    $book_code,
                    $col_map['Date'] =>    $order->piecedate,
                    $col_map['Inv.Number'] =>    $order->number,
                    $col_map['Acc.Code'] =>    isset($book_account_status[$order->status]) ? $book_account_status[$order->status] : $gen_account_cust,
                    $col_map['Label'] =>    $order->lib,
                    $col_map['Outcome'] =>    woocommerce_accounting_format_number(round($order->outcome, $rounding_precision)),
                    $col_map['Income'] =>    $fillwith,
                    $col_map['Cost Center'] =>    "",
                    $col_map['Empty 1'] =>    "",
                    $col_map['Empty 2'] =>    "",
                    $col_map['Empty 3'] =>    "",
                    $col_map['Empty 4'] =>    ""
                );
                ksort($cust_line);
                $cust_line = apply_filters('woocommerce_accounting:export:order_output_cust_line_'.$ship_opt, $cust_line, $order);
                woocommerce_accounting_add_line( $output, $cust_line, $separator);
            }
            //Shipping methods
            if ((!empty ($order_shipping_method)) && ($order->income_fdpht != 0)) {
                $deliv_line = array(
                    $col_map['Journal'] =>    $book_code,
                    $col_map['Date'] =>    $order->piecedate,
                    $col_map['Inv.Number'] =>    $order->number,
                    $col_map['Acc.Code'] =>    $order_shipping_accounting_acc,
                    $col_map['Label'] =>    $order->lib,
                    $col_map['Outcome'] =>    $fillwith,
                    $col_map['Income'] =>    woocommerce_accounting_format_number(round($order->income_fdpht, $rounding_precision)),
                    $col_map['Cost Center'] =>    $order_shipping_analytic_acc,
                    $col_map['Empty 1'] =>    "",
                    $col_map['Empty 2'] =>    "",
                    $col_map['Empty 3'] =>    "",
                    $col_map['Empty 4'] =>    ""
                );
                ksort($deliv_line);
                $deliv_line = apply_filters('woocommerce_accounting:export:order_output_deliv_line_'.$ship_opt, $deliv_line, $order);
                woocommerce_accounting_add_line( $output, $deliv_line, $separator);
            }
            //Products
            if ($prod_opt == "1") {
                foreach ($order_products_datas as $order_products_data) {
                    if ($order_products_data['line_sub'] != 0) {
                        if (round($order_products_data['line_sub'], $rounding_precision) > 0) {
                            $prod_inc = round($order_products_data['line_sub'], $rounding_precision);
                            $prod_out = "";
                        }
                        elseif (round($order_products_data['line_sub'], $rounding_precision) < 0 ){
                            $prod_inc = "";
                            $prod_out = abs(round($order_products_data['line_sub'], $rounding_precision));
                        }
                        $prod_line = array(
                            $col_map['Journal'] =>    $book_code,
                            $col_map['Date'] =>    $order->piecedate,
                            $col_map['Inv.Number'] =>    $order->number,
                            $col_map['Acc.Code'] =>    $order_products_data['acc'],
                            $col_map['Label'] =>    $order->lib,
                            $col_map['Outcome'] =>    woocommerce_accounting_format_number($prod_out),
                            $col_map['Income'] =>    woocommerce_accounting_format_number($prod_inc),
                            $col_map['Cost Center'] =>    $order_products_data['ana'],
                            $col_map['Empty 1'] =>    "",
                            $col_map['Empty 2'] =>    "",
                            $col_map['Empty 3'] =>    "",
                            $col_map['Empty 4'] =>    ""
                        );
                        ksort($prod_line);
                        $prod_line = apply_filters('woocommerce_accounting:export:order_output_prod_line', $prod_line, $order_products_data, $order,$book_code,$output);
                        if ($prod_line) {
                            woocommerce_accounting_add_line( $output, $prod_line, $separator);
                        }
                    }
                }
                //Detailed coupon
                if ($bef_tax > 0) {
                    $coup_inc = 0;
                    $coup_out = 0;
                    foreach ($bef_order_products_discounts as $key => $bef_order_products_discount) {
                        $coup_inc = $coup_inc + round($bef_order_products_discount, $rounding_precision);
                    }
                    $coupon_value = str_replace('.', $dec_separator, $coup_inc);

                    $coupon_line = array(
                        $col_map['Journal'] =>    $book_code,
                        $col_map['Date'] =>    $order->piecedate,
                        $col_map['Inv.Number'] =>    $order->number,
                        $col_map['Acc.Code'] =>    $gen_account_discount,
                        $col_map['Label'] =>    $order->lib.' '.$gen_sku_discount,
                        $col_map['Outcome'] =>    $coupon_col == 'o' ? $coupon_value : $fillwith,
                        $col_map['Income'] =>    $coupon_col == 'i' ? "-{$coupon_value}" : $fillwith,
                        $col_map['Cost Center'] =>    $gen_analytic_discount,
                        $col_map['Empty 1'] =>    "",
                        $col_map['Empty 2'] =>    "",
                        $col_map['Empty 3'] =>    "",
                        $col_map['Empty 4'] =>    ""
                    );
                    ksort($coupon_line);
                    $coupon_line = apply_filters('woocommerce_accounting:export:order_output_coupon_line', $coupon_line, $order_products_datas, $order, $output);
                    if ($coupon_line) {
                        woocommerce_accounting_add_line( $output, $coupon_line, $separator);
                    }
            
                }
            }
            //Simple products
            elseif ($prod_opt == "0" && $order->income_prodht != 0) {
                if (round($order->income_prodht, $rounding_precision) > 0) {
                    $prod_inc = round($order->income_prodht, $rounding_precision);
                    $prod_out = $fillwith;
                }
                elseif (round($order->income_prodht, $rounding_precision) < 0 ){
                    $prod_inc = $fillwith;
                    $prod_out = abs(round($order->income_prodht, $rounding_precision));
                }
                $prod_line = array(
                    $col_map['Journal'] =>    $book_code,
                    $col_map['Date'] =>    $order->piecedate,
                    $col_map['Inv.Number'] =>    $order->number,
                    $col_map['Acc.Code'] =>    $gen_account_prod,
                    $col_map['Label'] =>    $order->lib,
                    $col_map['Outcome'] =>    woocommerce_accounting_format_number($prod_out),
                    $col_map['Income'] =>    woocommerce_accounting_format_number($prod_inc),
                    $col_map['Cost Center'] =>    $gen_analytic_prod,
                    $col_map['Empty 1'] =>    "",
                    $col_map['Empty 2'] =>    "",
                    $col_map['Empty 3'] =>    "",
                    $col_map['Empty 4'] =>    ""
                );

                ksort($prod_line);
                $prod_line = apply_filters('woocommerce_accounting:export:order_output_simple_prod_line', $prod_line, $order);
                woocommerce_accounting_add_line( $output, $prod_line, $separator);
            }

            //Simple product by country
            elseif ($prod_opt == "2" && $order->income_prodht != 0) {
                $wc_order = wc_get_order($order->ID);
                $order->countryid = $wc_order->get_billing_country();
                $country_account_prod = $gen_account_prod;
                if (!empty($order_country_acc[$order->countryid]['acc'])) {
                    $country_account_prod = $order_country_acc[$order->countryid]['acc'];
                }
                if (round($order->income_prodht, $rounding_precision) > 0) {
                    $prod_inc = round($order->income_prodht, $rounding_precision);
                    $prod_out = $fillwith;
                }
                elseif (round($order->income_prodht, $rounding_precision) < 0 ){
                    $prod_inc = $fillwith;
                    $prod_out = abs(round($order->income_prodht, $rounding_precision));
                }
                $prod_line = array(
                    $col_map['Journal'] =>    $book_code,
                    $col_map['Date'] =>    $order->piecedate,
                    $col_map['Inv.Number'] =>    $order->number,
                    $col_map['Acc.Code'] =>    $country_account_prod,
                    $col_map['Label'] =>    $order->lib,
                    $col_map['Outcome'] =>    woocommerce_accounting_format_number($prod_out),
                    $col_map['Income'] =>    woocommerce_accounting_format_number($prod_inc),
                    $col_map['Cost Center'] =>    $gen_analytic_prod,
                    $col_map['Empty 1'] =>    "",
                    $col_map['Empty 2'] =>    "",
                    $col_map['Empty 3'] =>    "",
                    $col_map['Empty 4'] =>    ""
                );

                ksort($prod_line);
                $prod_line = apply_filters('woocommerce_accounting:export:order_output_country_prod_line', $prod_line, $order);
                woocommerce_accounting_add_line( $output, $prod_line, $separator);
            }

            //Taxes
            if ($tax_applied_check != 0 && $taxes_opt == "1") {
                foreach ($tax_displays as $tax_id => $tax_display) {
                    $tax_inc = 0;
                    $tax_out = 0;
                    if (round($tax_display['amount'], $rounding_precision) > 0) {
                        $tax_inc = round($tax_display['amount'], $rounding_precision);
                        $tax_out = $fillwith;
                    }
                    elseif (round($tax_display['amount'], $rounding_precision) < 0 ){
                        $tax_inc = $fillwith;
                        $tax_out = abs(round($tax_display['amount'], $rounding_precision));
                    }
                    $tax_line = array(
                        $col_map['Journal'] =>    $book_code,
                        $col_map['Date'] =>    $order->piecedate,
                        $col_map['Inv.Number'] =>    $order->number,
                        $col_map['Acc.Code'] =>    $tax_display['account'],
                        $col_map['Label'] =>    $order->lib,
                        $col_map['Outcome'] =>    woocommerce_accounting_format_number($tax_out),
                        $col_map['Income'] =>    woocommerce_accounting_format_number($tax_inc),
                        $col_map['Cost Center'] =>    "",
                        $col_map['Empty 1'] =>    "",
                        $col_map['Empty 2'] =>    "",
                        $col_map['Empty 3'] =>    "",
                        $col_map['Empty 4'] =>    ""
                    );
                    ksort($tax_line);
                    $tax_line = apply_filters('woocommerce_accounting:export:order_output_tax_line', $tax_line, $order,$tax_id);
                    woocommerce_accounting_add_line( $output, $tax_line, $separator);
                }
            }
            elseif ($tax_line_amount != 0){
                $tax_inc = 0;
                $tax_out = 0;
                if (round($tax_line_amount, $rounding_precision) > 0) {
                    $tax_inc = round($tax_line_amount, $rounding_precision);
                    $tax_out = $fillwith;
                }
                elseif (round($tax_line_amount, $rounding_precision) < 0 ){
                    $tax_inc = $fillwith;
                    $tax_out = abs(round($tax_line_amount, $rounding_precision));
                }

                $tax_line = array(
                    $col_map['Journal'] =>    $book_code,
                    $col_map['Date'] =>    $order->piecedate,
                    $col_map['Inv.Number'] =>    $order->number,
                    $col_map['Acc.Code'] =>    $tax_line_account,
                    $col_map['Label'] =>    $order->lib,
                    $col_map['Outcome'] =>    woocommerce_accounting_format_number($tax_out),
                    $col_map['Income'] =>    woocommerce_accounting_format_number($tax_inc),
                    $col_map['Cost Center'] =>    "",
                    $col_map['Empty 1'] =>    "",
                    $col_map['Empty 2'] =>    "",
                    $col_map['Empty 3'] =>    "",
                    $col_map['Empty 4'] =>    ""
                );
                ksort($tax_line);
                $tax_line = apply_filters('woocommerce_accounting:export:order_output_tax_line', $tax_line, $order);
                woocommerce_accounting_add_line( $output, $tax_line, $separator);

            }
            do_action_ref_array('woocommerce_accounting:export:after_order_taxes', array(&$output, $order, $separator));
            
            //Income
            if ($total_income > $total_outcome) {
                $cred_line = array(
                    $col_map['Journal'] =>    $book_code,
                    $col_map['Date'] =>    $order->piecedate,
                    $col_map['Inv.Number'] =>    $order->number,
                    $col_map['Acc.Code'] =>    $exptchar_acc,
                    $col_map['Label'] =>    $order->lib.' ALERT!',
                    $col_map['Outcome'] =>     woocommerce_accounting_format_number($total_income - $total_outcome),
                    $col_map['Income'] =>   $fillwith,
                    $col_map['Cost Center'] =>    "",
                    $col_map['Empty 1'] =>    "",
                    $col_map['Empty 2'] =>    "",
                    $col_map['Empty 3'] =>    "",
                    $col_map['Empty 4'] =>    ""
                );
                ksort($cred_line);
                $cred_line = apply_filters('woocommerce_accounting:export:order_output_cred_line', $cred_line, $order);
                woocommerce_accounting_add_line( $output, $cred_line, $separator);
            }
            //Outcome
            elseif ($total_outcome > $total_income) {
                $deb_line = array(
                    $col_map['Journal'] =>    $book_code,
                    $col_map['Date'] =>    $order->piecedate,
                    $col_map['Inv.Number'] =>    $order->number,
                    $col_map['Acc.Code'] =>    $exptcred_acc,
                    $col_map['Label'] =>    $order->lib.' ALERT!',
                    $col_map['Outcome'] =>   $fillwith,
                    $col_map['Income'] =>    woocommerce_accounting_format_number($total_outcome - $total_income),
                    $col_map['Cost Center'] =>    "",
                    $col_map['Empty 1'] =>    "",
                    $col_map['Empty 2'] =>    "",
                    $col_map['Empty 3'] =>    "",
                    $col_map['Empty 4'] =>    ""
                );
                ksort($deb_line);
                $deb_line = apply_filters('woocommerce_accounting:export:order_output_deb_line', $deb_line, $order);
                woocommerce_accounting_add_line( $output, $deb_line, $separator);
            }
            do_action_ref_array('woocommerce_accounting:export:after_order_output', array(&$output, $order, $separator));
        }

        do_action_ref_array('woocommerce_accounting:export:before_close', array(&$output, $compiledOrders, $separator));
        fclose($output) or die("Can't close php://output");
        exit;
    }
    else {
        wp_safe_redirect(add_query_arg([
            'page'=>'woocommerce_accounting_exporter',
            'format'=>'basic',
            'error'=>'no_orders',
        ], admin_url('admin.php')));
        exit;
    }
}
