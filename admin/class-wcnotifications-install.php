<?php
/**
 * install-classes
 * The library of all the installation class
 * @author Bakalski  <Georgi Bakalski>
 * @package <Plugin Name> Plugin
 * @subpackage Installation Class
 * @version <Version>
 */
 
class wc_notifications_install {
 
    /**
     * install
     * Do the things
     */
    public function install() {
        $this->checkop();
    }
 
    /**
     * Restores the WP Options to the defaults
     * Deletes the default options set and calls checkop
     */
    public function restore_op() {
        delete_option('plugin_abbr_op');
        $this->checkop();
    }
 
    /**
     * Creates the options
     */
    private function checkop() {
        //check if option is already present
        //option key is plugin_abbr_op, but can be anything unique
	$wc_notifications_email_details = array ();
        $array_from = array();
	$array_to = array();
        $array_from[0] = 'pending';
	$array_to[0] = 'processing';
        $template = '<strong>Order From:</strong>
{order_billing_name}
**************************

<strong>Details:</strong>
**************************
{email_order_items_table}

**************************

{email_order_total_footer}

<strong>Address</strong>
**************************

{order_billing_name}
{order_shipping_address_1}

{order_shipping_city}

{order_shipping_state}

{order_shipping_postcode}

{order_shipping_country}

<i>tel: {order_billing_phone}</i>

**************************

Hardware Group LTD';

        if(!get_option('WCNotifications_email_details')) {
            //not present, so add
           $op = array(  		'title' => 'IPP Order Notification',
                                        'description'   => 'Order Notification Receipt',
                                        'subject'       => 'IPP Order Notification',
                                        'recipients'    => '',
                                        'heading'       => 'NEW IPP ORDER',
                                        'from_status'   => $array_from,
                                        'to_status'     => $array_to,
                                        'template'      => $template,
                                        'order_action'  => 'on',
                                        'enable'        => 'on',
                                        'send_customer' => 'on',

            );
	    $op['id'] = uniqid( 'wnotifications' );
 	    array_push( $wc_notifications_email_details, $op );
        $array_from1 = array();
	$array_to1 = array();
        $array_from1[0] = 'processing';
	$array_to1[0] = 'completed';
        $template1 = '<strong>PACKING SLIP</strong>
**************************
<strong>Details:</strong>
**************************
{email_order_items_table}

**************************
<strong>Address</strong>
**************************

{order_billing_name}
{order_shipping_address_1}

{order_shipping_city}

{order_shipping_state}

{order_shipping_postcode}

{order_shipping_country}

<i>tel: {order_billing_phone}</i>

**************************

Hardware Group LTD';

           $op1 = array(  		'title' => 'IPP Packing Slip',
                                        'description'   => 'Packing Slip',
                                        'subject'       => 'IPP Packing Slip',
                                        'recipients'    => '',
                                        'heading'       => 'PACKING SLIP',
                                        'from_status'   => $array_from1,
                                        'to_status'     => $array_to1,
                                        'template'      => $template1,
                                        'order_action'  => 'on',
                                        'enable'        => 'on',
                                        'send_customer' => 'on',

            );
	    $op1['id'] = uniqid( 'wnotifications' );
 	    array_push( $wc_notifications_email_details, $op1 );
            add_option('WCNotifications_email_details', $wc_notifications_email_details);            
            
            
            
            
        }
    }
}
