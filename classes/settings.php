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

/**
 * Save options of the plugin
 * 	Selected lists and value of the counter (speed display infos, avoid conect to API AWeber)
 * 
 */
class AWeberDevFacile_Settings
{
	private $adminOptionsSettings = '';
	private $pluginAdminOptions = '';
	private $nValueExpire = 3600;// number of seconds



	public function __construct() 
	{
        $this->adminOptionsSettings = 'aweber_devfacile_settings_lists';
        $this->pluginAdminOptions = get_option($this->adminOptionsSettings);
	}


	/**
	 * for each options stored, 
	 * find object datas for the selected lists in enter
	 * 
	 * @param  [type] $sSelectedLists [description]
	 * @return [type]                 [description]
	 */
	public function getDatasByLists($sSelectedLists)
	{	
		// if the value amlready exist
		if ( ! isset($this->pluginAdminOptions['content_infos']) ) 
		{
			$oDataInfos = new AWeberDevFacile_dataInfos(-9, 0);
			return $oDataInfos;// no infos in database options
		}	
		else
		{
			extract($this->pluginAdminOptions);// get admin option AWeber Dev Facile

			/*echo '--- <br/>content_infos getDatasByLists<pre>'; 
			print_r($content_infos[0]);*/

			// get all records
			$bFindRecord = false;
			foreach ($content_infos[0] as $key => $oData) 
			{
			    if( $oData->sSelectedLists == $sSelectedLists )
			    {
			    	$bFindRecord = true;
			    	break; 
			    }   
			}

			if($bFindRecord == true)
			{
				//echo "<br /> bFindRecord : ".$oData->nTotalSubscrivers;
				$oDateTime = new DateTime();

				// test if update count is necessary
				if( ( $oData->nTimeStamp + $this->nValueExpire ) < ( $oDateTime->getTimestamp() ) )// update every hour
				{
			    	/*echo '--- <br/>delete record<pre>'; 
					print_r($content_infos[0]);*/

					$oDataInfos = new AWeberDevFacile_dataInfos(-9, $oData->nTotalSubscrivers);
				}
				else
					$oDataInfos = new AWeberDevFacile_dataInfos(1, $oData->nTotalSubscrivers);
	
				return $oDataInfos;// infos in database options
			}	
		}	

		$oDataInfos = new AWeberDevFacile_dataInfos(-9, 0);
		return $oDataInfos;// no infos in database options
	}


	/**
	 * Store informations of selected lists in options wordpress
	 * 
	 * @param  [type] $sSelectedLists [description]
	 * @return [type]                 [description]
	 */
	public function addRecord($sLists, $nCount)
	{
		$oStoredSettings = new AWeberDevFacile_OneField($sLists, $nCount);

		$aCountLists = array();

		// create an array with infos
		if ( ! isset($this->pluginAdminOptions['content_infos']) ) 
		{
			$aCountLists[0] = array();
		}	
		else
		{
			extract($this->pluginAdminOptions);// get admin option AWeber Dev Facile
			$aCountLists[0] = (array) $content_infos[0];
			/*echo '--- <br/>content_infos addRecord<pre>'; 
			print_r($content_infos);*/
		}

		array_push ($aCountLists[0], $oStoredSettings);
		/*echo '--- <br/>addRecord pluginAdminOptions<pre>'; 
		print_r($aCountLists);*/

		$this->pluginAdminOptions = array('content_infos' => $aCountLists,);
	    update_option($this->adminOptionsSettings, $this->pluginAdminOptions);
	}


	/**
	 * Remove informations of selected lists in options wordpress
	 * 
	 * @param  [type] $sSelectedLists [description]
	 * @return [type]                 [description]
	 */
	public function removeRecord($sSelectedLists)
	{

		if ( ! isset($this->pluginAdminOptions['content_infos']) ) 
		{
			return 1;// no infos, add it
		}	
		else
		{
			extract($this->pluginAdminOptions);// get admin option AWeber Dev Facile

			/*echo '<br/>--- removeRecord record<pre>'; 
			print_r($content_infos[0]);*/

			// get all records
			$bFindRecord = false;
			foreach ($content_infos[0] as $key => $oData) 
			{
			    if( $oData->sSelectedLists == $sSelectedLists )
			    {
			    	$bFindRecord = true;
			    	break; 
			    }   
			}

			if($bFindRecord == true)
			{
				$oDateTime = new DateTime();

				// test if update count is necessary
				if( ( $oData->nTimeStamp + $this->nValueExpire ) < ( $oDateTime->getTimestamp() ) )// update every hour
				{
					// remove record from options
					unset($content_infos[0][$key]);
					$aCountLists[0] = (array) $content_infos[0];
					$this->pluginAdminOptions = array('content_infos' => $aCountLists,);
	    			update_option($this->adminOptionsSettings, $this->pluginAdminOptions);

					//echo "<br /> update count is necessary ";
			    	/*echo '--- <br/>delete record<pre>'; 
					print_r($content_infos[0]);*/

					return 1;// record found and delete, add it
				}

				return -1;// record found, but no update necessary
			}	
		}	

		return 1;// no record found, add it
	}

}


/**
 * Object stored in settings option wordpress
 * 
 */
class AWeberDevFacile_OneField
{

    public $nTimeStamp = '';
    public $sSelectedLists = '';
    public $nTotalSubscrivers = '';



	public function __construct($sLists, $nCount) 
	{
		$oDate = new DateTime();
        $this->nTimeStamp = $oDate->getTimestamp();
        $this->sSelectedLists = $sLists;
        $this->nTotalSubscrivers = $nCount;
	}


	/**
	 * Return selected lists
	 * 
	 * @return [type] [description]
	 */
	/*public function getSelectedList()
	{
		return $this->sSelectedLists;
	}*/

}


/**
 * Object with number of subscribers and code continue
 * 
 */
class AWeberDevFacile_dataInfos
{

    public $nCodeContinue = '';
    public $nTotalSubscrivers = '';


	public function __construct($nCode, $nCount) 
	{
        $this->nCodeContinue = $nCode;
        $this->nTotalSubscrivers = $nCount;
	}
}


