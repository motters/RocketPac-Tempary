<?php

/*
  #############################################################
  #Begin the class
  #############################################################
  $ftp = new ftp();

  ############################################################
  #Enter the FTP settings
  #############################################################
  $settings = $ftp->writeSettings(array('host'=>'example.co.uk',
  'authType'=>'login',
  'username'=>'ftpclass',
  'password'=>'password',
  'port'=>'21',
  'protocol '=>'ftp',
  'passiveMode'=>'1'));

  #############################################################
  #Connect to the server
  #############################################################
  $connect = $ftp->ftpConnect();

  ############################################################
  #Examples of each command is below
  #############################################################
  //$makeDirectory = $ftp->makeDirectory('test2');
  //$listFiles = $ftp->listFilesInDirectory('testdirectory/test/test/');
  //$deleteFile = $ftp->ftpDeleteFile('test2/controller.ext.php');
  //$deleteFolder = $ftp->ftpDeleteEmptyFolder('test2');
  //$chmodFile = $ftp->ftpChmodFile('test2/packages.txt', 0644);
  //$sysType = $ftp->sysType();
  //$currentDirectory = $ftp->currentDirectory();
  //$parentDirectory = $ftp->parentDirectory();
  //$getFileSize = $ftp->getFileSize('module.xml');
  //$lastModified = $ftp->lastModified('module.xml');
  //$uploadFileContentToExsistingFile = $ftp->uploadFileContentToFile('test.html', 'test1.html');
  //$uploadFile = $ftp->uploadFile('test.html', 'test1.html');
  //$uploadFile = $ftp->uploadFile('test.html', 'test.html'); retrieveFile
  //$currentDirectory2 = $ftp->setCurrentDirectory('test/test/');
  //$uploadFile = $ftp->uploadFile('robots.txt', 'test/robots.txt');
  //$downloadFile = $ftp->retrieveFile('public/robots.txt', 'test/robots.txt');

  ###############################################################
  #Simple returning a printed array from listFiles function
  ###############################################################
  return print_r($listFiles);
 */

class ftp extends rocketpack {
    /* Set the Host 
     * @Author Sam Mottley
     */

    private $host;

    /* Set ftp protocol
     * @Author Sam Mottley
     * @Types ftp, ssl
     */
    private $protocol = 'fpt';

    /* Set the Auth type anonymous OR login
     * @Author Sam Mottley
     */
    private $authType;

    /* Set the ftp username 
     * @Author Sam Mottley
     */
    private $username;

    /* Set the ftp password 
     * @Author Sam Mottley
     */
    private $password;

    /* Set the frt port number 
     * @Author Sam Mottley
     */
    private $port = '21';

    /* Set the whether passive mode is on or off.
     * @Author Sam Mottley
     * @Default is ON 
     */
    private $passiveMode = 1;

    /* Where the FTP connect is sorted 
     * @Author Sam Mottley
     */
    private $storeConnection;

    /* Set the Error messages 
     * @Author Sam Mottley    
     */
    private $ErrorMessages = array('errorFtpMode' => 'Could not set a ftp mode',
        'errorAuth' => 'Could not log you in. Please check your Username and Password',
        'errorFtpConnection' => 'Could not connect to FTP server check the host and port prams',
        'errorCreateStructure' => 'Could not create directory structure {DirStructure}',
        'errorChangeDirectory' => 'Could not chnage directory {Location}',
        'errorDeleteFile' => 'Could not delete {file}',
        'errorDeleteFolder' => 'Could not delete {folder}',
        'errorChmod' => 'Could not chmode {file} with the permisions of {permissions}',
        'errorSystemType' => 'Could not find the system type',
        'errorCurrentDirectory' => 'Could not find the current directory',
        'errorParentDirectory' => 'Could not go to parent directory',
        'errorFileSize' => 'Could not get the file size {file}',
        'errorModifiedDate' => 'Could not be find the last modified date of {file}',
        'errorUpdateFile' => 'File {fileLocation} could not be uploaded to {fileServer} with {transferMode}. Please try again later ',
        'errorUploadFileContent' => 'File {fileLocation} could not be uploaded to {fileServer} with {transferMode}. Please try again later ',
        'errorDownloadFile' => 'There was an error downloading the file to {fileLocation} from the location {fileServer} with {transferMode}',
        'errorMoveFolder' => 'Could not move the folder {newLocation} to {currentLocation}',
        'errorRenameItem' => 'Could not rename {itemOldName} to {itemNewName}',
        '' => '');

    /* Here we define the php version id
     * @Author Sam Mottley
     */

    public function definePHPVersion() {
        //Here we work out the PHP version
        if (!defined('PHP_VERSION_ID')) {
            $version = explode('.', PHP_VERSION);
            define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
        }
    }

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
        $this->writeToVar('host', $settingsArray['host']);
        $this->writeToVar('authType', $settingsArray['authType']);
        $this->writeToVar('username', $settingsArray['username']);
        $this->writeToVar('password', $settingsArray['password']);
        $this->writeToVar('port', $settingsArray['port']);
        $this->writeToVar('passiveMode', $settingsArray['passiveMode']);
        $this->writeToVar('protocol', $settingsArray['protocol']);

        if (!empty($settingsArray['customErrorMessages'])) {
            foreach ($settingsArray['customErrorMessages'] as $errorType => $customeMessage) {
                $this->writeToVar('ErrorMessages[' . $errorType . ']', $customeMessage);
            }
        }
    }

    /* Connect via SSL and uses standard ftp functions
     * @Author Sam Mottley
     */

    public function sslConnect() {
        if (function_exists('ftp_ssl_connect')) {
            // set up basic ssl connection
            $this->storeConnection = ftp_ssl_connect($this->host);

            if ($this->storeConnection) {
                // login with username and password
                if ($this->authType == 'anonymous') {
                    //Here we login via a anonymous auth
                    $stausLogin = ftp_login($this->storeConnection, 'anonymous', '');
                } else {
                    //Here we login with the username and password
                    $stausLogin = ftp_login($this->storeConnection, $this->username, $this->password);
                }
            }

            //Here we check that they login successully
            if ($stausLogin) {
                if (ftp_pasv($this->storeConnection, $this->passiveMode)) {
                    $this->definePHPVersion();
                    return true;
                } else {
                    notification::StoreWarning($this->ErrorMessages['errorFtpMode']);
                    return false;
                }
            } else {
                notification::StoreWarning($this->ErrorMessages['errorAuth']);
                return false;
            }
            $this->writeToVar('protocol', 'ssl');
        } else {
            $this->ftpConnect();
            $this->writeToVar('protocol', 'ftp');
        }
    }

    /* Connect to ftp 
     * @Author Sam Mottley
     */

    public function ftpConnect() {
        //Connect to ftp server
        $this->storeConnection = ftp_connect($this->host, $this->port);

        if ($this->storeConnection) {
            //Now we need to login	
            if ($this->authType == 'anonymous') {
                //Here we login via a anonymous auth
                $stausLogin = ftp_login($this->storeConnection, 'anonymous', '');
            } else {
                //Here we login with the username and password
                $stausLogin = ftp_login($this->storeConnection, $this->username, $this->password);
            }

            //Here we check that they login successully
            if ($stausLogin) {
                if (ftp_pasv($this->storeConnection, $this->passiveMode)) {
                    $this->definePHPVersion();
                    return true;
                } else {
                    notification::StoreWarning($this->ErrorMessages['errorFtpMode']);
                    return false;
                }
            } else {
                notification::StoreWarning($this->ErrorMessages['errorAuth']);
                return false;
            }
        } else {
            notification::StoreWarning($this->ErrorMessages['errorFtpConnection']);
            return false;
        }
        $this->writeToVar('protocol', 'ftp');
    }

    /* Make directory 
     * @Author Sam Mottley
     */

    public function makeDirectory($makeDirectory, $permissions = '0644', $recursive = TRUE) {
        $errorMakingDir = 0;
        if ($recursive == TRUE) {
            $currentLocation = $this->currentDirectory();
            $exlodePath = explode('/', $makeDirectory);
            $path = '';
            foreach($exlodePath as $number => $folderName){
                $path .= '/' . $folderName; 
                if (@$this->setCurrentDirectory($folderName) == false) {
                    if (ftp_mkdir($this->storeConnection, $path)) {
                       $this->chmodItem($makeDirectory, $permissions);
                    } else {
                       $errorMakingDir = 1;
                    }
                }
            }
           
            $this->setCurrentDirectory($currentLocation);
            
            if($errorMakingDir == 1){
                notification::StoreWarning(str_replace('{DirStructure}', $makeDirectory, $this->ErrorMessages['errorCreateStructure']));
                return false;
            }else{
                return true;
            }
        } else {
            if (ftp_mkdir($this->storeConnection, $makeDirectory)) {
                $this->chmodItem($makeDirectory, $permissions);
                return true;
            } else {
                notification::StoreWarning(str_replace('{DirStructure}', $makeDirectory, $this->ErrorMessages['errorCreateStructure']));
                return false;
            }
        }
        
    }

    /* Change you position in the ftp folder structure
     * @Author Sam Mottley
     */

    public function setCurrentDirectory($location) {

        //Here we are going to chnage the current directory
        if (@ftp_chdir($this->storeConnection, $location)) {
            return true;
        } else {
            notification::StoreWarning(str_replace('{Location}', $location, $this->ErrorMessages['errorChangeDirectory']));
            return false;
        }
    }

    /* list files in current ftp directory 
     * @Author Sam Mottley
     */

    public function listFilesInDirectory($directory = '.', $additionPrams = '-la') {
        $fileArray = ftp_nlist($this->storeConnection, $additionPrams . ' ' . $directory);
        if ($fileArray) {
            return $fileArray;
        } else {
            return false;
        }
    }

    /* Delete a file NOT a folder
     * @Author Sam Mottley
     */

    public function deleteFile($file) {
        //Attempt to delete file
        $ftpDelete = ftp_delete($this->storeConnection, $file);

        //Check if apptempt was successfull
        if ($ftpDelete) {
            return true;
        } else {
            notification::StoreWarning(str_replace('{file}', $file, $this->ErrorMessages['errorDeleteFile']));
            return false;
        }
    }

    /* Delete a folder with **no** content in it
     * @Author Sam Mottley
     */

    public function deleteEmptyFolder($folder) {
        //Attempt to delete Folder
        $ftpDelete = ftp_rmdir($this->storeConnection, $folder);

        //Check if apptempt was successfull
        if ($ftpDelete) {
            return true;
        } else {
            notification::StoreWarning(str_replace('{folder}', $folder, $this->ErrorMessages['errorDeleteFolder']));
            return false;
        }
    }

    /* Chmod item give it any path and it will handle it and return you to you past position 
     * @Author Sam Mottley
     */

    public function chmodItem($file, $permissions) {
        $ftpChmod = '';
        //Make sire we are in the correct directory
        if (strstr($file, '/')) {
            $pathInfo = pathinfo($file);
            $currentLocation = $this->currentDirectory();
            if ($this->setCurrentDirectory($pathInfo['dirname'])) {
                //Attempt to chmod file
                $ftpChmod = ftp_chmod($this->storeConnection, $permissions, $pathInfo['basename']);
            }
            $this->setCurrentDirectory($currentLocation);
        } else {
            //Attempt to chmod file
            $ftpChmod = ftp_chmod($this->storeConnection, $permissions, $file);
        }
        //Check if apptempt was successfull
        if ($ftpChmod) {
            return true;
        } else {
            notification::StoreWarning(str_replace('{file}', $file, str_replace('{permissions}', $permissions, $this->ErrorMessages['errorChmod'])));
            return false;
        }
    }

    /* Returns the OS platform
     * @Author Sam Mottley
     */

    public function sysType() {
        //Attempt to find system type
        $systemType = ftp_systype($this->storeConnection);

        //Check if apptempt was successfull
        if ($systemType) {
            return $systemType;
        } else {
            notification::StoreWarning($this->ErrorMessages['errorSystemType']);
            return false;
        }
    }

    /* Returns the current directory you are in
     * @Author Sam Mottley
     */

    public function currentDirectory() {
        //Attempt to find current directory
        $currentDirectory = ftp_pwd($this->storeConnection);

        //Check if apptempt was successfull
        if ($currentDirectory) {
            return $currentDirectory;
        } else {
            notification::StoreWarning($this->ErrorMessages['errorCurrentDirectory']);
            return false;
        }
    }

    /* Move your position to the above direction
     * @Author Sam Mottley
     */

    public function parentDirectory() {
        //Attempt to go to parent directory
        $parentDirectory = ftp_cdup($this->storeConnection);

        //Check if apptempt was successfull
        if ($parentDirectory) {
            return $parentDirectory;
        } else {
            notification::StoreWarning($this->ErrorMessages['errorParentDirectory']);
            return false;
        }
    }

    /* Get the current size of the file
     * @Author Sam Mottley
     */

    public function getFileSize($file) {
        //Attempt to get file size
        $getFileSize = ftp_size($this->storeConnection, $file);

        //Check if apptempt was successfull
        if ($getFileSize) {
            return $getFileSize;
        } else {
            notification::StoreWarning(str_replace('{file}', $file, $this->ErrorMessages['errorFileSize']));
            return false;
        }
    }

    /* Find when the file was last altered NOTE:Wont work on all servers!
     * @Author Sam Mottley
     */

    public function lastModified($file) {
        $lastModified = ftp_mdtm($this->storeConnection, $file);

        if ($lastModified) {
            return $lastModified;
        } else {
            notification::StoreWarning(str_replace('{file}', $file, $this->ErrorMessages['errorModifiedDate']));
            return false;
        }
    }
    
    /*Check that item does not already exsist
     * @Author Sam Mottley
     */
    public function checkItemPresent($item, $location){
        
        $items = $this->listFilesInDirectory($location, '');
        
        if(in_array($item,$items)){
            return true;
        }else{
            return false;
        }
        
    }
    /* Rename an item from one name to another and move it too :)
     * @Author Sam Mottley
     */

    public function renameAndMoveItem($itemOldName, $itemNewName) {
        $pathInfoOldName = pathinfo($itemOldName);
        $pathInfoNewName = pathinfo($itemNewName);
        
        //check to see were not going to over write an item!
        if($this->checkItemPresent($pathInfoNewName['basename'], $pathInfoNewName['dirname'])){
            //item already exsists
            notification::StoreWarning(str_replace('{itemOldName}', $itemOldName, str_replace('{itemNewName}', $itemNewName, $this->ErrorMessages['errorRenameItem'])));
        
            return false;
        }else{
            if ((strstr($itemOldName, '/')) && ($itemOldName['extension'] != '')) {
                $currentLocation = $this->currentDirectory();
                if ($this->setCurrentDirectory($pathInfoOldName['dirname'])) {
                    //Attempt to rename file
                    if (($ftpChmod = ftp_rename($this->storeConnection, $pathInfoOldName['basename'], $pathInfoNewName['basename']))) {
                        return true;
                    } else {
                        notification::StoreWarning(str_replace('{itemOldName}', $itemOldName, str_replace('{itemNewName}', $itemNewName, $this->ErrorMessages['errorRenameItem'])));

                        return false;
                    }
                }
                $this->setCurrentDirectory($currentLocation);
            } else {
                //Attempt to rename  file
                if (($ftpChmod = ftp_rename($this->storeConnection, $itemOldName, $itemNewName))) {
                    return true;
                } else {
                    notification::StoreWarning(str_replace('{itemOldName}', $itemOldName, str_replace('{itemNewName}', $itemNewName, $this->ErrorMessages['errorRenameItem'])));

                    return false;
                }
            }
        }
    }

    /* Move a folder with its contains from one position to another
     * @Author Sam Mottley
     */

    public function renameAndMoveFolder($currentLocation, $newLocation) {
        $pathInfoOldName = pathinfo($currentLocation);
        $pathInfoNewName = pathinfo($newLocation);
        
        //check to see were not going to over write an item!
        if($this->checkItemPresent($pathInfoNewName['basename'], $pathInfoNewName['dirname'])){
             //item already exsists
        }else{
            if ((strstr($currentLocation, '/')) && (@$pathInfoOldName['extension'] == '')) {
                    $moveFolder = ftp_rename($this->storeConnection, $currentLocation, $newLocation);

                    return $moveFolder;
            } else {
                //ERROR HERE
                notification::StoreWarning(str_replace('{newLocation}', $newLocation, str_replace('{currentLocation}', $currentLocation, $this->ErrorMessages['errorMoveFolder'])));
                return false;
            }
        }
    }

    /* Sends an arbitrary command to an FTP server
     * @Author Sam Mottley
     * UNTESTED
     */

    public function rawFtp($command) {
        return ftp_raw($this->storeConnection, $command);
    }

    /* Requests execution of a command on the FTP server
     * @Author Sam Mottley
     * UNTESTED
     */

    public function ftp_exec($command) {
        return ftp_exec($this->storeConnection, $command);
    }

    /* Has the ability to tell if a file is binary or Ascii !!Still in beta!!
     * @Author Sam Mottley
     * UNTESTED
     */

    public function isAscii($file) {
        if (PHP_VERSION_ID < 6000) {
            //In php version less than 6 we do not have a function to tell if it's binary or not.
            if (is_file($file)) {
                $content = str_replace(array("\n", "\r", "\t", "\v", "\b"), '', file_get_contents($file));
                return ctype_print($content); //Content is Ascii compatable
            } else {
                return false; //contents is binary
            }
        } else {
            //In PHP 6 we have a function to see if a file is binary or not
            $fileContents = file_get_contents($file);
            $isBinary = is_binary($fileContents);
            if ($isBinary == true) {
                return false; //contents is binary
            } else {
                return true; //Content is Ascii compatable	
            }
        }
    }

    /* Get the local file content and upload the contents to an EXSISTING file on the server and over write the content.
     * @Author Sam Mottley
     */

    public function uploadFileContentToExsistingFile($fileLocation, $fileServer, $transferMode = FTP_BINARY) {
        //Here we upload the file
        $uploadFile = ftp_put($this->storeConnection, $fileServer, $fileLocation, $transferMode);

        //here we check the file has been uploaded
        if ($uploadFile) {
            return true;
        } else {
            notification::StoreWarning(str_replace('{transferMode}', $transferMode, str_replace('{fileServer}', $fileServer, str_replace('{fileLocation}', $fileLocation, $this->ErrorMessages['errorUploadFileContent']))));
            return false;
        }
    }

    /* Upload a file
     * @Author Sam Mottley
     * @VAR fileServer relivent to root of ftp access
     * @VAR fileLocation relivent to web server 
     */

    public function uploadFile($fileLocation, $fileServer, $transferMode = FTP_BINARY) {
        //Here we set the CURRENT directory
        $currentLocation = $this->currentDirectory();

        //set the new directory relivent to root
        $this->setCurrentDirectory('/'); //Set it to root
        //open file
        $filePointer = fopen($fileLocation, 'r');

        //Here we upload the file
        $uploadFile = ftp_fput($this->storeConnection, $fileServer, $filePointer, $transferMode);

        //Here we set the dictory back to what it was
        $this->setCurrentDirectory($currentLocation); //Set it to root
        //here we check the file has been uploaded
        if ($uploadFile) {
            return true;
        } else {
            notification::StoreWarning(str_replace('{transferMode}', $transferMode, str_replace('{fileServer}', $fileServer, str_replace('{fileLocation}', $fileLocation, $this->ErrorMessages['errorUpdateFile']))));
            return false;
        }
    }

    /* download a file
     * @Author Sam Mottley
     * @VAR fileServer relivent to root of ftp access
     * @VAR fileLocation relivent to web server 
     */

    public function retrieveFile($fileLocation, $fileServer, $transferMode = 'FTP_BINARY') {
        //File pointer
        $filePointer = fopen($fileLocation, 'w');

        //Here we set the CURRENT directory
        $currentLocation = $this->currentDirectory();

        //set the new directory relivent to root
        $this->setCurrentDirectory('/'); //Set it to root

        $retieveFile = ftp_fget($this->storeConnection, $filePointer, $fileServer, FTP_BINARY, 0);

        //Go back to the last directory
        $this->setCurrentDirectory($currentLocation);
        if ($retieveFile) {
            return true;
        } else {
            notification::StoreWarning(str_replace('{transferMode}', $transferMode, str_replace('{fileServer}', $fileServer, str_replace('{fileLocation}', $fileLocation, $this->ErrorMessages['errorDownloadFile']))));
            return false;
        }
    }

}

?>