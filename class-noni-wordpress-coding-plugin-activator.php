<?php

/**
 * Fired during plugin activation
 *
 * @link       noni
 * @since      1.0.0
 *
 * @package    Noni_Wordpress_Coding_Plugin
 * @subpackage Noni_Wordpress_Coding_Plugin/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Noni_Wordpress_Coding_Plugin
 * @subpackage Noni_Wordpress_Coding_Plugin/includes
 * @author     Nonibrands <dev@nonibrands.com>
 */
class Noni_Wordpress_Coding_Plugin_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;

		$table_name = $wpdb->prefix . "noni_addresses"; 

		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name){
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				address1 varchar(55) NOT NULL,
				city varchar(55) NOT NULL,
				province varchar(55) NOT NULL,
				postal_code mediumint(9) NOT NULL,
				country varchar(55) NOT NULL,
				PRIMARY KEY  (id)
			  ) $charset_collate;";
	
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
	}

}
