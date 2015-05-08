<?php


/**
 * Display count subscribers of selected lists
 * 
 */
class AWeberDevFacile_Client 
{
    private $application = '';
    private $account = '';

    private $adminOptionsName = '';
    private $pluginAdminOptions = '';


	public function __construct() 
	{
        $this->adminOptionsName = 'aweber_devfacile_connect_infos';
	}


    public function connectToAWeberAccount()
    {
        $this->pluginAdminOptions = get_option($this->adminOptionsName);

        // app is authorised by AWeber
        if ($this->pluginAdminOptions['access_key']) 
        {
            extract($this->pluginAdminOptions);// get admin option AWeber Dev Facile

            $error_ = null;
            try 
            {
                //echo '<br/>--- page AWeberAPI !!'; 
                $this->application = new AWeberAPI($consumer_key, $consumer_secret);
                $this->account = $this->application->getAccount($access_key, $access_secret);
            } 
            catch (AWeberException $e) 
            {
                $error_ = get_class($e);
                //echo "error API AWeber".$error_;
                $this->account = null;
            }

            if (!$this->account) 
            {
                if($error_ != 'AWeberOAuthException' && $error_ != 'AWeberOAuthDataMissing') 
                {
                    //echo $this->messages['temp_error'];
                    $temp_error = True;
                } 
                else 
                {
                    $this->deauthorizeAWeberAccount();
                    //echo $this->messages['auth_failed'];
                }

                return -2;// code erreur
            }

        }
        else
            return -1;// code erreur

        $oList = $this->findList('default');
        $this->setDebug(false);

        return 1;// all is ok
    }


    /**
     * Get list infos by name
     * 
     * @param  [type] $listName [description]
     * @return [type]           [description]
     */
    public function findList($listName)
    {
        try
        {
            //must pass an associative array to the find method
            $foundLists = $this->account->lists->find(array('name' => $listName));

            return $foundLists[0];
        }
        catch(Exception $exc)
        {
            // print $exc;
        }

        return -1;// code erreur
    }   


    /**
     * Add debug output when app comunicate with API AWeber
     * 
     * @param [type] $bValue [description]
     */
    public function setDebug($bValue)
    {
        # set this to true to view the actual api request and response
        $this->application->adapter->debug = $bValue;
    } 

}

