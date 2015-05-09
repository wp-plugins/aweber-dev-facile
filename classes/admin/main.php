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
 * Admin class
 * Connect app AWeber Dev Facile
 * 
 */
class AWeberDevFacile_Admin
{
	private $APP_ID = '';
	private $account = '';

	private $adminOptionsName = '';
	private $adminOauthID = '';
	private $adminOauthIDRemove = '';
	private $pluginAdminOptions = '';
	
	private $messages = '';



	public function __construct()
	{
		$this->APP_ID = "61eb9f5a";

        $this->adminOptionsName = 'aweber_devfacile_connect_infos';
        $this->adminOauthID = 'aweber_devfacile_oauth_id';
        $this->adminOauthIDRemove = 'aweber_devfacile_oauth_removed';
	    $this->messages = array(); 
	}



	/**
	 * create token for app AWeber Dev Facile
	 * 
	 * @return [type] [description]
	 */
	public function connectToAWeberAccount()
    {
    	echo '<div class="wrap">
    		<h2>'.__( 'Title-Page-Admin', DEV_NAME ).'</h2>
    		<h3>'.__( 'Text-Page-Admin', DEV_NAME ).'</h3>
    			<form name="aweber_devfacile_import_form" method="post" action="options.php">';

    	wp_nonce_field('update-options');// add 2 hidden fileds for redirect user

    	echo '<input type="hidden" name="aweber_forms_import_hidden" value="Y">
        	<table class="form-table">';

    	$this->pluginAdminOptions = get_option($this->adminOptionsName);

    	// get the group of options
        settings_fields(DEV_OPTION_SETTINGS);

        $oauth_removed = get_option($this->adminOauthIDRemove);
        $oauth_id = get_option($this->adminOauthID);

        $authorize_success = False;
        $temp_error = null;
        $error = null;

        // Check to see if they removed the connection
        $authorization_removed = False;

        if ($oauth_removed == 'TRUE' || (!empty($_GET['reauth']) && $_GET['reauth'] == True)) 
        	$authorization_removed = True;// authorization with app AWeber is removed   

        if ($oauth_removed == 'FALSE')
        {
        	echo "<br />oauth_removed FALSE : ".$oauth_removed;
            /*if (get_option('aweber_comment_checkbox_toggle') == 'OFF' and
                get_option('aweber_registration_checkbox_toggle') == 'OFF')
            {
                $options['create_subscriber_comment_checkbox'] = 'OFF';
                $options['create_subscriber_registration_checkbox'] = 'OFF';
            }
            else {
                echo $this->messages['no_list_selected'];
                $error = True;
            }*/
        }

        if ( is_numeric($oauth_removed) ) 
        {
        	echo "<br />oauth_removed numeric : ".$oauth_removed;
            /*$options['list_id_create_subscriber'] = $oauth_removed;
            $options['create_subscriber_comment_checkbox'] = get_option('aweber_comment_checkbox_toggle');
            $options['create_subscriber_registration_checkbox'] = get_option('aweber_registration_checkbox_toggle');
            
            if (strlen(get_option('aweber_signup_text_value')) < 7)
                echo $this->messages['signup_text_too_short'];
            else
                $options['create_subscriber_signup_text'] = get_option('aweber_signup_text_value');
            
            update_option($this->widgetOptionsName, $options);*/
        }

        if ($authorization_removed)
        {
            $this->deauthorizeAWeberAccount();
            $this->pluginAdminOptions = get_option($this->adminOptionsName);

            echo '<div id="message" class="updated"><p>'.__( 'Connection-AWeber-closed', DEV_NAME ).'</p></div>';
            
            $error = $temp_error = null;
        }
        // save the connection to app AWeber Dev Facile
        elseif ($oauth_id and !$this->pluginAdminOptions['access_secret']) 
        {
	        // Then they just saved a key and didn't remove anything
	        // Check it's validity then save it for later use
	        $error_code = "";
	        try 
	        {
	            list($consumer_key, $consumer_secret, $access_key, $access_secret) = AWeberAPI::getDataFromAweberID($oauth_id);
	        }
	        catch (AWeberAPIException $exc) 
	        {
	            list($consumer_key, $consumer_secret, $access_key, $access_secret) = null;
	            # make error messages customer friendly.
	            $descr = $exc->description;
	            $descr = preg_replace('/http.*$/i', '', $descr);     # strip labs.aweber.com documentation url from error message
	            $descr = preg_replace('/[\.\!:]+.*$/i', '', $descr); # strip anything following a . : or ! character
	            $error_code = " ($descr)";
	        } 
	        catch (AWeberOAuthDataMissing $exc) 
	        {
	            list($consumer_key, $consumer_secret, $access_key, $access_secret) = null;
	        } 
	        catch (AWeberException $exc) 
	        {
	            list($consumer_key, $consumer_secret, $access_key, $access_secret) = null;
	        }

	        if (!$access_secret) 
	        {
	            $msg =  '<div id="aweber_access_token_failed" class="error">';
	            $msg .= __( 'Connection-AWeber-not-possible', DEV_NAME )."$error_code<br />";
	            $error = True;

	            # show oauth_id if it failed and an api exception was not raised
	            if ($error_code == "") 
	                $msg .= __( 'Authorization-code-entered', DEV_NAME )."$oauth_id <br />";

	            $msg .= __( 'sure-entered-complete-authorization', DEV_NAME )."</div>";
	            echo $msg;

	            $this->deauthorizeAWeberAccount();
	        } 
	        else 
	        {
	            $this->pluginAdminOptions = array(
	                'consumer_key' => $consumer_key,
	                'consumer_secret' => $consumer_secret,
	                'access_key' => $access_key,
	                'access_secret' => $access_secret,
	            );
	            update_option($this->adminOptionsName, $this->pluginAdminOptions);
	        }
	    }

	    // app is authorised by AWeber
	    if ($this->pluginAdminOptions['access_key']) 
	    {
	        extract($this->pluginAdminOptions);// get admin option AWeber Dev Facile

	        $error_ = null;
	        try 
	        {
	        	//echo '<br/>--- admin AWeberAPI !!'; 
	            $oApplication = new AWeberAPI($consumer_key, $consumer_secret);
	    	    $this->account = $oApplication->getAccount($access_key, $access_secret);
	        } 
	        catch (AWeberException $e) 
	        {
	            $error_ = get_class($e);
	            $account = null;
	        }

	        if (!$this->account) 
	        {
	            $this->pluginAdminOptions = get_option($this->adminOptionsName);

	            if($error_ != 'AWeberOAuthException' && $error_ != 'AWeberOAuthDataMissing') 
	            {
	                echo $this->messages['temp_error'];
	                $temp_error = True;
	            } 
	            else 
	            {
	                $this->deauthorizeAWeberAccount();
	                echo $this->messages['auth_failed'];
	            }

	        }
	        else 
	            $authorize_success = True;

	    }// end if app authorized

	    if(empty($_GET['updated']) || $error || $temp_error) 
	    {
	        echo '<script type="text/javascript">
	            	jQuery("#setting-error-settings_updated").hide();
	        		</script>';
	    }


	    // connection with app AWeber ok
	    if ($authorize_success) 
	    {
	    	echo '<p>'.__( 'Connection-AWeber-successfull', DEV_NAME ).'</p>
	    	<input type="hidden" id="aweber-settings-hidden-value" name="'.$this->adminOauthIDRemove.'" value="TRUE" />
	    		<p class="submit">
                    <input type="submit" id="aweber-settings-button" class="button-primary" value="'.__( 'btn-remove-connection', DEV_NAME ).'"/>
	        	</p>
	        <h2>'.__( 'Title-AWeber-Dev-Facile-Settings', DEV_NAME ).'</h2>
	            <p>Work in progress...</p>';

			$oCopywriting = new AWeberDevFacileCopy_Admin();
			$oCopywriting->addFormCopywritingFacile();

	        echo ' <input type="hidden" name="action" value="update" />
        		<input type="hidden" name="page_options" value="'.$this->adminOauthID.'" />';
	    }
	    else
	    {// ask for authorization

	    	echo '<tr valign="top">
                <th scope="row">'.__( 'step-1', DEV_NAME ).'</th>
                <td><a target="_blank" 
                    href="https://auth.aweber.com/1.0/oauth/authorize_app/'.$this->APP_ID.'">'.__( 'get-authorization-code', DEV_NAME ).'</a></td>
                </tr>

                <tr valign="top">
                <th scope="row">'.__( 'step-2', DEV_NAME ).' '.__( 'paste-authorization-code', DEV_NAME ).'</th>
                <td><input type="text" size="69" name="'.$this->adminOauthID.'"/></td>
                </tr>
                
                </table>
                <p class="submit">
                    <input type="submit" id="aweber-settings-button" class="button-primary" value="'.__( 'btn-make-connection', DEV_NAME ).'" />
                </p>';
	    }


	    if ($authorization_removed or $authorize_success)
	    {
            echo '<script type="text/javascript" >
                    jQuery.noConflict();
                    jQuery("#aweber_auth_error").hide();
                </script>';
 		}

 		// update value of plugin options
 		echo '<input type="hidden" name="action" value="update" />
        	  <input type="hidden" name="page_options" value="'.$this->adminOauthID.'" />';

        /*echo '<script type="text/javascript" >
                jQuery(\'#aweber-settings-save-button\').live(\'click\', function() {
                    jQuery(\'#aweber-settings-hidden-value\).val(jQuery(\'#AWeberWebformPluginWidgetOptions-list\').val());
                    jQuery(\'#aweber-settings-hidden-signup-text-value\').val(jQuery(\'#aweber-create-subscriber-signup-text\').val());
                });
        </script>';*/


	    echo '</table>';
	    echo '</form></div>';

	}// end if connectToAWeberAccount 



    /**
     * Remove authorization of AWeber app
     * 
     * @return [type] [description]
     */
    public function deauthorizeAWeberAccount()
    {
        $admin_options = get_option($this->adminOptionsName);
        $admin_options = array(
            'consumer_key' => '',
            'consumer_secret' => '',
            'access_key' => '',
            'access_secret' => '',
        );

        update_option($this->adminOptionsName, $admin_options);

        delete_option($this->adminOauthID);
        delete_option($this->adminOauthIDRemove);
    }

}


/**
 * register_setting user profil
 * 
 */
class AWeberDevFacile_Admin_Page 
{
	public function __construct() 
	{
		
	}
}


