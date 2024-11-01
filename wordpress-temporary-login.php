<?php
/**
 * Plugin Name: WordPress Temporary Login Account CEED (Create,Email,Extend,Delete)
 * Plugin URI: https://traitway.com/product/temporary-login-account-ceed/
 * Description: Let's create WordPress login accounts and email the credentials. You can set time limit to login account expired.You can extend the login period in settings.
 * Version: 1.0.2
 * Author: Traitway
 * Author URI: https://traitway.com/
 */

if (!defined('ABSPATH')){
    exit;
}
if (!defined('CEED_MAIN_URL')) {
    define('CEED_MAIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('CEED_MAIN_PATH')) {
    define('CEED_MAIN_PATH', plugin_dir_path(__FILE__));
}

if (!defined('CEED_CSS')) {
    define('CEED_CSS', CEED_MAIN_URL . "resources/css/");
}
if (!defined('CEED_JS')) {
    define('CEED_JS', CEED_MAIN_URL . "resources/js/");
}

add_action('admin_menu','traitway_ceed_menu_for_settings');

function traitway_ceed_menu_for_settings(){
	$id_of_the_login_user = get_current_user_id();
    $user_data = new WP_User($id_of_the_login_user);
    $allow = FALSE;
    $user_role = $user_data->roles;
    $user_roles_default = array("administrator");
    foreach($user_role as $value)
    {
        if(in_array($value, $user_roles_default))
        {
            $allow = TRUE;
        }
    }
    if($allow)
	{
		if(in_array("administrator", $user_role))
        {
            $cap = "administrator";
        }
		add_menu_page(__('Create Temporary Login','ceed'), __('WordPress Login', 'ceed'), $cap, "ceed", "ceed_settings_call_fun", "dashicons-admin-users", 25);
    }
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'traitway_ceed_action_link');

function traitway_ceed_action_link($links){
    $plugin_links = array(
        '<a href="' . admin_url('admin.php?page=ceed') . '">'.__('Settings','ceed').'</a>',
        '<a href="https://traitway.com/request-a-quote/">'.__('Services','ceed').'</a>',
        '<a href="https://traitway.com/support/">'.__('Support','ceed').'</a>',
    );
    return array_merge($plugin_links, $links);
}

function ceed_settings_call_fun()
{
    $id_of_the_login_user = get_current_user_id();
    require_once(ABSPATH.'wp-admin/includes/user.php' );
    wp_enqueue_script("wordpress-temporary-login", CEED_JS . "wordpress-temporary-login.js");
    wp_enqueue_style("ceed-wordpress-temporary-login", CEED_CSS . "ceed-wordpress-temporary-login.css");
    if(is_super_admin( $id_of_the_login_user )){
	   include_once(CEED_MAIN_PATH . "includes/wordpress-temporary-login-settings.php");
    }
}

$time_cred = get_option('ceed_user_datas');
global $wpdb;
if(!empty($time_cred)){
    foreach ($time_cred as $key => $value) {
        require_once(ABSPATH.'wp-admin/includes/user.php' );
        $value['date'] = str_replace('-', '', $value['date']);
        if( strtotime( $value['date'] ) <= current_time( 'timestamp' ) ){
            $table  = $wpdb->prefix.'users';
            $sql = "delete from ".$table." where ID = ".$value['user_id'];
            $wpdb->get_results($sql);
            $table  = $wpdb->prefix.'usermeta';
            $sql = "delete from ".$table." where user_id = ".$value['user_id'];
            $wpdb->get_results($sql); 
            unset( $time_cred[$key] );
            update_option('ceed_user_datas',$time_cred);
        }
    }
}

?>