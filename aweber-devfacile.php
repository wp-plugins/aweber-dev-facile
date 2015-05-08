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
 * echo do_shortcode('[AWcount selectList="your_list_name_1, your_list_name_2"]');
 * 
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
		// echo $dir . $prefix . $file . ".php <br/> \n";
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
	_devfacile_load_files( DEV_DIR.'classes/admin/', array( 'admin', 'copywriting', 'main' ) );

// Les fonctions
_devfacile_load_files( DEV_DIR.'functions/', array( 'api-AW-devfacile' ) );


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
	}
}


/**
 * launch initialisation of the plugin
 * 
 */
add_action( 'plugins_loaded', 'init_AWeberDevFacile_plugin' );


/**
 * [AWcount selectList="your_list_name_1, your_list_name_2"]
 * 
 */
add_shortcode( 'AWcount', 'get_AW_count' );


/**
 * To define the lang to use for translation
 * 
 */
if( get_locale() != 'fr_FR') 
	add_filter('locale','aweber_devfacile_redefine_locale');


/*
$calvin = "6 years";
$hobbes = "stuffed";
 
// "Prepare" the query
$sql = $wpdb->prepare( "INSERT INTO $wpdb->name_table( id, field1, field2 ) VALUES ( %d, %s, %s )", $_POST['id'], $calvin, $hobbes );
 
// Run it
$wpdb->query( $sql );
*/


