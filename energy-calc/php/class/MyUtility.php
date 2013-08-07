<?php
class MyUtility
{
	public  $v,$db,$e,$truncate,$rc;
	
	function __construct()
	{
		#echo "My Utility constructer";
		$this->v->exclude=array('ls.out','.DS_Store','.htaccess','.htaccess.sav','state-pdf','supp1','WS_FTP.LOG','.htaccess.old');	
		$v->sleep = 5;
		$v->start = 0;
		$v->limit = 999999999; //End of Rows
		$v->userAgent= 'Mozilla/5.0 (X11; U; Linux i686; it-IT; rv:1.9.0.2) Gecko/2008092313 Ubuntu/9.25 (jaunty) Firefox/3.8';//'Googlebot/2.1 (+http://www.google.com/bot.html)';
		$v->curltimeOut = 15;
		$this->v= $v;
		
		
				
		# Initialize error object
		$e=array();
		$this->e=$e;
		$this->e[]='error list';	
		
		// Set up database
		$db = ADONewConnection(DB_TYPE);
		$db->debug = false;
		$db->port=DB_PORT;
		$db->Connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$this->db = $db;

		
	}
	
	# Chnage Filter to an array so we can search for word AND pdf files
	protected function getFiles($path,$filter,$exclude=NULL)
	{
		if($exclude=='' || $exclude==NULL ) $exclude = $this->v->exclude;
		//var_dump($this->v->exclude);
	
		$it = new RecursiveDirectoryIterator($path);
		foreach(new RecursiveIteratorIterator($it) as $file):
				
					if	(!strstr($file,$exclude)):												
						
						//FILTER
						if($filter !='' || $filter!=NULL):
							if(in_array($this->getFileExtension($file->getFilename()),$filter)):
								$fileList[$file->getFileName()]= $file->getPathName(); //Get only the file you want...like all .txt files							
							endif;
						
						//NO FILTER Get all files
						else:
							$fileList[$file->getFileName()]= $file->getPathName();
						endif;

					endif;
				
		endforeach;

		return $fileList;

	}
	
	protected function getFiles2($path,$filter,$exclude=NULL)
	{
		
		if($exclude=='' || $exclude==NULL ) $exclude = $this->v->exclude;
		//var_dump($this->v->exclude);
	
		$it = new RecursiveDirectoryIterator($path);
		foreach(new RecursiveIteratorIterator($it) as $file):
				
					if	(!strstr($file,$exclude)):												
						
						//FILTER
						if($filter !='' || $filter!=NULL):
							if($this->getFileExtension($file->getFilename())==$filter):
								$fileList[$file->getFileName()]= $file->getPathName(); //Get only the file you want...like all .txt files							
							endif;
						
						//NO FILTER Get all files
						else:
							$fileList[$file->getFileName()]= $file->getPathName();
						endif;

					endif;
				
		endforeach;

		return $fileList;

	}
	
	
	
	protected function getFileExtension($filename)
	{
		$ext = explode('.',$filename,2); 
		return $ext[1]; 		
	}
	
	protected function removeFileExtension($filename)
	{
		return substr($filename, 0, (strlen ($filename)) - (strlen (strrchr($filename,'.'))));
	}
	
	

	
		
	protected function pdftotext($file,$saveTo=NULL)
	{
		//$ret = shell_exec('/opt/local/bin/pdftotext -layout -enc Latin1 '.$file.' -');
		$ret = shell_exec(PDFTOTEXT_LOC.' -layout -enc Latin1 '.$file.' -');
		
		
		switch($ret):
						case 127:
							die("Could not find pdftotext tool.");
							break;
						
						case  2:
							die("Could not find pdf file: ".$file);
							break;	
							
												
						case  3:
							die("Error opening an output file: ".$file);
							break;	
						
						case  4:
							die("Error related to PDF permissions: ".$file);
							break;	
						
						case  5:
							die("Other error. ".$file);
							break;	
						
						//Success!
						default:
							return trim(ereg_replace( ' +', ' ', $ret));
							break;
		endswitch;
							
	}
	
	
	protected function flattenArray($array)
	{
			foreach($array as $element):
					foreach($element as $key=>$value):
						$merged[]=$value;
					endforeach;			
			endforeach;
			
			return $merged;
	}
	
	protected function removeParentIndex($array)
	{
			foreach($array as $element):
					foreach($element as $key=>$value):
						$merged[]=$value;
					endforeach;			
			endforeach;
			
			return $merged;
	}
	
	
	protected function mergeTwoArrays_Simple($first, $second)
	{
		//Create Final Array of descriptions and urls
			$count= count($first); 
			for($i=0;$i<$count;$i++):
				$result[$first[$i]]=$second[$i];
			endfor;
			
			return $result;
	}
	
protected function searchArray($pattern,$array)
	{
			foreach($array as $value):
				// If there is a match, add it to the matches array
				if(preg_match($pattern,$value)) $matches[]=$value;
			endforeach;	
			
			return $matches;	
	}
	
	protected function deleteEmptyArrayValues($array)
	{		
			foreach ($array As $key =>$value) :
					if(trim($value)==NULL): 
						unset($array[$key]);
					endif;
			endforeach;
			
			$result = array_merge($array); 
			return $result;
			
	}
	
	protected function mergeTwoArrays($first,$firstName, $second, $secondName)
	{
				//Create Final Array of descriptions and urls
				$count= count($first); 
				
				for($i=0;$i<$count;$i++):
					$result[] =array($firstName=> $first[$i],$secondName=>$second[$i]);
				endfor;
				
				return $result;
	}
	
	
	
	protected function str2Array($data,$num2Remove=0)
	{
		
		$i=0;
		$data =trim($data);
		//$data =trim(htmlspecialchars($data,ENT_QUOTES));
		
		

		foreach(preg_split("/[\r\n]+/", $data) as $line)
			{			
				$line=trim($line);
				//split the line and delete unneccesary blank spacees
				if($line !='') $lines[]= trim(ereg_replace( ' +', ' ', $line));
				//if($line !='') $lines[]= ereg_replace( ' +', ' ', $line);
				
			}
					
			//use to remove field headers if necessary
			while($i < $num2Remove):
				array_shift($lines);
				$i++;
			endwhile;
	
			return $lines;

	}
	
	protected function cleanString($str)
	{
		
		$str = trim(htmlentities($str));
		$str = preg_replace('/\s\s+/',' ',$str);
		$str = preg_replace('/&[A-Z-a-z-0-9-\#]{2,7};/',' ',$str);	
		
		return $str;
	}
	
	protected function convertRegexToEnglishString($str)
	{
		$str = trim($str);
		$search = array('[\s]+','  ','/');
		$replace= ' ';
		$str = str_replace($search, $replace, $str);
		
		return $str;
	}
	
	protected function combineAllFieldHeaders($fldHeaders,$fldSubHeaders)
	{
		if($fldSubHeaders!=Null):
			foreach($fldHeaders as $value1):
					foreach($fldSubHeaders as $value2):
						$result[] = $value2.$value1; 
					endforeach;
			endforeach;
			
			# Add the matched string to the field headers array
			array_unshift($result,'extracted');
		endif;
		
		return $result;
		
	}
	
	
	protected function fuseMatchesFieldHeadersGlobals($globalFields,$fieldHeaders,$matches)
	{
			# FUSE FIELD HEADERS with the records extracted from the file---------------
				foreach($matches as $match):
					$count = count($fieldHeaders);
					$record = array();
					
					foreach($match as $key=>$value):
						# Iterate over fields of each match to create an associative record
						for($i=0;$i<$count;$i++):			
							$record[$fieldHeaders[$i]]= $match[$i];								
						endfor;
					endforeach;
					
					# Add Extra (Global) Fields NOT Extracted from Document
					foreach($globalFields as $key2 =>$value2) $record[$key2]=$value2;
					
					# add this record to the records array
					$records[] = $record;	
				endforeach;
				
				return $records;
	}

	protected function fuseDataArrayWithFieldHeaders($fieldHeaders,$data)
	{
			foreach($data as $key1 => $value1):
					
					foreach($value1 as $key2=>$value2):	
						$arrayValues[$fieldHeaders[$key2]]=$value2; //Add NAME, YRS, JAN,FEB...ANN headers as keys for each value
					endforeach;	
					
					$arrayIndex[]=$arrayValues; //Add arrayInner to the final array	
		
			endforeach;
				
			return $arrayIndex;
	}
	
	
	protected function cleanArray($lines)
	{
		foreach($lines as $key=>$value):
			$lines[$key]=htmlentities($value);
			$lines[$key]=trim(preg_replace('/[ ]+/',' ',$value));
			$lines[$key]=trim(preg_replace('/\&[A-Z-a-z-0-9-#]{3,5};/',' ',$value));		
		endforeach;
		
		return $lines;
	}
	
	
	public function googleGeoCode($address,$format='csv')
		{
			
			//Set up our variables
			$longitude = "";
			$latitude = "";
			$precision = "";
			 
			 $data = array(
	            'q' => $address,
	            'key' => 'ABQIAAAA_bee_kdGMO5k59USHpEZfxR9h38nUCQXltaiE0_0roxiTJG5lxTdhohGuovTtAiT8DZxpCds43QrRA',
	            'sensor' => 'true',
	            'output' => $format,
	            'oe' => 'utf8'
	        );
			
		
			$ch = curl_init();
			
			$get = 'http://maps.google.com/maps/geo?' . http_build_query($data);
			curl_setopt($ch, CURLOPT_URL, $get);
			curl_setopt($ch, CURLOPT_HEADER,0);
			curl_setopt($ch, CURLOPT_USERAGENT,CURL_USERAGENT);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			 
			$data = curl_exec($ch);
			sleep(2); //Stagger Request to keep Google from banning you
			curl_close($ch);

			//Check our Response code to ensure success
			if (substr($data,0,3) == "200"):				
				$data = explode(",",$data);
				return $data;			
			elseif (substr($data,0,3) == "620"):
				die('too fast test. got a 620');
				
			else:
					$this->e->latlon[]= "Error in geocoding! Http error ".substr($data,0,3); return NULL;
				
			endif;
			
			
			
			
		}
		
		
protected function curl_get_file_contents($url)
	{		
		
			$fh = fopen('/Users/robert/Desktop/curl.txt','w') or die($php_errormsg);
			$c = curl_init();
	        curl_setopt($c, CURLOPT_URL, $url);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			//curl_setopt($c,CURLOPT_AUTOREFERER,1);
	        curl_setopt($c,  CURLOPT_USERAGENT, $this->v->userAgent);
	        curl_setopt($c,CURLOPT_TIMEOUT,20); 
	       	curl_setopt($c, CURLOPT_VERBOSE, 1);
	       // curl_setopt($c,CURLOPT_FOLLOWLOCATION,1);
	        //curl_setopt($c,CURLOPT_COOKIESESSION,1);
	        //curl_setopt($c,CURLOPT_FRESH_CONNECT,1);
	        curl_setopt($c, CURLOPT_STDERR, $fh);
	        $contents = curl_exec($c);
	        curl_close($c);
	       fclose($fh) or die($php_errormsg);
			
	        

	        //echo  $this->v->userAgent.' '.$this->v->url;
	        if ($contents) return $contents;
	            else return FALSE;
	}
	
# Shared MYSQL ADODB QUERIES------------------------------------------------------------------------------------------	
	
	
	# Get nearest weather station to a given latitude and longitude
	protected function spatialProximitySearch($centerPointLat,$centerPointLon,$distance=200,$unit='m',$table='mshrLite',$select=NULL)
	{
		$result = array();
		# Use with mshrLite table
		$select_old = array('id','coopId', 'wbanName', 'climId', 'state', 'county', 'latitude', 'longitude');
		
		
		if(is_array($select)):
			$selectSQL = $this->arrayToSqlSELECTStr($select);
		elseif($select == '*'):	
			$selectSQL = $this->getSelectedFields($table,array('latitude','longitude'));
			
			$selectSQL = $this->arrayToSqlSELECTStr($selectSQL);
			
			
			
		endif;
		
		
		/*** distance unit ***/
		 switch ($unit):
		 /*** miles ***/
		 case 'm':
		    $unit = 3963;
		    break;
		 /*** nautical miles ***/
		 case 'n':
		    $unit = 3444;
		    break;
		 default:
		    /*** kilometers ***/
		    $unit = 6371;
		 endswitch;
		 
		 $this->db->SetFetchMode(ADODB_FETCH_ASSOC);
		 $rs = 		$this->db->Execute("$selectSQL , 
									
											( {$unit} * ACOS( COS( RADIANS({$centerPointLat}) ) * 
											COS( RADIANS( latitude ) ) * 
											COS( RADIANS(longitude ) - 
											RADIANS({$centerPointLon}) ) + 
											SIN( RADIANS({$centerPointLat}) ) * 
											SIN( RADIANS( latitude ) ) ) ) AS distance 
											
											FROM {$table}
										
											HAVING distance < {$distance}
								
											ORDER BY distance ASC
											Limit 1");
		
		while(!$rs->EOF): 
			$result[]=$rs->fields; 
			$rs->MoveNext(); 
		endwhile;
		
		return $result;
	}
	
	protected function getSelectedFields($table,$ignoreFields)
	{
		# Get all the fields in the Table
		$tableFields= $this->db->MetaColumnNames($table,$numericIndex=TRUE);

		# Ignore Certain Fields and Store the Rest in an array
		foreach($tableFields as $value):
		
			if(!in_array($value,$ignoreFields)):
				$fields[]=$value;
			endif;	
		endforeach;
			
		return $fields;
	}
	
	protected function arrayToSqlSELECTStr($array)
	{
		$sql ='SELECT ';
		$last = end($array);
		reset($array); 
		foreach ($array as $value):
			$sql .= $value;
			if($value != $last) $sql .=',';
		endforeach;
		
		return $sql;
			
	}
	
	# Get the first coopId that exists for clim84 dir(AK500026.txt) and clim60 dir (Clim_AK_01.pdf) and clim20 dir(500026.pdf), CCD data (in database)
	# Use the Directory iterator function
	protected function getMatchingFiles($result, $location = array('clim20,'))
	{
			
	}
	
	protected function truncateTable($table)
	{
		$this->db->Execute("TRUNCATE table $table");
	}
	
	protected function getTableComment($table)
	{
		$sql = 'SHOW CREATE TABLE `'.$t.'`;';
		$query = mysql_query($sql);
		$v = mysql_result($query, 0, 1);
		
		if($v):
			$p = strpos($v,"COMMENT=");
			if($p):
				return substr($v, $p + 8);
			endif;
		endif;
		
	//return 'Table description not found';

	}
	
	protected function file2String($filename)
	{
		// get contents of a file into a string
		$handle = fopen($filename, "r");
		$string = fread($handle, filesize($filename));
		fclose($handle);
		
		return trim(htmlspecialchars($string,ENT_COMPAT));
	}
	
	
	/* TODO
	 *  Steps for tomorrow before you start graphs with Google
	 *  Get nearest weather station (coopId)  
	 *  Loop through the found set of coopId and pull the first coopId file that exists (in Clim 20 and clim84)
	 *  Make (and store) a thumbnail (ImageMagick) of each of the first page of these files
	 *  Get text from  clim60-Climate Descriptions by State to make a climate abstract...preg_match everything after /Climate[\s]+of[\s]+[\b\w\b]/ to End of file
	 *  Get corresponding CCD data (add lat/lon to all CCD files)
	 *  Dump all the text into a user entered address table like name = 'Saana,Yemen' lat=xxx.xx, lon=xxx.xx
	 *  
	 */

	
	
	
	

	
	

	
/*		private function nodeHTMLScraper($html,$scrape)
		{
					
			
			//Load the remote Nasa Insolation Page into Dom and then traverse with Xpath
			//$html = $this->curl_get_file_contents($url);
			$doc = new DOMDocument();
			$doc->preserveWhiteSpace = false;
			$doc->loadHTML($html);
			$xpath = new DOMXpath($doc);
			$elements = $xpath->evaluate($scrape);
			
			
			//Traverse through the found node and get the data
			if (!is_null($elements)): 
			
	  			foreach ($elements as $element):
	   				//echo "<br/>[". $element->nodeName. "]";
	   				 $nodes = $element->childNodes;
	    				foreach ($nodes as $node):
	      					$result[]= $node->nodeValue;
	      					//echo "<br/>[". $element->nodeValue. "]";				
	    				endforeach;
	  			endforeach;
			endif;
			
			return $result;	
			
		}
		
		
		public function getNode($url,$scrape)
		{
			$html = $this->curl_get_file_contents($url);
			$dom = new DOMDocument();
			$dom->loadHTML($html);
			
			$xpath = new DOMXPath($dom);
			$hrefs = $xpath->evaluate($scrape);
			
			if(!is_null($hrefs)):
				for ($i = 0; $i < $hrefs->length; $i++):
					$href = $hrefs->item($i);
					$url = $href->getAttribute('href');
					$result[] =$url;
				endfor;
			endif;
			
			//Return a simple variable if 1 value is returned. Else return an array
			if(count($result)==1):
				return $result[0];
			else:
				return $result;	
			endif;
					
			
			return $result;
		}
		
	public function getAttribute($html,$scrape)
		{
			//$html = $this->curl_get_file_contents($url);
			$dom = new DOMDocument();
			$dom->loadHTML($html);
			
			$xpath = new DOMXPath($dom);
			$hrefs = $xpath->evaluate($scrape);
			
			if(!is_null($hrefs)):
				for ($i = 0; $i < $hrefs->length; $i++):
					$href = $hrefs->item($i);
					$url = $href->getAttribute('href');
					$result[] =$url;
				endfor;
			endif;
			
			//Return a simple variable if 1 value is returned. Else return an array
			if(count($result)==1):
				return $result[0];
			else:
				return $result;	
			endif;
					
			
			//return $result;
		}
		
			private function mergeTwoArrays_original($one,$two)
	{
				//Create Final Array of descriptions and urls
				$count= count($one); 
				
				for($i=0;$i<$count;$i++):
					$result =array('description'=>$one->titles[$i],'url'=>$two->hrefs[$i]);
				endfor;
				
				return $result;
	}
	

*/
	
	
	
	
}