<?php

/**
 * @package AWeber Dev Facile
 */
/*
Plugin Name: AWeber Dev Facile
Plugin URI: http://www.programmation-facile.com/
Description: Display Total AWeber Subscribers Count from one or selected lists - exemple of use : [AWcount select="list-name-1,list-name-2,list-name-3,list-name-4"]  / Affiche le nombre total d'abonnés AWeber d'une ou plusieurs listes - exemple d'utilisation : [AWcount select="list-name-1,list-name-2,list-name-3,list-name-4"]. <a href="http://www.Developpement-Facile.com" target="_blank" >Cliquez ici pour Créer des Applications sur smartphones, tablettes et le web</a>
Version: 0.1
Author: Matthieu
Author URI: http://www.programmation-facile.com/
License: GPLv2 or later
Text Domain: www.programmation-facile.com
*/


/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/


// don't load directly
if ( !defined('ABSPATH') )
	die('-1');


/**
 * Exemple of use
 *
 * [AWcount select="list-name-1,list-name-2,list-name-3,list-name-4"] 
 */

// Plugin tables
global $wpdb;
$wpdb->tables[]   = 'aweber-devfacile';
$wpdb->name_table = $wpdb->prefix . 'aweber-devfacile';


// les constantes
define( 'DEV_URL', plugin_dir_url ( __FILE__ ) );
define( 'DEV_DIR', plugin_dir_path( __FILE__ ) );
define( 'DEV_VERSION', '0.1' );
define( 'DEV_NAME', 'aweber-devfacile' );
define( 'DEV_OPTION_SETTINGS', 'aweber-devfacile-settings' );


/**
 * Function for easy load and include files
 * 
 */
function _devfacile_load_files($dir, $files, $prefix = '') 
{
	foreach ($files as $file) 
	{
		// echo $dir . $prefix . $file . ".php <br/>";
		if ( is_file($dir . $prefix . $file . ".php") ) 
			require_once($dir . $prefix . $file . ".php");	
	}	
}

// api aweber
_devfacile_load_files( DEV_DIR.'aweber_api/', array( 'aweber_api' ) );

// Les classes clientes
_devfacile_load_files( DEV_DIR.'classes/', array( 'main', 'plugin', 'settings' ) );

// Les classes admin
if (is_admin()) 
	_devfacile_load_files( DEV_DIR.'classes/admin/', array( 'admin', 'page', 'main' ) );

// Les fonctions
_devfacile_load_files( DEV_DIR.'functions/', array( 'plugin', 'tpl' ) );


// Plugin activate/desactive hooks
register_activation_hook(__FILE__, array('AWeberDevFacile', 'activate'));
register_deactivation_hook(__FILE__, array('AWeberDevFacile', 'deactivate'));


// au moment du chargement des plugins
function init_AWeberDevFacile_plugin() 
{
	global $oAWeberDevFacile;

	// Load translations  -  How to internationalize your wordpress plugin
	load_plugin_textdomain ( DEV_NAME, false, basename(rtrim(dirname(__FILE__), '/')) . '/languages' );
	
	// Load client
	$oAWeberDevFacile['client'] = new AWeberDevFacile_Client();
	
	// Load Admin
	if ( is_admin() ) 
	{
		$oAWeberDevFacile['admin'] = new AWeberDevFacile_Admin();
		add_action( 'admin_menu', 'displayAdminAWeberDevFacile' );// create admin menu plugin
		//$oAWeberDevFacile['admin_page'] = new AWeberDevFacile_Admin_Page();
	}

	// Widget
	//add_action('widgets_init', create_function('', 'return register_widget("AWeberDevFacile_Widget");'));
}

add_action( 'plugins_loaded', 'init_AWeberDevFacile_plugin' );


/**
 * Add link in panel admin of wordpress
 * 
 * @return [type] [description]
 */
function displayAdminAWeberDevFacile()
{
	if (function_exists('add_options_page')) 
	{
		// add settings options for the plugin
        register_setting(DEV_OPTION_SETTINGS, 'aweber_devfacile_oauth_id');
        register_setting(DEV_OPTION_SETTINGS, 'aweber_devfacile_oauth_removed');
        register_setting(DEV_OPTION_SETTINGS, 'aweber_devfacile_settings_lists');

		// add a link tu sub menu settings
        add_options_page('AWeber DevFacile', 'AWeber DevFacile', 'manage_options', basename(__FILE__), 'displayAdminPageAWeberDevFacile');
    }
}


/**
 * Build the page in admin panel
 * 
 * @return [type] [description]
 */
function displayAdminPageAWeberDevFacile()
{
	global $oAWeberDevFacile;

	$oAppAdminAWeber = $oAWeberDevFacile['admin'];
	$oAppAdminAWeber->connectToAWeberAccount();
}




/**
 * Get AWeber count subscrivers from selected list
 * 
 * @return [type] [description]
 */
function get_AW_count($atts)
{
	global $oAWeberDevFacile, $wpdb;

	$nTotalSubscribers = 0;

	/*echo '<br/>--- atts <br/><pre>'; 
	print_r($atts);*/

	// get all selected lists
    extract(
    	shortcode_atts(
    		array(
    			'select' => '',    			
    		), 
    		$atts
    	)
    );

    $oStoredSettings = new AWeberDevFacile_Settings();
    $oDataInfos = $oStoredSettings->getDatasByLists($select);

    if( $oDataInfos->nCodeContinue == -9 )
    {
    	// get infos of list with API AWeber
	    $aLists = explode(",",$select);
		
		// if nothing select list
	    if(count($aLists)<=0)
	    	return 0;

		$oAppAWeber = $oAWeberDevFacile['client'];
		$nRetourConnect = $oAppAWeber->connectToAWeberAccount();

		if( $nRetourConnect == -1 || $nRetourConnect == -2 )
			return $oDataInfos->nTotalSubscrivers;// error code

		//$oAppAWeber->setDebug(true);

		// get all list subscribers
		foreach ($aLists as $value) 
		{
		    $oList = $oAppAWeber->findList($value);

		    if( $oList == -1 )
				return $oDataInfos->nTotalSubscrivers;// error code

		    /*echo '--- <br/><pre>'; 
			print_r($oList);*/

		    if( $oList->total_subscribers != 0 )
		    {
		        $nTotalSubscribers += $oList->total_subscribed_subscribers;// Number of Subscribers where status=subscribed
		        //$nTotalAllSubscribers += $oList->total_subscribers;// Number of Subscribers where status=subscribed
		    }    
		}

		unset($value); // delete reference on last element
		
		// remove datas in options wordpress
		$nCodeErrorRemove = $oStoredSettings->removeRecord($select);
		if( $nCodeErrorRemove == -1)
			return $nTotalSubscribers;// error code

		// store datas in options wordpress
		$oStoredSettings->addRecord($select, $nTotalSubscribers);
    }
    else
    	$nTotalSubscribers = $oDataInfos->nTotalSubscrivers;

	return $nTotalSubscribers;
}



/**
 * [AWcount selectList="your_list_name_1, your_list_name_2"]
 * 
 */
add_shortcode( 'AWcount', 'get_AW_count' );

/**
 * echo do_shortcode('[AWcount selectList="your_list_name_1, your_list_name_2"]');
 * 
 */
add_filter('widget_text', 'do_shortcode');


/*
$calvin = "6 years";
$hobbes = "stuffed";
 
// "Prepare" the query
$sql = $wpdb->prepare( "INSERT INTO $wpdb->name_table( id, field1, field2 ) VALUES ( %d, %s, %s )", $_POST['id'], $calvin, $hobbes );
 
// Run it
$wpdb->query( $sql );
*/


