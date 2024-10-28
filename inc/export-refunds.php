<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $small_number;
global $rounding_precision;

// Manage 2.1E-5 syntax
$small_number = 0.0001;


function woocommerce_accounting_export_refunds_data() {
    global $woocommerce;
    global $post;
    global $small_number;
    global $rounding_precision;

    //test nonce
    if (! isset( $_POST['_check_refunds_export'] ) || ! wp_verify_nonce( $_POST['_check_refunds_export'], 'check_nonce_refunds_export')){
        print 'Sorry, your nonce did not verify.';
        exit;
    } 

    $separator = sanitize_text_field($_POST['woocommerce_accounting_refunds_export_separator']);
    $dec_separator = sanitize_text_field($_POST['woocommerce_accounting_refunds_export_decimal_separator']);
    $ts1 = sanitize_text_field($_POST['woocommerce_accounting_refunds_export_start_date']);
    $ts2 = sanitize_text_field($_POST['woocommerce_accounting_refunds_export_end_date']);
    $date_opt = sanitize_text_field($_POST['woocommerce_accounting_refunds_export_date_opt']);
    $date_format_opt = sanitize_text_field($_POST['woocommerce_accounting_export_refunds_date_format']);
    $cust_opt = sanitize_text_field($_POST['woocommerce_accounting_refunds_export_cust_opt']);
    $pay_opt = sanitize_text_field($_POST['woocommerce_accounting_refunds_export_pay_opt']);
    $prod_opt = sanitize_text_field($_POST['woocommerce_accounting_refunds_export_prod_opt']);
    $taxes_opt = sanitize_text_field($_POST['woocommerce_accounting_refunds_export_taxes_opt']);
    $ship_opt = sanitize_text_field($_POST['woocommerce_accounting_refunds_export_ship_opt']);
    $factnum_opt = sanitize_text_field($_POST['woocommerce_accounting_refunds_export_factnum_opt']);
    $zero_opt = sanitize_text_field($_POST['woocommerce_accounting_refunds_export_zero_opt']);

    update_option( 'woocommerce_accounting_refunds_export_start_date',$ts1);
    update_option( 'woocommerce_accounting_refunds_export_end_date',$ts2);
    update_option( 'woocommerce_accounting_export_refunds_date_format',$date_format_opt);
    update_option( 'woocommerce_accounting_refunds_export_separator', $separator);
    update_option( 'woocommerce_accounting_refunds_export_date_opt',$date_opt);
    update_option( 'woocommerce_accounting_refunds_export_pay_opt',$pay_opt);
    update_option( 'woocommerce_accounting_refunds_export_prod_opt',$prod_opt);
    update_option( 'woocommerce_accounting_refunds_export_taxes_opt',$taxes_opt);
    update_option( 'woocommerce_accounting_refunds_export_ship_opt',$ship_opt);
    update_option( 'woocommerce_accounting_refunds_export_factnum_opt',$factnum_opt);
    update_option( 'woocommerce_accounting_refunds_export_cust_opt',$cust_opt);
    update_option( 'woocommerce_accounting_refunds_export_decimal_separator',$dec_separator);
    update_option( 'woocommerce_accounting_refunds_export_zero_opt',$zero_opt);

    // Use WC Order Query style
    // https://github.com/woocommerce/woocommerce/wiki/wc_get_orders-and-WC_Order_Query#general

    $date_options = array(
        '0' => 'date_created',
        '1' => 'date_completed',
        '3' => 'date_paid',
    );

    $order_args = array(
        'limit' => -1,
        'type' => 'shop_order_refund',
        'status' => array( 'wc-completed' ),
        'orderby' => 'ID',
        'order' => 'ASC',
    );

    if(isset($date_options[$date_opt])) {
        $order_args[$date_options[$date_opt]] = "{$ts1}...{$ts2}";
    }

    $order_args = apply_filters('woocommerce_accounting:export:get_orders_refunds_args', $order_args, $date_opt, array( 'wc-completed' ), array($ts1, $ts2));
    $orders = woocommerce_accounting_get_orders($order_args);
    do_action('woocommerce_accounting:export:launch', $orders);
    
    $compiledOrders = array();

    //Datas to export
    if (!empty($orders)) {
        //Generic datas
        $book_code = get_option('woocommerce_accounting_book_code_order');
        $gen_account_cust = get_option('woocommerce_accounting_generic_cust_accounting_account');
        $gen_account_prod = get_option ('woocommerce_accounting_generic_prod_accounting_account');
        $gen_account_fdp = get_option ('woocommerce_accounting_generic_fdp_accounting_account');
        $gen_account_tax = get_option ('woocommerce_accounting_generic_tax_accounting_account');
        $gen_analytic_prod = get_option ('woocommerce_accounting_generic_prod_analytic_account');
        $gen_analytic_fdp = get_option('woocommerce_accounting_generic_fdp_analytic_account');
        $lib_prefix = get_option ('woocommerce_accounting_lib_prefix');

        $rounding_precision = 2;


        foreach ($orders as $wcOrder)
        {
            // skip order if it is a regular order
            if($wcOrder instanceof \WC_Order){
                continue;
            }
            $order = (object) array();
            $order->WCOrder = $wcOrder;
            $order->ID = $wcOrder->get_id();
            $order->status = 'wc-'.$wcOrder->get_status();
            $order->date_created = $wcOrder->get_date_created();
            $order->date_completed = $wcOrder->get_date_completed();
            $order->date_paid = $wcOrder->get_date_paid();

            $order->total = $wcOrder->get_total();
            $order->total_tax = (float) $wcOrder->get_total_tax();
            $order->shipping_tax = (float) $wcOrder->get_shipping_tax();
            
            //searching for parent order
            $order->ancestor_order = $wcOrder->get_parent_id();
            $order->AnsOrder = new \WC_Order($order->ancestor_order);
            
            $order->custid = $order->AnsOrder->get_customer_id();
            $order->gatewayid = $order->AnsOrder->get_payment_method();

            if ($date_opt == "0") {
                $order->piecedate =  get_post_field( 'post_date', $order->ID );
            } else {
                $invoice_date = get_post_meta($order->ID,'_wcpdf_credit_note_date',true);
                if (!empty($invoice_date)) {
                    $order->piecedate = $invoice_date;
                } else {
                    $order->piecedate = get_post_field( 'post_date', $order->ID );
                }
            }
            //Date Format
            $order_year = date_i18n( 'Y', strtotime( $order->piecedate ) );
            $order_month = date_i18n( 'm', strtotime( $order->piecedate ) );
            $order_day = date_i18n ( 'd', strtotime( $order->piecedate ) ) ;
            if ($date_format_opt == "1") {
                $order->piecedate = $order_day . '-' . $order_month . '-' . $order_year;
            } elseif ($date_format_opt == "2") {
                $order->piecedate = $order_month . '-' . $order_day . '-' . $order_year;
            } elseif ($date_format_opt == "3") {
                $order->piecedate = $order_year . '-' . $order_month . '-' . $order_day;
            } elseif ($date_format_opt == "4") {
                $order->piecedate = $order_day . '/' . $order_month . '/' . $order_year;
            } elseif ($date_format_opt == "5") {
                $order->piecedate = $order_month . '/' . $order_day . '/' . $order_year;
            } elseif ($date_format_opt == "6") {
                $order->piecedate = $order_year . '/' . $order_month . '/' . $order_day;
            } else {
                $order->piecedate = $order->piecedate;
            }

            // Test if plugin is activated
            if ( in_array( 'woocommerce-pdf-ips-pro/woocommerce-pdf-ips-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                $pro_settings = get_option( 'wpo_wcpdf_pro_settings' );
                $number = get_post_meta($order->ID,'_wcpdf_credit_note_number',true);
                $invoice_date = get_post_meta($order->ID,'_wcpdf_credit_note_date',true);
                
                //Is invoice number defined ?
                if ( ( !empty ($number) ) && $factnum_opt == 0 ){
                    //Use credit note number if is defined
                    $order_year = date_i18n( 'Y', strtotime( $order->piecedate ) );
                    $order_month = date_i18n( 'm', strtotime( $order->piecedate ) );
                    $order_day = date_i18n( 'd', strtotime( $order->piecedate ) );
                    $invoice_year = date_i18n( 'Y', strtotime( $invoice_date ) );
                    $invoice_month = date_i18n( 'm', strtotime( $invoice_date ) );
                    $invoice_day = date_i18n( 'd', strtotime( $invoice_date ) );
                    //Creating credit note numbers
                    $formats['prefix'] = $pro_settings['credit_note_number_formatting_prefix'];
                    $formats['suffix'] = $pro_settings['credit_note_number_formatting_suffix'];
                    $formats['padding'] = $pro_settings['credit_note_number_formatting_padding'];

                    // Replacements
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
                    if ( ctype_digit( (string)$formats['padding'] ) ) {
                        $number = sprintf('%0'.$formats['padding'].'d', $number);
                    }
                    $formatted_refund_number = $formats['prefix'] . $number . $formats['suffix'] ;
                    $order->number = $formatted_refund_number;
                } 
                else {
                    $order->number = $order->ancestor_order;
                }
                //Create the number if not created
            } 
            else {
                $order->number = $order->ancestor_order;
            }
            //Customer account type (customer, country or gateway)
            if ($cust_opt =="0") {
                $order->account_cust = get_user_meta($order->custid,'woocommerce_accounting_user_accountingcode',true);
            }
            else if ($cust_opt =="1"){
                $order_coutry_acc = get_option('woocommerce_accounting_gateways_libs');
                $order->account_cust = $order_coutry_acc[$order->gatewayid]['acc'];
            }
            // Get the country only in label if prod by country
            if ($prod_opt == "2") {
                $country_label = $order->AnsOrder->get_shipping_country();
            }
            else {
                $country_label = "";
            }
            //activating payment gateway in lib
            if ($pay_opt == "1") {
                $pay_meth = substr($order->AnsOrder->get_payment_method(), 0, 1) . substr($order->AnsOrder->get_payment_method(), 3, 1) . substr($order->AnsOrder->get_payment_method(), -1) ;
                $order->lib = remove_accents (strtoupper($pay_meth) . ' ' .  $lib_prefix . ' ' . strtoupper($order->AnsOrder->get_billing_company()) . ' ' . ucfirst($order->AnsOrder->get_billing_last_name()) . ' ' . ucfirst($order->AnsOrder->get_billing_first_name()) . ' ' . $country_label);
                $order->lib = trim($order->lib);
            }
            elseif ($pay_opt == "2") {
                $order_pay_meth = $order->AnsOrder->get_payment_method();
                $listed_pay_lib = get_option('woocommerce_accounting_gateways_libs')[$order_pay_meth]['lib'];
                if (!empty ($listed_pay_lib)) {
                    $pay_meth = $listed_pay_lib;
                } else {
                    $pay_meth = substr($order->AnsOrder->get_payment_method(),0,1) . substr($order->AnsOrder->get_payment_method(), 3, 1) . substr($order->AnsOrder->get_payment_method(), -1) ;
                };
                $order->lib = remove_accents (strtoupper($pay_meth) . ' ' .  $lib_prefix . ' ' . strtoupper($order->AnsOrder->get_billing_company()) . ' ' . ucfirst($order->AnsOrder->get_billing_last_name()) . ' ' . ucfirst($order->AnsOrder->get_billing_first_name()) . ' ' . $country_label);
                $order->lib = trim($order->lib);
            }
            else {
                    $order->lib = remove_accents ($lib_prefix . ' ' . strtoupper($order->AnsOrder->get_billing_company()) . ' ' . ucfirst($order->AnsOrder->get_billing_last_name()) . ' ' . ucfirst($order->AnsOrder->get_billing_first_name()) . ' ' . $country_label);
                    $order->lib = trim($order->lib);
            }
            if ( $factnum_opt == 1 ){
                //Creating invoice numbers as PDF Invoice plugin if set
                if (!empty($number)){
                    $order_year = date_i18n( 'Y', strtotime( $order->piecedate ) );
                    $order_month = date_i18n( 'm', strtotime( $order->piecedate ) );
                    $order_day = date_i18n( 'd', strtotime( $order->piecedate ) );
                    $invoice_year = date_i18n( 'Y', strtotime( $invoice_date ) );
                    $invoice_month = date_i18n( 'm', strtotime( $invoice_date ) );
                    $invoice_day = date_i18n( 'd', strtotime( $invoice_date ) );
                    
                    //Creating credit note numbers
                    $formats['prefix'] = $pro_settings['credit_note_number_formatting_prefix'];
                    $formats['suffix'] = $pro_settings['credit_note_number_formatting_suffix'];
                    $formats['padding'] = $pro_settings['credit_note_number_formatting_padding'];
                    // Replacements
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
                    if ( ctype_digit( (string)$formats['padding'] ) ) {
                        $number = sprintf('%0'.$formats['padding'].'d', $number);
                    }
                    $formatted_refund_number = $formats['prefix'] . $number . $formats['suffix'] ;

                    $order->lib = $formatted_refund_number . ' ' . $order->lib;
                }
            }
            else {
                if (!empty($formatted_refund_number)){
                    $order->lib = $order->ancestor_order . ' ' . $order->lib;
                }
            }
            $order->outcome = round($order->total, $rounding_precision);
            $order->income_tax = round($order->total_tax > $small_number ? $order->total_tax : 0, $rounding_precision) + round($order->shipping_tax > $small_number ? $order->shipping_tax : 0, $rounding_precision);
            $order->income_fdpht = round($wcOrder->get_total_shipping(), $rounding_precision);
            $order->income_prodht = (($order->outcome) - (($order->income_tax) + ($order->income_fdpht)));
            $compiledOrders[] = $order;
        }

        // Let's prepare export
        $output = fopen("php://output",'w') or die ("Can't open php://output");
        $filename = 'woocommerce-accounting-wbk-refund-export-' . $ts1 . '-' . $ts2 . '.csv';
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
        // CSV Headers
        if (!empty($csv_headers['journal'])) {
            $csv_journal = $csv_headers['journal'];
        } else {
            $csv_journal = 'Code_Journal';
        }
        if (!empty($csv_headers['date'])) {
            $csv_date = $csv_headers['date'];
        } else {
            $csv_date = 'Date_de_piece';
        }
        if (!empty($csv_headers['number'])) {
            $csv_number = $csv_headers['number'];
        } else {
            $csv_number = 'Numero_de_piece';
        }
        if (!empty($csv_headers['code'])) {
            $csv_code = $csv_headers['code'];
        } else {
            $csv_code = 'Compte_Comptable';
        }
        if (!empty($csv_headers['label'])) {
            $csv_label = $csv_headers['label'];
        } else {
            $csv_label = 'Libelle';
        }
        if (!empty($csv_headers['outcome'])) {
            $csv_outcome = $csv_headers['outcome'];
        } else {
            $csv_outcome = 'Debit';
        }
        if (!empty($csv_headers['income'])) {
            $csv_income = $csv_headers['income'];
        } else {
            $csv_income = 'Credit';
        }
        if (!empty($csv_headers['center'])) {
            $csv_center = $csv_headers['center'];
        } else {
            $csv_center = 'Code_Analytique';
        }

        //Columns order preparation
        $col_map = get_option('woocommerce_accounting_colorder');
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
        
        if ($separator == "t") {
            woocommerce_accounting_add_line($output, $header_line, "\t", true);
        }
        else {
            woocommerce_accounting_add_line($output, $header_line, $separator, true);
        }

        //Zero Option
        if ($zero_opt == "1") {
            $fillwith = 0.00;
        } else {
            $fillwith = "";
        }

        foreach($compiledOrders as $order) {
            $get_order = $order->WCOrder;

            /*-----------------------------------------------------------------------------*/
            /*---------------              Taxes for each order         -------------------*/
            /*-----------------------------------------------------------------------------*/

            $tax_applied_check = get_post_meta($order->ID,'_order_tax',true) + get_post_meta($order->ID,'_order_shipping_tax',true);
            // Is a tax set on this order ?
            if (($tax_applied_check != 0) && ($taxes_opt == "1") && ($order->order_discount == 0)){
                // Searching for taxes applied
                $tax_displays = array ();

                foreach ($get_order->get_taxes() as $get_order_tax) {
                    $tax_rate_id = $get_order_tax['rate_id'];
                    // Getting specific account and amount if tax is set
                    if ( (!empty(get_option('woocommerce_accounting_tax_rates_accounting_account')[$tax_rate_id])) && (!empty(get_option('woocommerce_accounting_tax_rates_accounting_account')[$tax_rate_id]['acc']) ) ) {
                        $tax_line_account = get_option ('woocommerce_accounting_tax_rates_accounting_account')[$tax_rate_id]['acc'];
                        $tax_line_amount = $get_order_tax['tax_amount'] + $get_order_tax['shipping_tax_amount'];

                        $tax_displays [] = array (
                        'account' => $tax_line_account,
                        'amount' => $tax_line_amount
                        );
                    }
                    // Generic account and line total if not
                    else {
                        $tax_line_account = $gen_account_tax;
                        $tax_line_amount = $get_order_tax['tax_amount'] + $get_order_tax['shipping_tax_amount'];

                        $tax_displays [] = array (
                        'account' => $tax_line_account,
                        'amount' => $tax_line_amount
                        );
                    }
                }
            }
            // If no specific tax : generic account and order tax total
            else {
                $tax_line_account = $gen_account_tax;
                $tax_line_amount = $order->income_tax;
            }
            /*-----------------------------------------------------------------------------*/
            /*--------------    Add shipping method accounting account  -------------------*/
            /*-----------------------------------------------------------------------------*/
            $order_shipping_methods = $get_order->get_shipping_methods( );
            foreach ($order_shipping_methods as $order_shipping_method){
                $order_shipping_acc_list = get_option ('woocommerce_accounting_shipping_methods_accounting_account');
                if ($ship_opt == "1") {
                    // Test if specific shipping method account is set
                    if (!empty($order_shipping_acc_list[$order_shipping_method['method_id']]['acc'])) {
                        $order_shipping_accounting_acc = $order_shipping_acc_list[$order_shipping_method['method_id']]['acc'];
                    } else {
                        $order_shipping_accounting_acc = $gen_account_fdp;
                    }
                    // Preparing specific analytic code
                    // Test if specific shipping method code is set
                    $order_shipping_ana_list = get_option ('woocommerce_accounting_shipping_methods_analytic_account');
                    if (!empty($order_shipping_ana_list[$order_shipping_method['method_id']]['ana'])) {
                        $order_shipping_analytic_acc = $order_shipping_ana_list[$order_shipping_method['method_id']]['ana'];
                    } else {
                        $order_shipping_analytic_acc = $gen_analytic_fdp;
                    }
                } else {
                    $order_shipping_accounting_acc = $gen_account_fdp;
                    $order_shipping_analytic_acc = $gen_analytic_fdp;
                }
            }
            /*-----------------------------------------------------------------------------*/
            /*--------------  Add products accounting codes and accounts  -----------------*/
            /*-----------------------------------------------------------------------------*/
            $bef_tax = get_post_meta($order->ID,'_cart_discount',true);
            if ($prod_opt == "1" && $order->order_discount == 0) {
                $order_products = $get_order->get_items();
                // Preparing table for datas
                $order_products_datas = array ();
                //Getting datas
                foreach ($order_products as $item_id => $order_product) {
                    $order_product_id = $order_product['product_id'];
                    $order_product_acc = get_post_meta($order_product_id,'woocommerce_accounting_product_accounting_account',true);
                    $order_product_ana = get_post_meta($order_product_id,'woocommerce_accounting_product_accounting_analytic',true);
                    // Generic account if specific product account is not set
                    if (!empty ($order_product_acc)){
                        $order_prod_acc = $order_product_acc;
                    } else {
                        $order_prod_acc = $gen_account_prod;
                    }
                    // Generic analytic code if specific product account is not set
                    if (!empty ($order_product_ana)) {
                        $order_prod_ana = $order_product_ana;
                    }
                    else {
                        $order_prod_ana = $gen_analytic_prod;
                    }
                    //  Sort table
                    $order_products_datas[$item_id] = array (
                        'acc'=> $order_prod_acc,
                        'ana'=> $order_prod_ana,
                        'line_sub'=> $order_product['line_subtotal']
                        );
                }
                /*-----------------------------------------------------------------------------*/
                /*--------------          Coupon amounts per product          -----------------*/
                /*-----------------------------------------------------------------------------*/
                
                //Coupons are considered as negative products
                //Thinking ex. tax because it's illegal another way
                if ($bef_tax > 0) {
                    //Amount of the coupon on each line (ex. tax)
                    $bef_order_products_discounts = array();
                    foreach ($order_products as $key=>$bef_order_product) {
                        $bef_order_product_id = $bef_order_product['product_id'];
                        $bef_discount_ht = ($bef_order_product['line_subtotal']) - ($bef_order_product['line_total']);
                        $bef_order_products_discounts[$key] = round($bef_discount_ht,2);
                    }
                }
            }
            // Exceptionnal accounts
            $exptcred_acc = get_option('woocommerce_accounting_generic_exptcred_accounting_account');
            $exptchar_acc = get_option('woocommerce_accounting_generic_exptchar_accounting_account');
            /*Different cases*/
            //Order amount
            $chk_tot = round($order->outcome, $rounding_precision);
            //Products
            if ($prod_opt == "0" OR $prod_opt == "2" OR $order->order_discount > 0) {
                $chk_prod = round($order->income_prodht,2);
            }
            else {
                $col_prod = array();
                foreach ($order_products as $order_product) {
                    $col_prod[] = round($order_product['line_subtotal'],2) ;
                }
                $chk_prod = round(array_sum($col_prod),2);
            }
            //Taxes
            if ($taxes_opt == "0" OR $order->order_discount > 0) {
                $chk_tax = round($order->income_tax,2);
            }
            else {
                $col_tax = array();
                foreach ($get_order->get_taxes() as $get_order_tax) {
                    $tax_line_am = $get_order_tax['tax_amount'] + $get_order_tax['shipping_tax_amount'];
                    $col_tax[] = round($tax_line_am,2) ;
                }
                $chk_tax = round(array_sum($col_tax),2);
            }
            //Shipping
            $chk_fdp = round($order->income_fdpht,2);
            //Coupon
            if ($bef_tax > 0 && $prod_opt == "1" && $order->order_discount == 0) {
                $chk_disc = round(array_sum($bef_order_products_discounts),2);
            }
            else {
                $chk_disc = 0;
            }
            // Difference
            $chk_op = ($chk_tot + $chk_disc) - ($chk_prod + $chk_tax + $chk_fdp);
            $chk_op = round($chk_op,2);
            $chk = abs($chk_op);
            
            //Let's export ! (for tab)
            if ($separator == "t") {
                //Alert system for more than rounding error difference
                if ($chk > 0.03) {

                    woocommerce_accounting_add_line($output, array(
                        "ALERT !!! NEXT REFUND SEEMS TO BE A GENERIC REFUND OR TO BE AN ERROR. IT WILL BE ADDED IN AN EXCEPTIONNAL ACCOUNT. YOU SHOULD CHECK IT TO PRECISE WHAT HAS BEEN REFUNDED."
                    ),"\t");
                }
                //Customers
                if ((!empty ($order->account_cust)) && ($order->outcome != 0))  {
                    $cust_line = array(
                        $col_map['Journal'] =>    $book_code,
                        $col_map['Date'] =>    $order->piecedate,
                        $col_map['Inv.Number'] =>    $order->number,
                        $col_map['Acc.Code'] =>    $order->account_cust,
                        $col_map['Label'] =>    $order->lib,
                        $col_map['Outcome'] =>    $fillwith,
                        $col_map['Income'] =>    str_replace('.',$dec_separator, abs(round($order->outcome,2))),
                        $col_map['Cost Center'] =>    "",
                        $col_map['Empty 1'] =>    "",
                        $col_map['Empty 2'] =>    "",
                        $col_map['Empty 3'] =>    "",
                        $col_map['Empty 4'] =>    ""
                    );
                    ksort($cust_line);

                    woocommerce_accounting_add_line($output,$cust_line,"\t");
                }
                else if ($order->outcome != 0) {
                    $cust_line = array(
                        $col_map['Journal'] =>    $book_code,
                        $col_map['Date'] =>    $order->piecedate,
                        $col_map['Inv.Number'] =>    $order->number,
                        $col_map['Acc.Code'] =>    $gen_account_cust,
                        $col_map['Label'] =>    $order->lib,
                        $col_map['Outcome'] =>    $fillwith,
                        $col_map['Income'] =>    str_replace('.',$dec_separator, abs(round($order->outcome,2))),
                        $col_map['Cost Center'] =>    "",
                        $col_map['Empty 1'] =>    "",
                        $col_map['Empty 2'] =>    "",
                        $col_map['Empty 3'] =>    "",
                        $col_map['Empty 4'] =>    ""
                            );
                    ksort($cust_line);
                    woocommerce_accounting_add_line($output,$cust_line,"\t");
                }
                //Shipping method
                if ((!empty ($order_shipping_method)) && ($order->income_fdpht != 0)) {
                    $deliv_line = array(
                        $col_map['Journal'] =>    $book_code,
                        $col_map['Date'] =>    $order->piecedate,
                        $col_map['Inv.Number'] =>    $order->number,
                        $col_map['Acc.Code'] =>    $order_shipping_accounting_acc,
                        $col_map['Label'] =>    $order->lib,
                        $col_map['Outcome'] =>    str_replace('.',$dec_separator, abs(round($order->income_fdpht,2))),
                        $col_map['Income'] =>    $fillwith,
                        $col_map['Cost Center'] =>    $order_shipping_analytic_acc,
                        $col_map['Empty 1'] =>    "",
                        $col_map['Empty 2'] =>    "",
                        $col_map['Empty 3'] =>    "",
                        $col_map['Empty 4'] =>    ""
                    );
                    ksort($deliv_line);
                    woocommerce_accounting_add_line($output, $deliv_line, "\t");
                }

                //Products
                if ($prod_opt == "1" && $order->order_discount == 0) {
                    foreach ($order_products_datas as $order_products_data) {
                        if ($order_products_data['line_sub'] != 0) {
                            $prod_line = array(
                                $col_map['Journal'] =>    $book_code,
                                $col_map['Date'] =>    $order->piecedate,
                                $col_map['Inv.Number'] =>    $order->number,
                                $col_map['Acc.Code'] =>    $order_products_data['acc'],
                                $col_map['Label'] =>    $order->lib,
                                $col_map['Outcome'] =>    str_replace('.',$dec_separator, abs(round($order_products_data['line_sub'],2))),
                                $col_map['Income'] =>    $fillwith,
                                $col_map['Cost Center'] =>    $order_products_data['ana'],
                                $col_map['Empty 1'] =>    "",
                                $col_map['Empty 2'] =>    "",
                                $col_map['Empty 3'] =>    "",
                                $col_map['Empty 4'] =>    ""
                            );
                            ksort($prod_line);
                            //Coupon
                            woocommerce_accounting_add_line($output, $prod_line,"\t");
                        }
                    }
                    if ($bef_tax > 0) {
                        foreach ($bef_order_products_discounts as $key => $bef_order_products_discount) {
                            $coupon_line = array(
                                $col_map['Journal'] =>    $book_code,
                                $col_map['Date'] =>    $order->piecedate,
                                $col_map['Inv.Number'] =>    $order->number,
                                $col_map['Acc.Code'] =>    $order_products_datas[$key]['acc'],
                                $col_map['Label'] =>    $order->lib,
                                $col_map['Outcome'] =>    str_replace('.',$dec_separator, abs(round($bef_order_products_discount,2))),
                                $col_map['Income'] =>    $fillwith,
                                $col_map['Cost Center'] =>    $order_products_datas[$key]['ana'],
                                $col_map['Empty 1'] =>    "",
                                $col_map['Empty 2'] =>    "",
                                $col_map['Empty 3'] =>    "",
                                $col_map['Empty 4'] =>    ""
                            );
                            ksort($coupon_line);
                            woocommerce_accounting_add_line($output, $coupon_line,"\t");
                        }
                    }    
                //Simple Products
                }
                elseif ($prod_opt == "0" && $order->income_prodht != 0) {
                    $prod_line = array(
                        $col_map['Journal'] =>    $book_code,
                        $col_map['Date'] =>    $order->piecedate,
                        $col_map['Inv.Number'] =>    $order->number,
                        $col_map['Acc.Code'] =>    $gen_account_prod,
                        $col_map['Label'] =>    $order->lib,
                        $col_map['Outcome'] =>    str_replace('.',$dec_separator, abs(round($order->income_prodht,2))),
                        $col_map['Income'] =>    $fillwith,
                        $col_map['Cost Center'] =>    $gen_analytic_prod,
                        $col_map['Empty 1'] =>    "",
                        $col_map['Empty 2'] =>    "",
                        $col_map['Empty 3'] =>    "",
                        $col_map['Empty 4'] =>    ""
                        );
                        ksort($prod_line);
                        woocommerce_accounting_add_line($output, $prod_line,"\t");
                }
                //Simple products by country
                elseif ($prod_opt == "2" && $order->income_prodht != 0) {
                    $order->countryid = $order->AnsOrder->get_shipping_country();
                    $order_coutry_acc = get_option('woocommerce_accounting_countries_acc');
                    $country_account_prod = $order_coutry_acc[$order->countryid]['acc'];
                    if (empty($country_account_prod)) {
                        $country_account_prod = $gen_account_prod;
                    }
                    $prod_line = array(
                        $col_map['Journal'] =>    $book_code,
                        $col_map['Date'] =>    $order->piecedate,
                        $col_map['Inv.Number'] =>    $order->number,
                        $col_map['Acc.Code'] =>    $country_account_prod,
                        $col_map['Label'] =>    $order->lib,
                        $col_map['Outcome'] =>    str_replace('.',$dec_separator, abs(round($order->income_prodht,2))),
                        $col_map['Income'] =>    $fillwith,
                        $col_map['Cost Center'] =>    $gen_analytic_prod,
                        $col_map['Empty 1'] =>    "",
                        $col_map['Empty 2'] =>    "",
                        $col_map['Empty 3'] =>    "",
                        $col_map['Empty 4'] =>    ""
                    );
                    ksort($prod_line);
                    woocommerce_accounting_add_line($output, $prod_line,"\t");
                }
                
                //Taxes
                if (($tax_applied_check != 0) && ($taxes_opt == "1") && ($order->order_discount == 0)) {
                    foreach ($tax_displays as $tax_display) {
                        $tax_line = array(
                            $col_map['Journal'] =>    $book_code,
                            $col_map['Date'] =>    $order->piecedate,
                            $col_map['Inv.Number'] =>    $order->number,
                            $col_map['Acc.Code'] =>    $tax_display['account'],
                            $col_map['Label'] =>    $order->lib,
                            $col_map['Outcome'] =>    str_replace('.',$dec_separator, abs(round($tax_display['amount'],2))),
                            $col_map['Income'] =>    $fillwith,
                            $col_map['Cost Center'] =>    "",
                            $col_map['Empty 1'] =>    "",
                            $col_map['Empty 2'] =>    "",
                            $col_map['Empty 3'] =>    "",
                            $col_map['Empty 4'] =>    ""
                        );
                        ksort($tax_line);
                        woocommerce_accounting_add_line($output, $tax_line,"\t");
                    }
                }
                else if ($tax_line_amount != 0){
                    $tax_line = array(
                        $col_map['Journal'] =>    $book_code,
                        $col_map['Date'] =>    $order->piecedate,
                        $col_map['Inv.Number'] =>    $order->number,
                        $col_map['Acc.Code'] =>    $tax_line_account,
                        $col_map['Label'] =>    $order->lib,
                        $col_map['Outcome'] =>    str_replace('.',$dec_separator, abs(round($tax_line_amount,2))),
                        $col_map['Income'] =>    $fillwith,
                        $col_map['Cost Center'] =>    "",
                        $col_map['Empty 1'] =>    "",
                        $col_map['Empty 2'] =>    "",
                        $col_map['Empty 3'] =>    "",
                        $col_map['Empty 4'] =>    ""
                    );
                    ksort($tax_line);
                    woocommerce_accounting_add_line($output, $tax_line,"\t");
                }
                // Exceptionnal Income
                if ($chk > 0 && $chk_op > 0) {
                    $cred_line = array(
                        $col_map['Journal'] =>    $book_code,
                        $col_map['Date'] =>    $order->piecedate,
                        $col_map['Inv.Number'] =>    $order->number,
                        $col_map['Acc.Code'] =>    $exptcred_acc,
                        $col_map['Label'] =>    $order->lib,
                        $col_map['Outcome'] =>    $fillwith,
                        $col_map['Income'] =>    str_replace('.',$dec_separator, round($chk,2)),
                        $col_map['Cost Center'] =>    "",
                        $col_map['Empty 1'] =>    "",
                        $col_map['Empty 2'] =>    "",
                        $col_map['Empty 3'] =>    "",
                        $col_map['Empty 4'] =>    ""
                    );
                    ksort($cred_line);
                    woocommerce_accounting_add_line($output, $cred_line,"\t");
                }
                //Exceptionnal Outcome
                elseif ($chk > 0 && $chk_op < 0) {
                    $deb_line = array(
                        $col_map['Journal'] =>    $book_code,
                        $col_map['Date'] =>    $order->piecedate,
                        $col_map['Inv.Number'] =>    $order->number,
                        $col_map['Acc.Code'] =>    $exptchar_acc,
                        $col_map['Label'] =>    $order->lib,
                        $col_map['Outcome'] =>    str_replace('.',$dec_separator, round($chk,2)),
                        $col_map['Income'] =>    $fillwith,
                        $col_map['Cost Center'] =>    "",
                        $col_map['Empty 1'] =>    "",
                        $col_map['Empty 2'] =>    "",
                        $col_map['Empty 3'] =>    "",
                        $col_map['Empty 4'] =>    ""
                    );
                    ksort($deb_line);
                    woocommerce_accounting_add_line($output,$deb_line,"\t");
                }
            // Other separator (not tab)
            }
            else {
                //Alert system for more than rounding error difference
                if ($chk > 0.03) {
                    woocommerce_accounting_add_line($output, array(
                        "ALERT !!! NEXT REFUND SEEMS TO BE A GENERIC REFUND OR TO BE AN ERROR. IT WILL BE ADDED IN AN EXCEPTIONNAL ACCOUNT. YOU SHOULD CHECK IT TO PRECISE WHAT HAS BEEN REFUNDED."
                    ),
                    $separator);
                }
                //Customers
                if ((!empty ($order->account_cust)) && ($order->outcome != 0))  {
                    $cust_line = array(
                        $col_map['Journal'] =>    $book_code,
                        $col_map['Date'] =>    $order->piecedate,
                        $col_map['Inv.Number'] =>    $order->number,
                        $col_map['Acc.Code'] =>    $order->account_cust,
                        $col_map['Label'] =>    $order->lib,
                        $col_map['Outcome'] =>    $fillwith,
                        $col_map['Income'] =>    str_replace('.',$dec_separator, abs(round($order->outcome,2))),
                        $col_map['Cost Center'] =>    "",
                        $col_map['Empty 1'] =>    "",
                        $col_map['Empty 2'] =>    "",
                        $col_map['Empty 3'] =>    "",
                        $col_map['Empty 4'] =>    ""
                    );
                    ksort($cust_line);
                    woocommerce_accounting_add_line($output,$cust_line, $separator);
                } else if ($order->outcome != 0) {
                    $cust_line = array(
                        $col_map['Journal'] =>    $book_code,
                        $col_map['Date'] =>    $order->piecedate,
                        $col_map['Inv.Number'] =>    $order->number,
                        $col_map['Acc.Code'] =>    $gen_account_cust,
                        $col_map['Label'] =>    $order->lib,
                        $col_map['Outcome'] =>    $fillwith,
                        $col_map['Income'] =>    str_replace('.',$dec_separator, abs(round($order->outcome,2))),
                        $col_map['Cost Center'] =>    "",
                        $col_map['Empty 1'] =>    "",
                        $col_map['Empty 2'] =>    "",
                        $col_map['Empty 3'] =>    "",
                        $col_map['Empty 4'] =>    ""
                    );
                    ksort($cust_line);
                    woocommerce_accounting_add_line($output,$cust_line, $separator);
                }
                //Shipping
                if ((!empty ($order_shipping_method)) && ($order->income_fdpht != 0)) {
                    $deliv_line = array(
                        $col_map['Journal'] =>    $book_code,
                        $col_map['Date'] =>    $order->piecedate,
                        $col_map['Inv.Number'] =>    $order->number,
                        $col_map['Acc.Code'] =>    $order_shipping_accounting_acc,
                        $col_map['Label'] =>    $order->lib,
                        $col_map['Outcome'] =>    str_replace('.',$dec_separator, abs(round($order->income_fdpht,2))),
                        $col_map['Income'] =>    $fillwith,
                        $col_map['Cost Center'] =>    $order_shipping_analytic_acc,
                        $col_map['Empty 1'] =>    "",
                        $col_map['Empty 2'] =>    "",
                        $col_map['Empty 3'] =>    "",
                        $col_map['Empty 4'] =>    ""
                    );
                    ksort($deliv_line);
                    woocommerce_accounting_add_line($output, $deliv_line, $separator);
                }
                //Products
                if ($prod_opt == "1" && $order->order_discount == 0) {
                    foreach ($order_products_datas as $order_products_data) {
                        if ($order_products_data['line_sub'] != 0) {
                            $prod_line = array(
                                $col_map['Journal'] =>    $book_code,
                                $col_map['Date'] =>    $order->piecedate,
                                $col_map['Inv.Number'] =>    $order->number,
                                $col_map['Acc.Code'] =>    $order_products_data['acc'],
                                $col_map['Label'] =>    $order->lib,
                                $col_map['Outcome'] =>    str_replace('.',$dec_separator, abs(round($order_products_data['line_sub'],2))),
                                $col_map['Income'] =>    $fillwith,
                                $col_map['Cost Center'] =>    $order_products_data['ana'],
                                $col_map['Empty 1'] =>    "",
                                $col_map['Empty 2'] =>    "",
                                $col_map['Empty 3'] =>    "",
                                $col_map['Empty 4'] =>    ""
                            );
                            ksort($prod_line);
                            woocommerce_accounting_add_line($output, $prod_line, $separator);
                        }
                    }
                    // Coupons
                    if ($bef_tax > 0) {
                        foreach ($bef_order_products_discounts as $key => $bef_order_products_discount) {
                            $coupon_line = array(
                                $col_map['Journal'] =>    $book_code,
                                $col_map['Date'] =>    $order->piecedate,
                                $col_map['Inv.Number'] =>    $order->number,
                                $col_map['Acc.Code'] =>    $order_products_datas[$key]['acc'],
                                $col_map['Label'] =>    $order->lib,
                                $col_map['Outcome'] =>    str_replace('.',$dec_separator, abs(round($bef_order_products_discount,2))),
                                $col_map['Income'] =>    $fillwith,
                                $col_map['Cost Center'] =>    $order_products_datas[$key]['ana'],
                                $col_map['Empty 1'] =>    "",
                                $col_map['Empty 2'] =>    "",
                                $col_map['Empty 3'] =>    "",
                                $col_map['Empty 4'] =>    ""
                            );
                            ksort($coupon_line);
                            woocommerce_accounting_add_line($output, $coupon_line, $separator);
                        }
                    }
                    //Simple products
                } elseif ($prod_opt == "0" && $order->income_prodht != 0) {
                    $prod_line = array(
                        $col_map['Journal'] =>    $book_code,
                        $col_map['Date'] =>    $order->piecedate,
                        $col_map['Inv.Number'] =>    $order->number,
                        $col_map['Acc.Code'] =>    $gen_account_prod,
                        $col_map['Label'] =>    $order->lib,
                        $col_map['Outcome'] =>    str_replace('.',$dec_separator, abs(round($order->income_prodht,2))),
                        $col_map['Income'] =>    $fillwith,
                        $col_map['Cost Center'] =>    $gen_analytic_prod,
                        $col_map['Empty 1'] =>    "",
                        $col_map['Empty 2'] =>    "",
                        $col_map['Empty 3'] =>    "",
                        $col_map['Empty 4'] =>    ""
                    );
                    ksort($prod_line);
                    woocommerce_accounting_add_line($output, $prod_line, $separator);
                    //Simple products by country
                } 
                elseif ($prod_opt == "2" && $order->income_prodht != 0) {
                    $wc_order = wc_get_order($order->ID);
                    $original_order_id = $wc_order->get_parent_id();
                    $original_order = wc_get_order( $original_order_id );
                    $order->countryid = $original_order->get_billing_country();
                    $order_country_acc = get_option('woocommerce_accounting_countries_acc');
                    $country_account_prod = $order_country_acc[$order->countryid]['acc'];
                    if (empty($country_account_prod)) {
                        $country_account_prod = $gen_account_prod;
                    }
                    $prod_line = array(
                        $col_map['Journal'] =>    $book_code,
                        $col_map['Date'] =>    $order->piecedate,
                        $col_map['Inv.Number'] =>    $order->number,
                        $col_map['Acc.Code'] =>    $country_account_prod,
                        $col_map['Label'] =>    $order->lib,
                        $col_map['Outcome'] =>    str_replace('.',$dec_separator, abs(round($order->income_prodht,2))),
                        $col_map['Income'] =>    $fillwith,
                        $col_map['Cost Center'] =>    $gen_account_prod,
                        $col_map['Empty 1'] =>    "",
                        $col_map['Empty 2'] =>    "",
                        $col_map['Empty 3'] =>    "",
                        $col_map['Empty 4'] =>    ""
                    );
                    ksort($prod_line);
                    woocommerce_accounting_add_line($output, $prod_line, $separator);
                }
                //Taxes
                if (($tax_applied_check != 0) && ($taxes_opt == "1") && ($order->order_discount == 0)) {
                    foreach ($tax_displays as $tax_display) {
                        $tax_line = array(
                            $col_map['Journal'] =>    $book_code,
                            $col_map['Date'] =>    $order->piecedate,
                            $col_map['Inv.Number'] =>    $order->number,
                            $col_map['Acc.Code'] =>    $tax_display['account'],
                            $col_map['Label'] =>    $order->lib,
                            $col_map['Outcome'] =>    str_replace('.',$dec_separator, abs(round($tax_display['amount'],2))),
                            $col_map['Income'] =>    $fillwith,
                            $col_map['Cost Center'] =>    "",
                            $col_map['Empty 1'] =>    "",
                            $col_map['Empty 2'] =>    "",
                            $col_map['Empty 3'] =>    "",
                            $col_map['Empty 4'] =>    ""
                        );
                        ksort($tax_line);
                        woocommerce_accounting_add_line($output, $tax_line, $separator);
                    }
                }
                else if ($tax_line_amount != 0){
                    $tax_line = array(
                        $col_map['Journal'] =>    $book_code,
                        $col_map['Date'] =>    $order->piecedate,
                        $col_map['Inv.Number'] =>    $order->number,
                        $col_map['Acc.Code'] =>    $tax_line_account,
                        $col_map['Label'] =>    $order->lib,
                        $col_map['Outcome'] =>    str_replace('.',$dec_separator, abs(round($tax_line_amount,2))),
                        $col_map['Income'] =>    $fillwith,
                        $col_map['Cost Center'] =>    "",
                        $col_map['Empty 1'] =>    "",
                        $col_map['Empty 2'] =>    "",
                        $col_map['Empty 3'] =>    "",
                        $col_map['Empty 4'] =>    ""
                    );
                    ksort($tax_line);
                    woocommerce_accounting_add_line($output, $tax_line, $separator);
                }
                // Exceptionnal Income
                if ($chk > 0 && $chk_op > 0) {
                    $cred_line = array(
                        $col_map['Journal'] =>    $book_code,
                        $col_map['Date'] =>    $order->piecedate,
                        $col_map['Inv.Number'] =>    $order->number,
                        $col_map['Acc.Code'] =>    $exptcred_acc,
                        $col_map['Label'] =>    $order->lib,
                        $col_map['Outcome'] =>    $fillwith,
                        $col_map['Income'] =>    str_replace('.',$dec_separator, round($chk,2)),
                        $col_map['Cost Center'] =>    "",
                        $col_map['Empty 1'] =>    "",
                        $col_map['Empty 2'] =>    "",
                        $col_map['Empty 3'] =>    "",
                        $col_map['Empty 4'] =>    ""
                    );
                    ksort($cred_line);
                    woocommerce_accounting_add_line($output, $cred_line, $separator);
                }
                //Exceptionnal Outcome
                elseif ($chk > 0 && $chk_op < 0) {
                    $deb_line = array(
                        $col_map['Journal'] =>    $book_code,
                        $col_map['Date'] =>    $order->piecedate,
                        $col_map['Inv.Number'] =>    $order->number,
                        $col_map['Acc.Code'] =>    $exptchar_acc,
                        $col_map['Label'] =>    $order->lib,
                        $col_map['Outcome'] =>    str_replace('.',$dec_separator, round($chk,2)),
                        $col_map['Income'] =>    $fillwith,
                        $col_map['Cost Center'] =>    "",
                        $col_map['Empty 1'] =>    "",
                        $col_map['Empty 2'] =>    "",
                        $col_map['Empty 3'] =>    "",
                        $col_map['Empty 4'] =>    ""
                    );
                    ksort($deb_line);
                    woocommerce_accounting_add_line($output,$deb_line, $separator);
                }
            }
        }
        fclose($output) or die("Can't close php://output");
        exit;
    } 
    else {
        wp_safe_redirect(add_query_arg([
            'page'=>'woocommerce_accounting_exporter',
            'format'=>'refunds',
            'error'=>'no_orders',
        ], admin_url('admin.php')));
        exit;
    }
}
?>
