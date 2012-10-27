<?php
/**
 *EAMPLE OF USE
 *You define the class in your controller file
 *	var $rocketSecuritySettings = array('defendAgainst'=>array('post'=>'sessionHiJacking,xss,ldap,xpath,xml,integerOverflows,sql,formatStringErrors,formatStringErrors',
 *														 'get'=>'sessionHiJacking,xss,ldap,xpath,xml,integerOverflows,sql,formatStringErrors,formatStringErrors',
 * 														 'sessions'=>'sessionHiJacking,xss,ldap,xpath,xml,integerOverflows,sql,formatStringErrors,formatStringErrors',
 *														 'header'=>'sessionHiJacking,xss,ldap,xpath,xml,integerOverflows,sql,formatStringErrors,formatStringErrors'), 
 *										'dontCheck'=>array('post'=>'pageUpdate:xss,sql, pageSideUpdate:xss,sql',//dont check post['pageUpdate'] and post['pageSideUpdate'] may content html / javascript and sql commands that are not attacks but legit
 *														 'get'=>'',//check ALL get varables 
 *														 'sessions'=>'customCode',//Dont check session['customCode']
 *														 'header'=>''),//check all headers
 * 										'securityLevel'=>2,
 *										'emailForReport'=>'sammottley@gmail.com',
 *										'warningAfter'=>5,
 *										'banAfter'=>10,
 *										'banFor'=>10);
 */
/**
 *This class will add an extra layer of secuirty to your application.
 */
class rocketSecurity extends rocketpack{
	
	/**
	 *Below are a list of 100% attacks
	 *Tags can be added and they are
	 *{text}, {symbol}, {number}
	 '||(elt(-{number}+{number},bin({number}),ord({number}),hex(char({number}))))
	 */
	static $sqlAttacks = array("'||(elt({symbol}{number}{symbol}{number},bin({number}),ord({number}),hex(char({number}))))","||{number}","'||'{number}","(||{number})","' OR {number}={number}-- ","OR {number}={number}","' OR '{number}'='{number}","; OR '{number}'='{number}'","%22+or+isnull%281%2F0%29+%2F*","%27+OR+%277659%27%3D%277659","%22+or+isnull%281%2F0%29+%2F*","%27+--+","' or {number}={number}--","\" or {number}={number}--","' or {number}={number} /*","or {number}={number}--","' or '{text}'='{text}","\" or \"{text}\"=\"{text}","') or ('{text}'='{text}","{text}' OR '","'%20SELECT%20*%20FROM%20INFORMATION_SCHEMA.TABLES--",") UNION SELECT%20*%20FROM%20INFORMATION_SCHEMA.TABLES;","' having {number}={number}--","' having {number}={number}--","' group by userid having {number}={number}--","' SELECT {text} FROM syscolumns WHERE {text} = (SELECT {text} FROM sysobjects WHERE {text} = {text}')--","' or {number} in (select @@version)--","' union all select @@version--","' OR 'unusual' = 'unusual'","' OR '{text}' = '{text}'+'{text}'","' OR '{text}' = N'{text}'","' OR '{text}' like '{text}%'","' OR {number} > {number}","' OR '{text}' > '{text}'","' OR '{text}' in ('{text}')","' OR {number} BETWEEN {number} and {number}","' or {text} like char({number});","' union select * from {text} where {text} = char({number},{number},{number},{number});","' union select ","{text}:*/={number}--","UNI/**/ON SEL/**/ECT","'; EXECUTE IMMEDIATE 'SEL' || 'ECT US' || 'ER'","'; EXEC ('SEL' + 'ECT US' + 'ER')","'/**/OR/**/{number}/**/=/**/{number}","' or {number}/*","+or+isnull%281%2F0%29+%2F*","%27+OR+%277659%27%3D%277659","%22+or+isnull%281%2F0%29+%2F*","%27+--+&{text}=","'; begin declare @var varchar({number}) set @var=':' select @var=@var+'+{text}+'/'+{text}+' ' from {text} where {text} > "," @var select @var as var into temp end --","' and {number} in (select var from temp)--","' union select {number},load_file('/etc/passwd'),{number},{number},{number};","{number};(load_file(char({number},{number},{number},{number},{number},{number},{number},{number},{number},{number},{number}))),{number},{number},{number};","' and {number}=( if((load_file(char({number},{number},{number},{number},{number}))<>char({number},{number})),{number},{number}));","'; exec master..xp_cmdshell 'ping {number}.{number}.{number}.{number}'--","CREATE USER {text} IDENTIFIED BY '{text}'","CREATE USER {text} IDENTIFIED BY {text} TEMPORARY TABLESPACE {text} DEFAULT TABLESPACE {text};","' ; drop {text} temp --","exec sp_addlogin '{text}' , '{text}'","exec sp_addsrvrolemember '{text}' , 'sysadmin'","INSERT INTO mysql.{text} ({text}, {text}, {text}) VALUES ('{text}', '{text}', PASSWORD('{text}'))","GRANT CONNECT TO {text}; GRANT RESOURCE TO {text};","INSERT INTO {text}({text}, {text}, {text}) VALUES( char(0x70) + char(0x65) + char(0x74) + char(0x65) + char(0x72) + char(0x70) + char(0x65) + char(0x74) + char(0x65) + char(0x72),char(0x64)", "order by {number}");
	static $xssAttacks =  array('>','"','\'','<script>','<','%3C','&lt','&lt;','&LT','&LT;','&#60','&#060','&#0060','&#00060','&#000060','&#0000060','&#60;','&#060;','&#0060;','&#00060;','&#000060;','&#0000060;','&#x3c','&#x03c','&#x003c','&#x0003c','&#x00003c','&#x000003c','&#x3c;','&#x03c;','&#x003c;','&#x0003c;','&#x00003c;','&#x000003c;','&#X3c','&#X03c','&#X003c','&#X0003c','&#X00003c','&#X000003c','&#X3c;','&#X03c;','&#X003c;','&#X0003c;','&#X00003c;','&#X000003c;','&#x3C','&#x03C','&#x003C','&#x0003C','&#x00003C','&#x000003C','&#x3C;','&#x03C;','&#x003C;','&#x0003C;','&#x00003C;','&#x000003C;','&#X3C','&#X03C','&#X003C','&#X0003C','&#X00003C','&#X000003C','&#X3C;','&#X03C;','&#X003C;','&#X0003C;','&#X00003C;','&#X000003C;','\x3c','\x3C','\u003c','\u003C');
	static $ldapAttacks = array('|','!','(',')','%28','%29','&','%26','%21','%7C','*|','%2A%7C','*(|(mail=*))','%2A%28%7C%28mail%3D%2A%29%29','*(|(objectclass=*))','%2A%28%7C%28objectclass%3D%2A%29%29','*()|%26\'','admin*','admin*)((|userPassword=*)','*)(uid=*))(|(uid=*');
	static $xPathAttacks = array("'+or+'1'='1","'+or+''='","x'+or+1=1+or+'x'='y","/","//","//*","*/*","@*","count(/child::node())","x'+or+name()='username'+or+'x'='y",);
	static $xmlInjection = array("<![CDATA[<script>var n=0;while(true){n++;}</script>]]>","<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><foo><![CDATA[<]]>SCRIPT<![CDATA[>]]>alert('gotcha');<![CDATA[<]]>/SCRIPT<![CDATA[>]]></foo>","<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><foo><![CDATA[' or 1=1 or ''=']]></foof>","<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><!DOCTYPE foo [<!ELEMENT foo ANY><!ENTITY xxe SYSTEM \"file://c:/boot.ini\">]><foo>&xee;</foo>","<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><!DOCTYPE foo [<!ELEMENT foo ANY><!ENTITY xxe SYSTEM \"file:///etc/passwd\">]><foo>&xee;</foo>","<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><!DOCTYPE foo [<!ELEMENT foo ANY><!ENTITY xxe SYSTEM \"file:///etc/shadow\">]><foo>&xee;</foo>","<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?><!DOCTYPE foo [<!ELEMENT foo ANY><!ENTITY xxe SYSTEM \"file:///dev/random\">]><foo>&xee;</foo>");
	static $integerOverflowsAttacks = array('-1','0','0x100','0x1000','0x3fffffff','0x7ffffffe','0x7fffffff','0x80000000','0xfffffffe','0xffffffff','0x10000','0x100000');
	static $formatStringErrors = array('%s%p%x%d','.1024d','%.2049d','%p%p%p%p','%x%x%x%x','%d%d%d%d','%s%s%s%s','%99999999999s','%08x','%%20d','%%20n','%%20x','%%20s','%s%s%s%s%s%s%s%s%s%s','%p%p%p%p%p%p%p%p%p%p','%#0123456x%08x%x%s%p%d%n%o%u%c%h%l%q%j%z%Z%t%i%e%g%f%a%C%S%08x%%','%s x 129','%x x 257');
	static $bufferOverflowsAttacks = array('A x 5','A x 17','A x 33','A x 65','A x 129','A x 257','A x 513','A x 1024','A x 2049','A x 4097','A x 8193','A x 12288');
	static $sessionHiJacking;
	
	/**
	 *This will search the common attack paramiters
	 *@Return This will return an array of found vunrabilities 
	 */
	public function checkStandardAttacks($data, $param, $defendAganst, $dontCheck){
		$Issues[$param] = array();
		
		switch($param){
			case 'get':
				//Here we check for sql attacks
				foreach(self::$sqlAttacks as $attack){
					$noSeach = 0;
					//here we search for the number and text tag then search using preg match 
					$searchTagNumber = strpos($attack, '{number}');
					$searchTagText = strpos($attack, '{text}');
					$searchTagSymbol = strpos($attack, '{symbol}');
					if(($searchTagNumber !== false) || ($searchTagText !== false) || ($searchTagSymbol !== false)){
						$searchPatternStarter = preg_quote($attack, '/');
						$searchPattern = $searchPatternStarter;
						$searchPattern = str_replace('\{text\}', '\w*', $searchPattern);
						$searchPattern = str_replace('\{number\}', '\d*', $searchPattern);
						$searchPattern = str_replace('\{symbol\}', '\W*', $searchPattern);
						$search = preg_match("/".$searchPattern."\s*\w*/", $data);
						if($search){
							$Issues[$param]['sqlAttack'][] = $attack;
						}
					}else{
						$noSeach = 1;//If no tag found then there was no search 	
					}
					
					//No tags found so run a quick simple search
					if($noSeach == 1){
						$search = strpos($data, $attack);
						if($search !== false){
							$Issues[$param]['sqlAttack'][] = $attack;
						}
					}
				}
				//Here we check for XSS attacks
				//Here we check for LDAP attacks
				//Here we check for XPath attacks
				//Here we check for XML attacks
				//Here we check for interger over flow attacks
				//Here we check for format string error attacks
				//Here we check for buffer over flow attacks
				//Here we check for session hi-jacking attacks
			break;
				
		}
		return $Issues;
	}

	
	/**
	 *The below function will clean any xss attck attempts
	 *@Return clean version of what was input
	 */
	public function cleanXss($issue , $htmlEntities = true, $stripTags = true, $scan = true){
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
		if(!empty($issue) && is_string($issue)) {
			return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $issue);
		}
	}
	
	/**
	 *The below will clean up any LDAP attacks
	 */
	public function cleanLdap($issue){
		//here we scan through all possable ways to inject LDAP
		foreach(self::$ldapAttacks as $danger){
			if(strpos($danger, $clean) === true){
				$clean 	= str_replace($danger, '', $clean);
			}
		}
		return $clean;
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
	public function rocketSecurityBrain($settings){
		foreach($settings['defendAgainst'] as $protcal => $setting){
			switch($protcal){
				case 'get':
					foreach($_GET as $name => $value){
						$result = $this->checkStandardAttacks($value, 'get', $settings['defendAgainst'], $settings['dontCheck']);
						print_r($result);
						echo '<br/><br/><br/>';
					}
					
				break;
				
			}
		}
		
	}
	
}


?>