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
  'username'=>'username',
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
  //Make a directoy
  $makeDirectory = $ftp->makeDirectory('dir/dir2/dir/dir8', $permissions = '0644', $recursive = TRUE);
  //Rename and have the option to move an item
  $renameAndMoveItem = $ftp->renameAndMoveItem('dir/dir2/dir/dir8/index.php', 'dir/dir2/dir/dir8/index5.php');
  //Rename and have the option to move an folder with its contents
  $moveFolder = $ftp->renameAndMoveFolder('dir/dir2/dir/dir8', 'dir/dir2/dir2/dir8');
  //Check for a file or folder on the ftp server       Item name, Item location
  $there = $ftp->checkItemPresent('index.php', 'dir/dir2/dir/dir8');
  //List all the files in the directory
  $listFiles = $ftp->listFilesInDirectory('.');
  //Delete a file
  $deleteFile = $ftp->ftpDeleteFile('dir/testFile.ext.php');
  //Delete an empty folder
  $deleteFolder = $ftp->ftpDeleteEmptyFolder('dir/dir2/dir/dir8');
  //Chmod File or folder
  $chmodFile = $ftp->ftpChmodFile('dir/dir2/dir/dir8/packages.txt', 0644);
  //Get the systems OS
  $sysType = $ftp->sysType();
  //Get the current directory you ar in
  $currentDirectory = $ftp->currentDirectory();
  //Go up to parent directory of th directory you are in
  $parentDirectory = $ftp->parentDirectory();
  //Get the files size
  $getFileSize = $ftp->getFileSize('module.xml');
  //Find out when file was last modified
  $lastModified = $ftp->lastModified('module.xml');
  //Upload file content and update a current file on the server      file Location WEB SERVER, file ftp Server, transferMode
  $uploadFileContentToExsistingFile = $ftp->uploadFileContentToFile('test.html', 'test1.html');
  //upload file from WEB server to FTP server as a new file or over write the one on th web server      file Location WEB SERVER, file ftp Server, transferMode
  $uploadFile = $ftp->uploadFile('test.html', 'test.html');
  //Set which directory you want to be in
  $currentDirectory2 = $ftp->setCurrentDirectory('dir/dir2/');
  //Download / Retrive file from FTP sevrer to WEB server      Where to download to, File location on FTP Server, Transferr mode
  $downloadFile = $ftp->retrieveFile('public/robots.txt', 'test/robots.txt');

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
        'errorRetrieveFile' => 'Could not download {file}');

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
        if(!empty($settingsArray['host'])){ $this->writeToVar('host', $settingsArray['host']);}
        if(!empty($settingsArray['authType'])){$this->writeToVar('authType', $settingsArray['authType']); }
        if(!empty($settingsArray['username'])){ $this->writeToVar('username', $settingsArray['username']); }
        if(!empty($settingsArray['password'])){ $this->writeToVar('password', $settingsArray['password']); }
        if(!empty($settingsArray['port'])){$this->writeToVar('port', $settingsArray['port']); }
        if(!empty($settingsArray['passiveMode'])){ $this->writeToVar('passiveMode', $settingsArray['passiveMode']); }
        if(!empty($settingsArray['protocol'])){$this->writeToVar('protocol', $settingsArray['protocol']); }

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

    public function makeDirectory($makeDirectory, $permissions = '777', $recursive = TRUE) {
		$wasError = '';
		if(!is_array($makeDirectory)){
			$makeDirectory = array(array($makeDirectory, $permissions, $recursive));
		}
		foreach($makeDirectory as $number => $information){
			$errorMakingDir = 0;
			$ftpCreate = false;
			if ($information[2] == TRUE) {
				$currentLocation = $this->currentDirectory();
				$exlodePath = explode('/', $information[0]);
				$path = '';
				foreach ($exlodePath as $number => $folderName) {
					$path .= '/' . $folderName;
					if (@$this->setCurrentDirectory($folderName) == false) {
						if ($ftpCreate = ftp_mkdir($this->storeConnection, $path)) {
							if($information[1] != false) { $this->chmodItem($path, $information[1]); }
						} else {
							$wasError[] = $information[0];
						}
					}
				}
	
				$this->setCurrentDirectory($currentLocation);
			} else {
				if ($ftpCreate = ftp_mkdir($this->storeConnection, $information[0])) {
					$this->chmodItem($information[0], $$information[1]);
				} else {
					$wasError[] = $information[0];
				}
			}
		}
		
		if($ftpCreate){
			return true;
		}else{
			notification::StoreWarning(str_replace('{DirStructure}', $makeDirectory, $this->ErrorMessages['errorCreateStructure']));
			return false;
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
      // $wasError = array();
	    $fileArray = ftp_nlist($this->storeConnection, $additionPrams . ' ' . $directory);
        if ($fileArray) {
            return $fileArray;
        } else {
			$errorSting = '';
			//foreach($wasError as $fileError){
				$errorSting .= $fileError . 'and';	
			//}
            return false;
        }
    }

    /* Delete a file NOT a folder
     * @Author Sam Mottley
	 * UNTESTED
     */

    public function deleteFile($file) {
		$wasError = '';
		if(!is_array($file)){
			$file = array($file);
		}
		foreach($file as $singleFile){
			if (strstr($singleFile, '/')) {
				$pathInfo = pathinfo($singleFile);
				$currentLocation = $this->currentDirectory();
				if ($this->setCurrentDirectory($pathInfo['dirname'])) {
					//Attempt to delete file
					$ftpDelete = ftp_delete($this->storeConnection, $pathInfo['basename']);
					 if (!$ftpDelete) {
					 	$wasError[] = $singleFile;
					 }
						
				}
			}else{
				//Attempt to delete file
				$ftpDelete = ftp_delete($this->storeConnection, $singleFile);	
				if (!$ftpDelete) {
					 	$wasError[] = $singleFile;
				}
			}
		}
        //Check if apptempt was successfull
        if ($ftpDelete) {
            return true;
        } else {
			$errorSting = '';
			foreach($wasError as $fileError){
				$errorSting .= $fileError . 'and';	
			}
            notification::StoreWarning(str_replace('{file}', $errorSting, $this->ErrorMessages['errorDeleteFile']));
            return false;
        }
    }

    /* Delete a folder with **no** content in it
     * @Author Sam Mottley
	 *UNTESTED
     */

    public function deleteEmptyFolder($folder) {
		$wasError = '';
		if(!is_array($folder)){
			$folder = array($folder);
		}
		foreach($folder as $singleFolder){
        	//Attempt to delete Folder
        	$ftpDelete = ftp_rmdir($this->storeConnection, $singleFolder);
			
			if (!$ftpDelete) {
				$wasError[] = $singleFolder;
			}
		}
        //Check if apptempt was successfull
        if ($ftpDelete) {
            return true;
        } else {
			$errorSting = '';
			foreach($wasError as $fileError){
				$errorSting .= $fileError . 'and';	
			}
			
            notification::StoreWarning(str_replace('{folder}', $errorSting, $this->ErrorMessages['errorDeleteFolder']));
            return false;
        }
    }

    /* Chmod item give it any path and it will handle it and return you to you past position 
     * @Author Sam Mottley
	 * UNTESTED
     */

    public function chmodItem($file, $permissions) {
       $wasError = '';
		if(!is_array($file)){
			$file = array($file);
		}
		foreach($file as $singlefile){
			$ftpChmod = '';
			//Make sire we are in the correct directory
			if (strstr($singlefile, '/')) {
				$pathInfo = pathinfo($singlefile);
				$currentLocation = $this->currentDirectory();
				if ($this->setCurrentDirectory($pathInfo['dirname'])) {
					//Attempt to chmod file
					$ftpChmod = ftp_chmod($this->storeConnection, $permissions, $pathInfo['basename']);
					
					if (!$ftpChmod) {
						$wasError[] = $singlefile;
					}
				}
				$this->setCurrentDirectory($currentLocation);
			} else {
				//Attempt to chmod file
				$ftpChmod = ftp_chmod($this->storeConnection, $permissions, $singlefile);
				if (!$ftpChmod) {
					$wasError[] = $singlefile;
				}
			}
		}
        //Check if apptempt was successfull
        if ($ftpChmod) {
            return true;
        } else {
			$errorSting = '';
			foreach($wasError as $fileError){
				$errorSting .= $fileError . 'and';	
			}
			
            notification::StoreWarning(str_replace('{file}', $errorSting, str_replace('{permissions}', $permissions, $this->ErrorMessages['errorChmod'])));
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
	 * UNTESTED
     */

    public function getFileSize($file) {
		$wasError = '';
		if(!is_array($file)){
			$file = array($file);
		}
		foreach($file as $singlefile){
        	//Attempt to get file size
        	$getFileSize[$singlefile] = ftp_size($this->storeConnection, $singlefile);
			if (!$getFileSize[$singlefile]) {
				$wasError[] = $singlefile;
			}
		}
        //Check if apptempt was successfull
        if (!in_array(false, $getFileSize)) {
            return $getFileSize;
        } else {
			foreach($getFileSize as $fileLocation => $status){
				if(in_array(false, $status)){
					$filesError .= 	$fileLocation . ', ';
				}
			}
            notification::StoreWarning(str_replace('{file}', $filesError, $this->ErrorMessages['errorFileSize']));
            return false;
        }
    }

    /* Find when the file was last altered NOTE:Wont work on all servers!
     * @Author Sam Mottley
     */

    public function lastModified($file) {
		$wasError = '';
		if(!is_array($file)){
			$file = array($file);
		}
		foreach($file as $singlefile){
        	$lastModified[$singlefile] = ftp_mdtm($this->storeConnection, $singlefile);
			if (!$lastModified[$singlefile]) {
				$wasError[] = $singlefile;
			}
		}
		
         if (!in_array(false, $lastModified)) {
            return $lastModified;
         } else {
			foreach($lastModified as $fileLocation => $status){
				if(in_array(false, $status)){
					$filesError .= 	$fileLocation . ', ';
				}
			}
            notification::StoreWarning(str_replace('{file}', $filesError, $this->ErrorMessages['errorModifiedDate']));
            return false;
        }
    }

    /* Check that item does not already exsist
     * @Author Sam Mottley
     */

    public function checkItemPresent($item, $locations) {
		$wasError = '';
		if(!is_array($item)){
			$item = array($item=>$locations);
		}
		foreach($item as $singleItem => $location){
			echo $singleItem;
			$command = $this->listFilesInDirectory($location, '');
			if(in_array($singleItem, $command)){ 
				$items[$singleItem] = true; 
			}else{ 
				$items[$singleItem] = false; 
			}
		}
		return $items;
	
    }

    /* Rename an item from one name to another and move it too :)
     * @Author Sam Mottley
	 * UNTESTED
     */

    public function renameAndMoveItem($itemOldName, $itemNewName = NULL) {
		$wasError = '';
		if(!is_array($itemOldName)){
			$item = array(array($itemOldName, $itemNewName));
		}else{
			$item = $itemOldName;	
		}
		foreach($item as $number => $information){
        	$pathInfoOldName = pathinfo($information[0]);
        	$pathInfoNewName = pathinfo($information[1]);
			$itemOldName = $information[0];
			$itemNewName = $information[1];
			
			//check to see were not going to over write an item!
			$checkPresent = $this->checkItemPresent($pathInfoNewName['basename'], $pathInfoNewName['dirname']);

			if ($checkPresent[$pathInfoNewName['basename']] == true) {
				//item already exsists
				$wasError[$itemOldName][] = 'itemNotExsists';
			} else {
				if ((strstr($itemOldName, '/')) && ($itemOldName['extension'] != '')) {
					$currentLocation = $this->currentDirectory();
					if ($this->setCurrentDirectory($pathInfoOldName['dirname'])) {
						//Attempt to rename file
						if (($ftpRename[$itemOldName] = ftp_rename($this->storeConnection, $itemOldName, $itemNewName))) {
							//return true;
						} else {
							$wasError[$itemOldName][] = 'renameFailed';
						}
					}else{
						$wasError[$itemOldName][] = 'setDirectory';	
					}
					$this->setCurrentDirectory($currentLocation);
				} else {
						//Attempt to rename  file
						if (($ftpRename = ftp_rename($this->storeConnection, $itemOldName, $itemNewName))) {
							//return true;
						} else {
							$wasError[$itemOldName][] = 'renameFailed';
						}
				}
			}
		}
		
		if($wasError == ''){
			return true;
		}else{
			$errorString = '';
			foreach($wasError as $file => $error){
				switch ($file){
					case 'itemNotExsists':
						$errorString .= $file . ' could not be found ';
					break;
					case 'renameFailed':
						$errorString .= $file . ' could not be renamed ';
					break;
					case 'setDirectory':
						$errorString .= $file .  'could not find the directory ';
					break;
				}
			}
			print_r($wasError);
			notification::StoreWarning(str_replace('{errorList}', $errorString, str_replace('{itemOldName}', $itemOldName, str_replace('{itemNewName}', $itemNewName, $this->ErrorMessages['errorRenameItem']))));	
			return false;		
		}
    }

    /* Move a folder with its contains from one position to another
     * @Author Sam Mottley
     */

    public function renameAndMoveFolder($currentLocation, $newLocation = NULL) {
		$wasError = '';
		if(!is_array($currentLocation)){
			$item = array(array($currentLocation, $newLocation));
		}else{
			$item = $currentLocation;	
		}
		foreach($item as $number => $information){
			$pathInfoOldName = pathinfo($information[0]);
			$pathInfoNewName = pathinfo($information[1]);
			$currentLocation = $information[0];
			$newLocation = $information[1];
	
			//check to see were not going to over write an item!
			$ItemPresent = $this->checkItemPresent($pathInfoNewName['basename'], $pathInfoNewName['dirname']);
			if ($ItemPresent[$pathInfoNewName['basename']]) {
				//item already exsists
				$wasError[$currentLocation][] = 'itemExsists';
			} else {
				if ((strstr($currentLocation, '/')) && (@$pathInfoOldName['extension'] == '')) {
					if($moveFolder[$currentLocation] = ftp_rename($this->storeConnection, $currentLocation, $newLocation)){
						
					}else{
						$wasError[$currentLocation][] = 'renameFailed';	
					}
	
				} else {
					//ERROR HERE
					$wasError[$currentLocation][] = 'setDirectory';	
					return false;
				}
			}
		}
		if($wasError == ''){
			return true;
		}else{
			$errorString = '';
			foreach($wasError as $file => $error){
				switch ($file){
					case 'itemExsists':
						$errorString .= $file . ' could not be found ';
					break;
					case 'renameFailed':
						$errorString .= $file . ' could not be renamed ';
					break;
					case 'setDirectory':
						$errorString .= $file .  'could not find the directory ';
					break;
				}
			}
			notification::StoreWarning(str_replace('{newLocation}', $newLocation, str_replace('{currentLocation}', $currentLocation, $this->ErrorMessages['errorMoveFolder'])));
			return false;		
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
        $wasError = '';
		if(!is_array($fileLocation)){
			$arrayInfo = array(array($fileLocation, $fileServer, $transferMode));
		}
		foreach($arrayInfo as $number => $singleData){
			//Here we upload the file
       		if($uploadFile[$singleData[1]] = ftp_put($this->storeConnection, $fileServer, $fileLocation, $transferMode)){
			}else{
				$wasError[$singleData[0]] = 'uploadingFile:'.$singleData[2];
			}
		}
        //here we check the file has been uploaded
        if($wasError == ''){
			return true;
		}else{
			$errorString = '';
			foreach($wasError as $file => $error){
				$errorIndv = explode(':', $error);
				switch ($errorIndv[0]){
					case 'uploadingFile':
						$errorString .= str_replace('{transferMode}', $errorIndv[1], str_replace('{fileLocation}', $file, $this->ErrorMessages['errorUpdateFile'])) . ' ';
					break;
				}
			}
            notification::StoreWarning($errorString);
			return false;		
		}    
	}

    /* Upload a file
     * @Author Sam Mottley
     * @VAR fileServer relivent to root of ftp access
     * @VAR fileLocation relivent to web server 
     */

    public function uploadFile($fileLocation, $fileServer, $transferMode = FTP_BINARY) {
		$wasError = '';
		if(!is_array($fileLocation)){
			$arrayInfo = array(array($fileLocation, $fileServer, $transferMode));
		}
		foreach($arrayInfo as $number => $singleData){
			//Here we set the CURRENT directory
			$currentLocation = $this->currentDirectory();
	
			//set the new directory relivent to root
			$this->setCurrentDirectory('/'); //Set it to root
			//open file
			if($filePointer = fopen($fileLocation, 'r')){
	
				//Here we upload the file
				if($uploadFile[$singleData[0]] = ftp_fput($this->storeConnection, $singleData[1], $filePointer, $singleData[2])){
					
				}else{
					$wasError[$singleData[0]] = 'uploadingFile:'.$singleData[2];
				}
			}else{
				$wasError[$singleData[0]] = 'openingFile:'.$singleData[2];
			}
			//Here we set the dictory back to what it was
			$this->setCurrentDirectory($currentLocation); 
		}
       if($wasError == ''){
			return true;
		}else{
			$errorString = '';
			foreach($wasError as $file => $error){
				$errorIndv = explode(':', $error);
				switch ($errorIndv[0]){
					case 'uploadingFile':
						$errorString .= str_replace('{transferMode}', $errorIndv[1], str_replace('{fileLocation}', $file, $this->ErrorMessages['errorUpdateFile'])) . ' ';
					break;
					case 'openingFile':
						$errorString .= str_replace('{transferMode}', $errorIndv[1], str_replace('{fileLocation}', $file, $this->ErrorMessages['errorUpdateFile'])) . ' ';
					break;
				}
			}
            notification::StoreWarning($errorString);
			return false;		
		}
    }

    /* download a file
     * @Author Sam Mottley
     * @VAR fileServer relivent to root of ftp access
     * @VAR fileLocation relivent to web server 
	 * UNTESTED
     */

    public function retrieveFile($fileLocation, $fileServer=NULL, $transferMode = FTP_BINARY) {
		$wasError = '';
		if(!is_array($fileLocation)){
			$arrayInfo = array(array($fileLocation, $fileServer, $transferMode));
		}
		$i = 0;
		foreach($arrayInfo as $number => $singleData){
			//File pointer
			$filePointer = fopen($singleData[0], 'w');
	
			//Here we set the CURRENT directory
			$currentLocation = $this->currentDirectory();
	
			//set the new directory relivent to root
			$this->setCurrentDirectory('/'); //Set it to root
	
			if($retieveFile[$singleData[0]] = ftp_fget($this->storeConnection, $filePointer, $singleData[1], $singleData[2], 0)){
			}else{
				$wasError[$singleData[0]] = 'retrievingFile';
			}
	
			//Go back to the last directory
			$this->setCurrentDirectory($currentLocation);
		}
        if($wasError == ''){
			return true;
		}else{
			$errorString = '';
			foreach($wasError as $file => $error){
				switch ($file){
					case 'retrievingFile':
						$errorString .= str_replace('{file}', $file, $this->ErrorMessages['errorRetrieveFile']) . ' ' ;
					break;
				}
			}
			notification::StoreWarning($errorString);
			return false;		
		}
    }

}
$sam = array(array('test.php', 'test.php', '0644', FTP_BINARY), array('test2.php', 'test2.php', '0644', FTP_BINARY));
?>