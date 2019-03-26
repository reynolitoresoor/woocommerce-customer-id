<?php
/*
Plugin Name: WooCommerce Customer ID
Description: WooCommerce Customer ID custom plugin
Author: Marc Aguilar
Author URI: 
Version: 1.0.1

Copyright: © 2012 XMS (email : developer@xtar.biz)
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/*
*
* Prevents direct access data leaks
*   
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'CUSTOMER_ID__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once(CUSTOMER_ID__PLUGIN_DIR . 'class-customer-id-model.php');
require_once(CUSTOMER_ID__PLUGIN_DIR . 'class-customer-id.php');
require_once(CUSTOMER_ID__PLUGIN_DIR . 'class-customer-id-admin.php');

if ( is_admin() ) {
  add_action( 'init', array( 'Customer_Id_Admin', 'init' ) );
} else {
  add_action( 'init', array( 'Customer_Id', 'init') );
}

/*
*
* Creates userprofile table when plugin activated
*   
*/
register_activation_hook ( __FILE__, 'on_activate' );

/*
*  When plugin deactivated
*/

register_activation_hook( __FILE__, 'woocomerce_customer_id_activate' );

  /*
  *  Create tables and update usermeta when plugin activated
  */
  function on_activate() {
      $customer_id = new Customer_ID_Model();
      $customer_id->createTables();
      $customer_id->updateUserMeta();
      
  }

  function medical_license_endpoint() {
    add_rewrite_endpoint( 'medical-license', EP_ROOT | EP_PAGES );
  }
  add_action( 'init', 'medical_license_endpoint' );

  // ------------------
  // 2. Add new query var
   
  function medical_license_query_vars( $vars ) {
      $vars[] = 'medical-license';
      return $vars;
  }
   
  add_filter( 'query_vars', 'medical_license_query_vars', 0 );
  
  // ------------------
  // 3. Insert the new endpoint into the My Account menu
   
  function medical_license_link_my_account( $items ) {
      $items['medical-license'] = 'Medical License';
      return $items;
  }
  add_filter( 'woocommerce_account_menu_items', 'medical_license_link_my_account' );
  
  /*
  *  Medical License content
  */
  add_action( 'woocommerce_account_medical-license_endpoint', array('Customer_Id', 'medical_license_endpoint_content') );
?>
