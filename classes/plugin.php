<?php


class AWeberDevFacile 
{

	/**
	 * create some table to save value of counter for selected lists
	 * 
	 * @return [type] [description]
	 */
	public static function activate() 
	{
		//global $wpdb;

		/*
		if ( ! empty($wpdb->charset) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		
		if ( ! empty($wpdb->collate) )
			$charset_collate .= " COLLATE $wpdb->collate";

		// Add one library admin function for next function
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		
		// Data table
		maybe_create_table( $wpdb->name_table, "CREATE TABLE IF NOT EXISTS `{$wpdb->name_table}` (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`selected_list` varchar(255) NOT NULL default 'default-list',
			`nb_subscrivers` int(13) NOT NULL default '0',
			PRIMARY KEY (`id`)
		) $charset_collate AUTO_INCREMENT=1;");
		*/
	}
	

	/**
	 * option : delete table
	 * 
	 * @return [type] [description]
	 */
	public static function deactivate() 
	{
		//global $wpdb;

		// erase all saved options of the plugin
		$aOptionsSaved = array(
	        'aweber_devfacile_settings_lists',
	        'aweber_devfacile_connect_infos',
	        'aweber_devfacile_oauth_id',
	        'aweber_devfacile_oauth_removed',
	    );

	    foreach ($aOptionsSaved as $option)
	    {
	        delete_option($option);
	    }

        /*
		// delete table
	    foreach ($wpdb->tables as $table)
	    {
	        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . $table);
	    }
		*/
	}

}