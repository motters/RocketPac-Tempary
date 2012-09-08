<?php

/*
 * This class lets you connect to a LDAP system and retrive users information. It also lets you auth via LDAP
 * @Author Sam Mottley
 * EXAMPLE USEAGE
 * //Start The ldap Class		
 * $LDAP = new ldap();
 *
 * //Here we set the private settings
 * $LDAP->writeSettings(array('host'=>'directory.example.co.uk', 'baseDn'=>'o=Your Company,c=GB', 'port'=>'PORTNUMBER', 'customErrorMessages' =>array('errorLdapSearch'=>'Custome error message here', 'errorIncorrectPassword'=>'Another custome error message here')));
 *
 * //Here we get a users details in an array
 * $GetDetails = $LDAP->ldapUserToArray('username', 'uid', NULL);//null does not have to be there but there to show you can add a filter to your returned array
 * print_r($GetDetails);
 *
 * //Here we will check the login detils
 * $CheckAuth = $LDAP->ldapAuthenticate('username', 'password');
 * print_r($CheckAuth);
 */

class ldap {
    /* Set the LDAP host address
     * @Author Sam Mottley
     */

    private $host;

    /* Set the base DN for the directory. 
     * @Author Sam Mottley
     */
    private $baseDn;

    /* Set the LDAP port
     * @Author Sam Mottley
     */
    private $port;

    /* Error return which field was wrong username or password or return invalid login
     * @Author Sam Mottley
     * @Default Show invaid login (More secure)
     */
    private $showIndviduallyInvalidAuthField = false;

    /* Degbug mode whether to log bugs or not
     * @Author Sam Mottley
     * @Default LOG Errors
     */
    private $logErros = app_debugmode;

    /* Set the Error messages 
     * @Author Sam Mottley
     */
    private $ErrorMessages = array('errorLdapSearch' => 'Unable to search LDAP check your parameters',
        'errorSearchTerm' => 'Invlaid search term',
        'errorLdapBind' => 'Unable to bind LDAP check your parameters',
        'errorLdapConnect' => 'Unable to connect to LDAP check your parameters',
        'errorEmptyPrams' => 'Please check your parameters',
        'errorIncorrectPassword' => 'Password was incorrect',
        'errorIncorrectUsername' => 'User could not be found',
        'errorIncorrectLoginDetails' => 'Invalid login details',
        'errorEmptyLoginDetails' => 'No username and / or password supplied',
        'errorSettingMissing' => 'You have missing the setting {setting}');

    /* Use the function so that we can write to a private varble outside and inside the class  
     * @Author Sam Mottley
     */

    public function writeToVar($Var, $Value) {
        $this->$Var = $Value;
    }

    /* Here we write a settings array set outside the class to the private settings varables  
     * @Author Sam Mottley
     */

    public function writeSettings($settingsArray) {
		$errorString = '';
        if (!empty($settingsArray['host'])) {
            $this->writeToVar('host', $settingsArray['host']);
        } else {
            $errorString .= str_replace('{setting}', 'host', $this->ErrorMessages['errorSettingMissing']);
        }
        if (!empty($settingsArray['baseDn'])) {
            $this->writeToVar('baseDn', $settingsArray['baseDn']);
        } else {
            $errorString .= str_replace('{setting}', 'baseDn', $this->ErrorMessages['errorSettingMissing']);
        }
        if (!empty($settingsArray['port'])) {
            $this->writeToVar('port', $settingsArray['port']);
        } else {
            $errorString .= str_replace('{setting}', 'port', $this->ErrorMessages['errorSettingMissing']);
        }
        if (!empty($settingsArray['customErrorMessages'])) {
            foreach ($settingsArray['customErrorMessages'] as $errorType => $customeMessage) {
                $this->writeToVar($ErrorMessage[$errorType], $customeMessage);
            }
        }

        notification::StoreWarning($errorString);
    }

    /* Pulls back array of detail of the user from LDAP
     * @Author Sam Mottley
     */

    public function ldapUserToArray($searchData, $searchTerm, $filter = NULL) {
        //generate the search string query

        $search = $searchTerm . '=' . $searchData;
        //Reset warnings
        notification::ResetWarning();

        //Check for empty prams
        if (($this->host != '') && ($this->baseDn != '') && ($this->port != '')) {
            // connecting to LDAP And check connection
            $ldapConnection = @ldap_connect($this->host, $this->port);
            //Check connection
            if ($ldapConnection) {
                //bind the connection
                $ldapBind = @ldap_bind($ldapConnection);
                //Check bind was successfull
                if ($ldapBind) {
                    //Check that search term is VALID
                    if (strstr($search, '=')) {
                        //Do the LDAP dearch on the specified search term
                        $Returned = @ldap_search($ldapConnection, $this->baseDn, $search);
                        if ($Returned) {
                            //Get array of results from the search
                            $Results = @ldap_get_entries($ldapConnection, $Returned, $filter); //filter 
                            //Close the LDAP connection   
                            @ldap_close($ldapConnection);
                            return $Results;
                        } else {
                            notification::StoreWarning($this->ErrorMessages['errorLdapSearch']);
                            return false;
                        }
                    } else {
                        notification::StoreWarning($this->ErrorMessages['errorSearchTerm']);
                        return false;
                    }
                } else {
                    notification::StoreWarning($this->ErrorMessages['errorLdapBind']);
                    return false;
                }
            } else {
                notification::StoreWarning($this->ErrorMessages['errorLdapConnect']);
                return false;
            }
        } else {
            notification::StoreWarning($this->ErrorMessages['errorEmptyPrams']);
            return false;
        }
    }

    /* Authenticate user against LDAP
     * @Author Sam Mottley
     */

    public function ldapAuthenticate($username, $password) {

        //Reset warnings
        notification::ResetWarning();

        if (($this->host != '') && ($this->baseDn != '') && ($this->port != '')) {
            if (($username != "") && ($password != "")) {
                //Connect to LDAP
                $ldapConnection = @ldap_connect($this->host, $this->port);
                if ($ldapConnection) {
                    //Search for the user
                    $returned = @ldap_search($ldapConnection, $this->baseDn, 'uid=' . $username);
                    if ($returned) {
                        //Retrieve the array
                        $result = @ldap_get_entries($ldapConnection, $returned);
                        if ($result[0]) {
                            //The user does exsists but now to check the password
                            if (@ldap_bind($ldapConnection, $result[0]['dn'], $password)) {
                                //return $result[0]; //Return the users details to show auth was correct
                                return 1; //return 1 to show auth was correct
                            } else {
                                //decide whether to show passwors is ivalid or login has failed
                                if ($showIndviduallyInvalidAuthField == true) {
                                    notification::StoreWarning($this->ErrorMessages['errorIncorrectPassword']); //We could tell them there password was wrong but that us a bit un-sercure
                                    return false;
                                } else {
                                    notification::StoreWarning($this->ErrorMessages['errorIncorrectLoginDetails']);
                                    return false;
                                }
                            }
                        } else {
                            //decide whether to show passwors is ivalid or login has failed
                            if ($showIndviduallyInvalidAuthField == true) {
                                notification::StoreWarning($this->ErrorMessages['errorIncorrectUsername']); //We could tell them there username was wrong but that us a bit un-sercure
                                return false;
                            } else {
                                notification::StoreWarning($this->ErrorMessages['errorIncorrectLoginDetails']);
                                return false;
                            }
                        }
                    } else {
                        notification::StoreWarning($this->ErrorMessages['errorLdapSearch']);
                        return false;
                    }
                } else {
                    notification::StoreWarning($this->ErrorMessages['errorLdapConnect']);
                    return false;
                }
            } else {
                notification::StoreWarning($this->ErrorMessages['errorEmptyLoginDetails']);
                return false;
            }
        } else {
            notification::StoreWarning($this->ErrorMessages['errorEmptyPrams']);
            return false;
        }
    }

}

?>