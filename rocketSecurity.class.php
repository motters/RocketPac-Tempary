<?php

class rocketSecurity extends rocketpack {
	
	/**
	 *Below are a list of very common attacks 
	 */
	static $sqlAttacks = array("'||(elt(-3+5,bin(15),ord(10),hex(char(45))))","||6","'||'6","(||6)","' OR 1=1-- ","OR 1=1","' OR '1'='1","; OR '1'='1'","%22+or+isnull%281%2F0%29+%2F*","%27+OR+%277659%27%3D%277659","%22+or+isnull%281%2F0%29+%2F*","%27+--+","' or 1=1--","\" or 1=1--","' or 1=1 /*","or 1=1--","' or 'a'='a","\" or \"a\"=\"a","') or ('a'='a","Admin' OR '","'%20SELECT%20*%20FROM%20INFORMATION_SCHEMA.TABLES--",") UNION SELECT%20*%20FROM%20INFORMATION_SCHEMA.TABLES;","' having 1=1--","' having 1=1--","' group by userid having 1=1--","' SELECT name FROM syscolumns WHERE id = (SELECT id FROM sysobjects WHERE name = tablename')--","' or 1 in (select @@version)--","' union all select @@version--","' OR 'unusual' = 'unusual'","' OR 'something' = 'some'+'thing'","' OR 'text' = N'text'","' OR 'something' like 'some%'","' OR 2 > 1","' OR 'text' > 't'","' OR 'whatever' in ('whatever')","' OR 2 BETWEEN 1 and 3","' or username like char(37);","' union select * from users where login = char(114,111,111,116);","' union select ","Password:*/=1--","UNI/**/ON SEL/**/ECT","'; EXECUTE IMMEDIATE 'SEL' || 'ECT US' || 'ER'","'; EXEC ('SEL' + 'ECT US' + 'ER')","'/**/OR/**/1/**/=/**/1","' or 1/*","+or+isnull%281%2F0%29+%2F*","%27+OR+%277659%27%3D%277659","%22+or+isnull%281%2F0%29+%2F*","%27+--+&password=","'; begin declare @var varchar(8000) set @var=':' select @var=@var+'+login+'/'+password+' ' from users where login > "," @var select @var as var into temp end --","' and 1 in (select var from temp)--","' union select 1,load_file('/etc/passwd'),1,1,1;","1;(load_file(char(47,101,116,99,47,112,97,115,115,119,100))),1,1,1;","' and 1=( if((load_file(char(110,46,101,120,116))<>char(39,39)),1,0));","'; exec master..xp_cmdshell 'ping 10.10.1.2'--","CREATE USER name IDENTIFIED BY 'pass123'","CREATE USER name IDENTIFIED BY pass123 TEMPORARY TABLESPACE temp DEFAULT TABLESPACE users;","' ; drop table temp --","exec sp_addlogin 'name' , 'password'","exec sp_addsrvrolemember 'name' , 'sysadmin'","INSERT INTO mysql.user (user, host, password) VALUES ('name', 'localhost', PASSWORD('pass123'))","GRANT CONNECT TO name; GRANT RESOURCE TO name;","INSERT INTO Users(Login, Password, Level) VALUES( char(0x70) + char(0x65) + char(0x74) + char(0x65) + char(0x72) + char(0x70) + char(0x65) + char(0x74) + char(0x65) + char(0x72),char(0x64)");
	static $xssAttacks =  array('>','"','\'','<script>','<','%3C','&lt','&lt;','&LT','&LT;','&#60','&#060','&#0060','&#00060','&#000060','&#0000060','&#60;','&#060;','&#0060;','&#00060;','&#000060;','&#0000060;','&#x3c','&#x03c','&#x003c','&#x0003c','&#x00003c','&#x000003c','&#x3c;','&#x03c;','&#x003c;','&#x0003c;','&#x00003c;','&#x000003c;','&#X3c','&#X03c','&#X003c','&#X0003c','&#X00003c','&#X000003c','&#X3c;','&#X03c;','&#X003c;','&#X0003c;','&#X00003c;','&#X000003c;','&#x3C','&#x03C','&#x003C','&#x0003C','&#x00003C','&#x000003C','&#x3C;','&#x03C;','&#x003C;','&#x0003C;','&#x00003C;','&#x000003C;','&#X3C','&#X03C','&#X003C','&#X0003C','&#X00003C','&#X000003C','&#X3C;','&#X03C;','&#X003C;','&#X0003C;','&#X00003C;','&#X000003C;','\x3c','\x3C','\u003c','\u003C');
	static $ldapAttacks = array('|','!','(',')','%28','%29','&','%26','%21','%7C','*|','%2A%7C','*(|(mail=*))','%2A%28%7C%28mail%3D%2A%29%29','*(|(objectclass=*))','%2A%28%7C%28objectclass%3D%2A%29%29','*()|%26\'','admin*','admin*)((|userPassword=*)','*)(uid=*))(|(uid=*');
	static $xPathAttacks = array("'+or+'1'='1","'+or+''='","x'+or+1=1+or+'x'='y","/","//","//*","*/*","@*","count(/child::node())","x'+or+name()='username'+or+'x'='y",);
	static $xmlInjection = array("<![CDATA[<script>var n=0;while(true){n++;}</script>]]>","<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><foo><![CDATA[<]]>SCRIPT<![CDATA[>]]>alert('gotcha');<![CDATA[<]]>/SCRIPT<![CDATA[>]]></foo>","<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><foo><![CDATA[' or 1=1 or ''=']]></foof>","<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><!DOCTYPE foo [<!ELEMENT foo ANY><!ENTITY xxe SYSTEM \"file://c:/boot.ini\">]><foo>&xee;</foo>","<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><!DOCTYPE foo [<!ELEMENT foo ANY><!ENTITY xxe SYSTEM \"file:///etc/passwd\">]><foo>&xee;</foo>","<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><!DOCTYPE foo [<!ELEMENT foo ANY><!ENTITY xxe SYSTEM \"file:///etc/shadow\">]><foo>&xee;</foo>","<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><!DOCTYPE foo [<!ELEMENT foo ANY><!ENTITY xxe SYSTEM \"file:///dev/random\">]><foo>&xee;</foo>");
	static $integerOverflowsAttacks = array('-1','0','0x100','0x1000','0x3fffffff','0x7ffffffe','0x7fffffff','0x80000000','0xfffffffe','0xffffffff','0x10000','0x100000');
	static $formatStringErrors = array('%s%p%x%d','.1024d','%.2049d','%p%p%p%p','%x%x%x%x','%d%d%d%d','%s%s%s%s','%99999999999s','%08x','%%20d','%%20n','%%20x','%%20s','%s%s%s%s%s%s%s%s%s%s','%p%p%p%p%p%p%p%p%p%p','%#0123456x%08x%x%s%p%d%n%o%u%c%h%l%q%j%z%Z%t%i%e%g%f%a%C%S%08x%%','%s x 129','%x x 257');
	static $bufferOverFlow = array('A x 5','A x 17','A x 33','A x 65','A x 129','A x 257','A x 513','A x 1024','A x 2049','A x 4097','A x 8193','A x 12288');
	static $sessionHiJacking;
	
	/**
	 *This will search the common attack paramiters
	 *@Return This will return an array of found vunrabilities 
	 */
	public function checkStandardAttacks(){
		
		
	}

	
	/**
	 *The below function will clean any xss attck attempts
	 *@Return clean version of what was input
	 */
	public function cleanXss($issue , $htmlEntities = true, $stripTags = true, $urlencode = true, $scan = true){
		$clean = $issue; 
		//Run through html entities and str_replace stops double encoding and utf-7 
		if($htmlEntities){
			$clean = str_replace('+ADw-', '', str_replace('%253c', '', htmlentities($clean, ENT_COMPAT | ENT_HTML401, 'UTF-8')));	
		}
		//run through stip tags
		if($stripTags == true){
			$clean = strip_tags($clean);
		}else if($stripTags == false){
			//do nothing
		}else{
			$clean = strip_tags($clean, $stripTags);	
		}
		//This will stop US-ASCII encoding xss attacks
		if($urlencode != false){
			 for($i = 0; $i < strlen($clean); $i++){
				if(strpos("/:@&%=?.#", $clean[$i]) === false){
					$cleanPart .= urlencode($clean[$i]);
				}else{
					$cleanPart .= $clean[$i];
				}
			 }
			 $clean = $cleanPart; 	
		}
		
		//here we scan through all possable ways to get through the filter andother DOUBLE CHECK
		foreach(self::$xssAttacks as $danger){
			if(strpos($danger, $clean) === true){
				$clean 	= str_replace($danger, '', $clean);
			}
		}
		
		return $clean;
	}
	
	/**
	 *The below will clean up any sql attacks
	 */
	public function cleanSQL($issue){
		if(is_array($issue)){
        	return array_map(__METHOD__, $issue);
		}
		
		if(!empty($issue) && is_string($issue)) {
			return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $issue);
		}
	}
	
	/**
	 *The below will clean up any LDAP attacks
	 */
	public function cleanLdap(){
		
		
	}
	
	/**
	 *The below will clean up any XML attacks
	 */
	public function cleanXml(){
		
		
	}
	
	/**
	 *The below will clean up any Integer overflow attacks
	 */
	public function cleanIntegerOverflows(){
		
		
	}
	
	/**
	 *The below will clean up any forat string errors attacks
	 */
	public function cleanFormatStringErrors(){
		
		
	}
	
	/**
	 *The below will clean up any XPath attacks
	 */
	public function cleanXPath(){
		
	}
	
	/**
	 *Check for any session hi-jacking attempts
	 */
	public function cleanSessionHiJacking(){
		//compare session set ip and current ip
		if((!isset($_SESSION['rpuip'])) && ($_SESSION['rpuip'] != $_SERVER["REMOTE_ADDR"])){
			$_SESSION['rpuid'] = null;
			return false;
		}else{
			return true;	
		}
		
	}
	
	/**
	 *The below will clean up any buffer over flow attempts
	 */
	public function cleanBufferOverFlow($issue){
		//clean any hex decimal
		$clean = preg_replace("![\][xX]([A-Fa-f0-9]{1,3})!", "",$issue);
		
		return $clean;
	}
	
	
	
	/**
	 *The below will load the relative function then an attack has been detected
	 */
	public function takeAction(){
		
		
	}
	
	
	/**
	 *The below is load function to search for attack, log attacks and get up safe varables to use in rocketpack
	 */	
	public function rocketSecurity(){
		
		
	}
	
}


?>