<?php

/**

  The Initial Developer of the Original Code is
  Matthieu  - http://www.programmation-facile.com/

  Contributor(s) :

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



//_________________________________________________________________________________
//____________________ for the visitor plugin _____________________________________
//_________________________________________________________________________________


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



//_________________________________________________________________________________
//____________________ for the admin plugin _______________________________________
//_________________________________________________________________________________



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


//_________________________________________________________________________________
//____________________ for the plugin _____________________________________________
//_________________________________________________________________________________



/**
 * Define the language by default for the plugin
 * 
 * @param  [type] $locale [description]
 * @return [type]         [description]
 */
function aweber_devfacile_redefine_locale($locale)
{
    $wpsx_url = $_SERVER['REQUEST_URI'];
    $wpsx_url_lang = substr($wpsx_url, -4); // test for the last 4 chars:
    
    if ( $wpsx_url_lang == "-fr/")
        $locale = 'fr_FR';
    else if ( $wpsx_url_lang == "-en/") 
        $locale = 'en_US';
    else // fallback to default
        $locale = 'en_US'; 

    // remove the hook of language
    remove_action('locale', 'aweber_devfacile_redefine_locale');
    
    return $locale;
}




