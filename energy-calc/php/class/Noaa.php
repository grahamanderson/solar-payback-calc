<?php 
define('SITE_IN', 1); include('../php/defines.php');

class Noaa extends MyUtility
{
	 //Intitialize variable, database, error objects
	public  $v,$db;
	
	function __construct()
	{		
		#echo "Noaa constructer";
		parent::__construct();

	}


	
	//Get Mean Values for each Month
	public function processClim20($path,$limit=5,$truncate=FALSE)
	{
		$clim20=array();
		$count=0;
		
		// Truncate Table if $truncate is True
		if($truncate===TRUE) $this->truncateTable('clim20');
		
		
		$data= $this->getFiles($path);
		
		foreach ($data AS $key=>$value):
				
				//Get the file name
				$coopFile =  $this->removeFileExtension($key); 
				$coopId= $this->removeFileExtension($key); $coopId =  trim(substr($coopId,2));
				$coopFilePath =$value;
				

				//make them global objects for the class
				$this->v->coopId = $coopId;
				$this->v->coopFile= $coopFile;
				$this->v->coopFilePath= $coopFilePath;
							
				//Convert the pdf to text with pdftotext command line utility
				$txt= $this->pdftotext($value);
				
				

			
				/*
				 * Get TempMean for the year- Jan-Dec
				 * If $matchItem =-1 gets the last item on the line 
				 * $total gets the $matchItem for  N lines BELOW the original match) 
				 * 			 
				 * REGULAR EXPRESSION EXPLANATION
				 * Following the string, Temperatur ( F), 
				 * Look For A Capitalized word of 3 letters,
				 * followed by a space, and the space NOT followed by a letter
				 */ 
				 
							
				//TEMPERATURE Jan-Dec-Ann------------------------------------------------------------
				$selectedText= $this->stringBetween($txt, 	$start='Temperature ( F)', 
															$end='+ Also occurred on an earlier date(s)');

				if($selectedText!=null) :

					//Convert the selected text into an array delimited by new lines
					$lines =  $this->convert2Array($selectedText,$beginAt=0);
					
					
					//Trim each line (Did not do for clim84 method)
					foreach($lines as $key => $value):$lines[$key]= trim($value); endforeach;
					
					
					//Looking for a capitalized word of 3 letter, followed by 1 space, space NOT followed by letters
					$pattern='\b^[A-Z]{1}[a-z]{2}\b[\s]{1}[^A-Za-z]';
					$result =  $this->getMean(	$lines,
												'/'.$pattern.'/',
												$matchLineNumber=1,
												$matchItem=4,
												$total=13);
					
					//die(var_dump($result));
					//Build the MYSQL INSERT Array to pass to ADODB
					$insertSQL = $this->buildClim20InsertStatement($result,'temp');	
							
					//Insert into the database
					$this->insert($table='clim20',$insertSQL);
				else:
					echo'Could not get the Temperature text for '. $this->v->coopFilePath;
					$this->e[]='Could not get the Temperature text for '. $this->v->coopFilePath;
				endif;
				
			

				//PRECIPITATION Values Jan-Dec-Ann//------------------------------------------------------
				
				//Get all the text between a selected range
				$selectedText= $this->stringBetween($txt, 	$start='Precipitation (inches)', 
															$end='+ Also occurred on an earlier date(s)');
				
				
				if($selectedText!=null):
					
															
					//Convert the text into an array delimited by new lines
					$lines =  $this->convert2Array($selectedText,$beginAt=0);
					
					//Trim each line (Did not do for clim84 method)
					foreach($lines as $key => $value):$lines[$key]= trim($value); endforeach;
					
					
					$pattern='(?<!Precipitation (inches))\b^[A-Z]{1}[a-z]{2}\b[\s]{1}[^A-Za-z]';
					$result =  $this->getMean(	$lines,
												'/'.$pattern.'/',
												$matchLineNumber=1,
												$matchItem=2,
												$total=13);
					
																
					//Build the MYSQL INSERT Array to pass to ADODB
					$insertSQL = $this->buildClim20InsertStatement($result,'precip');							
	
					
					//Insert into the database
					$this->insert($table='clim20',$insertSQL);
				else:
					echo'Could not get the Precipitation text for '. $this->v->coopFilePath;
					$this->e[]='Could not get the Precipitation text for '. $this->v->coopFilePath;
				endif;
	
			
				
				//SNOW Jan-Dec//------------------------------------------------------
								
				//Get all the text between a selected range
				$selectedText= $this->stringBetween($txt, 	$start='Snow (inches)', 
															$end='+ Also occurred on an earlier date(s)');
				
				
				if($selectedText!=null):
					
					//Convert the text into an array delimited by new lines
					$lines =  $this->convert2Array($selectedText,$beginAt=0);
					
					
					//Trim each line (Did not do for clim84 method)
					foreach($lines as $key => $value):$lines[$key]= trim($value); endforeach;
					
					$pattern='\b^[A-Z]{1}[a-z]{2}\b[\s]{1}[^A-Za-z]';
					$result =  $this->getMean(	$lines,
												'/'.$pattern.'/',
												$matchLineNumber=1,
												$matchItem=2,
												$total=13);
					
	
					//Build the MYSQL INSERT Array to pass to ADODB
					$insertSQL = $this->buildClim20InsertStatement($result,'snow');	
							
	
					//Insert into the database
					$this->insert($table='clim20',$insertSQL);
				
				else:
					echo 'Could not get the Snow text for '. $this->v->coopFilePath;
					$this->e[]='Could not get the Snow text for '. $this->v->coopFilePath;
				endif;
				
				//Put a iteration limit on the function for testing
				if($count>=$limit-1):
					break;
				endif;	
				
				$count++;
		endforeach;		
		
		//output any errors
		var_dump($this->e);
		die("I'm done");
	}
	
	
	
	
	//Get Mean Values for each Month for Clim84 Data from NOAA
	public function processClim84($path,$limit=5,$truncate=FALSE)
	{
		$clim84=array();
		$count=0;
		
		// Truncate Table if $truncate is True
		if($truncate===TRUE) $this->truncateTable('clim84');
		
		//Get all the files names and paths and load them into an array for parsing
		$data= $this->getFiles($path);
		//var_dump($data);
		
		foreach ($data AS $key=>$value):
		
				//Get the file name
				$coopFile =  $this->removeFileExtension($key); 
				$coopId= $this->removeFileExtension($key); $coopId =  trim(substr($coopId,2));
				$coopFilePath =$value;
				
				//Extract the CoopId from the file: AL75934 => 75934
				
				
				//die("$coopFile and $coopId and $coopFilePath");
				//make them global objects for the class
				$this->v->coopId = $coopId;
				$this->v->coopFile= $coopFile;
				$this->v->coopFilePath= $coopFilePath;
	
				// get contents of a file into a string
				$filename = $value;
				$handle = fopen($filename, "r");
				$txt = fread($handle, filesize($filename));
				fclose($handle);
		
				
				/*
				 * Get TempMean for the year- Jan-Dec
				 * If $matchItem =-1 gets the last item on the line 
				 * Get the string (with stringBetween) you want to run the regular expression on
				 * 			 
				 * REGULAR EXPRESSION EXPLANATION: \b^[A-Z]{3}\b[\s]{2}[^A-Za-z]
				 * 
				 * Look For An ALL caps word of 3 letters at the beginning of the line
				 * followed by a space, and the space NOT followed by a letter
				 */ 
				 
				
				
				//TEMPERATURE Jan-Dec-Ann------------------------------------------------------------
				$selectedText= $this->stringBetween($txt, 	$start='Average Temperature (deg F [tenths deg F for monthly])', 
															$end='Heating Degree Days');
				

				
				if($selectedText!=null): 

					
					//Convert the selected text into an array delimited by new lines
					$lines =  $this->convert2Array($selectedText,$beginAt=0);
					
					
					//Trim each line (Did not do for clim84 method)
					foreach($lines as $key => $value):$lines[$key]= trim($value); endforeach;
					
					
					//Looking for a capitalized word of 3 letters that IS NOT 'DAY', followed by 1 space, space NOT followed by letters
					$pattern='(?!DAY\b)\b^[A-Z]{3}\b[\s]{1}[^A-Za-z]';
					$result =  $this->getMean(	$lines,
												'/'.$pattern.'/',
												$matchLineNumber=1,
												$matchItem=-1,
												$total=12);
					
					
					//Build the MYSQL INSERT Array to pass to ADODB
					$insertSQL = $this->buildClim84InsertStatement($result,'temp');	
							
					//Insert into the database
					$this->insert($table='clim84',$insertSQL);
				else:
					echo "Could not get the Average Temperature text for <a href=\"{$this->v->coopFilePath}\">{$this->v->coopFilePath}</a>";
					$this->e[]="Could not get the Average Temperature text for <a href=\"{$this->v->coopFilePath}\">{$this->v->coopFilePath}</a>";
			endif;					
				
											
				
				
				
			//PRECIPITATION Jan-Dec-Ann------------------------------------------------------------
			$selectedText= $this->stringBetween($txt, 	$start='Total  Precipitation (Hundreths of an Inch)', 
														$end='EOF');
						
			if($selectedText!=null):
						
				//Convert the selected text into an array delimited by new lines
				$lines =  $this->convert2Array($selectedText,$beginAt=0);
				
				
				//Trim each line (Did not do for clim84 method)
				foreach($lines as $key => $value):$lines[$key]= trim($value); endforeach;
				
				
				//Looking for a capitalized word of 3 letter that IS NOT 'DAY', followed by 1 space, space NOT followed by letters
				$pattern='(?!DAY\b)\b^[A-Z]{3}\b[\s]{1}[^A-Za-z]';
				$result =  $this->getMean(	$lines,
											'/'.$pattern.'/',
											$matchLineNumber=1,
											$matchItem=-1,
											$total=12);
				
				//die(var_dump($result));
											
				//Build the MYSQL INSERT Array to pass to ADODB
				$insertSQL = $this->buildClim84InsertStatement($result,'precip');	
						
				//Insert into the database
				$this->insert($table='clim84',$insertSQL);
			
			else:
				echo('Could not get the Average Precipitation text for '. $this->v->coopFile);
				$this->e[]='Could not get the Average Precipitation text for '. $this->v->coopFile;	
			endif;
			
				
				//Put a iteration limit on the function for testing
				if($count>=$limit-1):
					break;
				endif;	
				
				$count++;
		endforeach;		
		
		//output any errors
		var_dump($this->e);
		die("I'm done");	
	}
	
	
	//Get Mean Values for each Month
	public function processCCD($path,$limit,$truncate=TRUE)
	{
		//die();
		//die("limit is: ".$limit);
		$clim20=array();
		$count=0;
		
		
		$data= $this->getFiles2($path,$filter='txt');
		
		
		
		foreach ($data AS $key=>$value):
				
				//die(var_dump($data));
				
				//Get the file name
				$coopFile =  $this->removeFileExtension($key); 
				$coopId=-1;
				$coopFilePath =$value;
				
				//make them global objects for the whole class
				$this->v->coopId = $coopId; $this->v->coopFile= $coopFile; $this->v->coopFilePath= $coopFilePath;
							
				
				 #-----------------------------------------------------------------------------
				 # NORMAL DAILY MEAN TEMPERATURE : DEGREES F from page 109 to 118 Jan-Dec-Ann
				 #-----------------------------------------------------------------------------
				
				$result =	$this->getCCDdata(	$coopFilePath,
												$pageStart=109,$pageEnd=118,
												$table='ccdTempNormal',
												$ignoreTableFields=array('id','latitude','longitude'),
												$comment='NORMAL DAILY MEAN TEMPERATURE : DEGREES F',
												$dataPattern='^[A-Z\.\(\) ,-\/]+',
												$excludeWordsFromPattern=	array('NORMALS','NORMAL','DATA','DR','WIND'),
												$insertLatLon=TRUE
												);	
				
				
				
				
				
				#Look over the outer index and inner associative array and insert them into the database							
				$this->mysqlInsertCCD($result,$table,$comment,$limit,$truncate);
				
				# add Latitude and Longitude to newly inserted DB records
				$result= $this->addLatLonToCCD($field='name',$table='ccdSnowfallNormal',$limit);
				
				
				
				
				
				
				 #------------------------------------------------------------------------------
				 # SNOWFALL (INCLUDING ICE PELLETS) - AVERAGE TOTAL IN INCHES
				 #------------------------------------------------------------------------------
				
				
				$result =	$this->getCCDdata(	$coopFilePath,
												$pageStart=45,$pageEnd=54,
												$table='ccdSnowfallNormal',
												$ignoreTableFields=array('id','latitude','longitude'),												
												$comment='SNOWFALL (INCLUDING ICE PELLETS) - AVERAGE TOTAL IN INCHES',
												$dataPattern=			'^[A-Z\.\(\) ,-\/\'&#;]+',
												$excludeWords=	array('DATA','SNOWFALL','THE','IT'),
												$insertLatLon=TRUE
												);
					
				
				//Look over the outer index and inner associative array and insert them into the database							
				$this->mysqlInsertCCD($result,$table,$comment,$limit,$truncate);
				
				# add Latitude and Longitude to newly inserted DB records
				$result= $this->addLatLonToCCD($field='name',$table='ccdSnowfallNormal',$limit);

		
				
			
				
				 #------------------------------------------------------------------------------
				 # WindSpeed Averages from page 54 to 62 Jan-Dec-Ann
				 #------------------------------------------------------------------------------
				
				$result =	$this->getCCDdata(	$coopFilePath,
												$pageStart=54,$pageEnd=62,
												$table='ccdWindspeedNormal',
				  								$ignoreTableFields=array('id','latitude','longitude'),
												$comment='Wind - Average Speed (MPH)',
												$dataPattern=			'^[A-Z\.\(\) ,-\/\'&#;]+',
												$excludeWords=	array('NORMALS','DATA','DR','WIND'),
												$insertLatLon=TRUE
												);
					
				# Look over the outer index and inner associative array and insert them into the database							
				$this->mysqlInsertCCD($result,$table,$comment,$limit,$truncate);
				
				# add Latitude and Longitude to newly inserted DB records
				$result= $this->addLatLonToCCD($field='name',$table,$limit);

				
				//die(var_dump($insertSQL));							
				
				
				

				 #------------------------------------------------------------------------------
				 # NORMAL Precipitation Averages from pages 136 to 145 Jan-Dec-Ann
				 #------------------------------------------------------------------------------
				
				$result =	$this->getCCDdata(	$coopFilePath,
												$pageStart=136,$pageEnd=145,
												$table='ccdPrecipitationNormal',
												$ignoreTableFields=array('id','latitude','longitude'),
												$comment='Normal Precipitation, Inches',
												$dataPattern=			'^[A-Z\.\(\) ,-\/\'&#;]+',
												$excludeWords=	array('NORMALS','NORMAL'),
												$insertLatLon=TRUE
												);
			
				
												
				# Look over the outer index and inner associative array and insert them into the database							
				$this->mysqlInsertCCD($result,$table,$comment,$limit,$truncate);
				
				# add Latitude and Longitude to newly inserted DB records
				$result= $this->addLatLonToCCD($field='name',$table,$limit);
				
				

				
				
				
				
				 #------------------------------------------------------------------------------
				 # SUNSHINE - AVERAGE PERCENTAGE OF POSSIBLE from pages 71 to 75 Jan-Dec-Ann
				 #------------------------------------------------------------------------------
				
			 	$result =	$this->getCCDdata(	$coopFilePath,
			 									$pageStart=70,$pageEnd=75,
												$table='ccdSunshine',
												$ignoreTableFields=array('id','latitude','longitude'),
												$comment='Sunshine - Percentage of Possible',
												$dataPattern='^[A-Z\.\(\) ,-\/\'&#;]+',
												$excludeWords=array('DATA','SUNSHINE','THE','IT'),
												$insertLatLon=TRUE

											);
			
				
												
				# Look over the outer index and inner associative array and insert them into the database							
				$this->mysqlInsertCCD($result,$table,$comment,$limit,$truncate);
				
				# add Latitude and Longitude to newly inserted DB records
				$result= $this->addLatLonToCCD($field='name',$table,$limit);
				
				
				
				 #---------------------------------------------------------------------------------------------------
				 # CLOUDINESS - MEAN NUMBER OF DAYS : CLEAR, PARTLY CLOUDY, CLOUDY from pages 71 to 83 Jan-Dec-Ann
				 #---------------------------------------------------------------------------------------------------

				$result =	$this->getCCDdata(	$coopFilePath,
												$pageStart=75,$pageEnd=83,
												$table='ccdCloudiness',
												$ignoreTableFields=array('id','latitude','longitude'),
												$comment='CLOUDINESS - MEAN NUMBER OF DAYS : CLEAR, PARTLY CLOUDY, CLOUDY',
												$dataPattern=	'^[A-Z\.\(\) ,-\/\'&#;]+',
												$excludeWords=	array('DATA','CLOUDINESS','THE','IT','CL'),
												$insertLatLon=TRUE
											);												
			
											
				# Look over the outer index and inner associative array and insert them into the database							
				$this->mysqlInsertCCD($result,$table,$comment,$limit,$truncate);
				
				# Add Latitude and Longitude to newly inserted DB records
				$result= $this->addLatLonToCCD($field='name',$table,$limit);
				
				
				 #-------------------------------------------------------------------------------------------
				 # Average Relative Humidity (percent) - Morning (M) and Afternoon (A) 71 to 83 Jan-Dec-Ann
				 #-------------------------------------------------------------------------------------------
						
				$result =	$this->getCCDdata(	$coopFilePath,
												$pageStart=83,$pageEnd=91,
												$table='ccdRelHumidity', 
												$ignoreTableFields=array('id','latitude','longitude'),
												$comment='Average Relative Humidity (percent) - Morning (M) and Afternoon (A)',
												$dataPattern='^[A-Z\.\(\) ,-\/\'&#;]+',
												$excludeWords=	array('DATA','AVERAGE','M','IT','CL'),
												$insertLatLon=TRUE 
											);												
				
				# Look over the outer index and inner associative array and insert them into the database							
				$this->mysqlInsertCCD($result,$table,$comment,$limit,$truncate);
				
				# Add Latitude and Longitude to newly inserted DB records
				$result= $this->addLatLonToCCD($field='name',$table,$limit);
				
				
				//Put a iteration limit on the function for testing
				if($count>=$limit-1):
					break;
				endif;	
				
				$count++;
				
	
		endforeach;		
		
		//output any errors
		var_dump($this->e);
		die("I'm done");
	}
/*
 * This method extracts all the data from start page to end page, processes it by add/subtracting headers and values and returns a result for INSERT into MYSQL
 */	
private function getCCDdata(	$coopFilePath,
								$pageStart,$pageEnd,	
								$table,
								$ignoredTableFields,
								$comment,
								$dataPattern,
								$excludedWords,									
								$insertLatLon=NULL								
							) 
	{
				
				
											
				//open the Text File
				$txt = $this->file2String($coopFilePath);
				
				
				
				
				//Get all text between pages 54 and 63 (WindSpeedAvg)
				$selectedText = $this->textBetweenPages($pageStart,$pageEnd,$txt);
				
				
				//If there is no text, log an error
				if($selectedText!=''&& $selectedText!==null):

									
					//Convert the selected text into an array delimited by new lines
					$lines =  $this->convert2Array($selectedText,$beginAt=0);

					
					//GET FIELD HEADERS From the Table. IGNORE The fields not used for find data like 'id','latitude','longitude',etc
					$fieldHeaders= $this->db->MetaColumnNames($table,$numericIndex=TRUE);	
					
					//Ignore all Fields that are not Data specific ...like id,latitude,longitude
					foreach($fieldHeaders as $value): 
						if(!in_array($value, $ignoredTableFields)) $dataFieldHeaders[]=$value; 
					endforeach;
					
					
					//Convert excluded List into a word boundry list and Add it to the original regex pattern
					$dataPattern = $this->addExcludedWordsToRegexPattern($excludedWords,$dataPattern);
					
					
					//Get the matching lines from the regular expression
					$result =  $this->extractValues(	$lines,
														$dataPattern,
														$dataFieldHeaders,
														$total='EOF');
				
					
					
					//Cleanup
					
					# Convert strings like 'AP' to 'Airport' in the name field
					$result = $this->convertNameCodes($result,$search='AP',$replace="Airport"); 

					
	
					return $result;
					
				
				else:
						echo 'Could not get the text for '. $this->v->coopFilePath;
						$this->e[]='Could not get the $category text for '. $this->v->coopFilePath;
				
				endif;
			
	}
	
	/*
	 * Convert the excluded Words array into a Word Boundy Look Ahead/Behind?  Then add the string to the regular expression
	 */
	private function addExcludedWordsToRegexPattern($excludedWords,$regex)
	{
							
					//Convert excluded List into a word boundry list
					foreach($excludedWords as $val)  $excl .= "(?!\b{$val}\b)";
					//Add the excluded list to the original regular expression
					$result = $excl.$regex;
					return $result;
	}


/*
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
			curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			 
			$data = curl_exec($ch);
			curl_close($ch);

			//Check our Response code to ensure success
			if (substr($data,0,3) == "200"):				
				$data = explode(",",$data);
				return $data;			
			else:
				$this->e->latlon[]= "Error in geocoding! Http error ".substr($data,0,3);
			endif;
			
			//sleep(1); //Stagger Request to keep Google from banning you
			
			
		}

*/	
	private function insertClimValues($table,$coopId,$coopFile,$data,$category)
	{
		
				//Build Insert Array for the Monthly temperatures
				$record=array(	'coopId'=>$coopId,
								'coopFile'=>$coopFile,
								'category'=>$category); 				
				
				
				//Building all the months and their values
				$sum=0;
				foreach($data as $key=>$value):
						
						//Convert the key to a MYSQL friendly string
						$key=$this->convert2FullMonthName($key); //Stupid.  MYSQL doesn't like month abrbeviations like 'dec'
						
						//If there are any weird characters, convert them to 0
						if( in_array($value,array('#','','@')) ) $value=0;
						
						$record[$key]=$value;
						
						//Sum all the numbers to average 
						$sum= $sum+$value;
				endforeach;
				
				
				//Insert the Average Value of all the months
				$record['average']=$sum/12;
				
				
				//Make the final SQL Insert
				$rs=$this->db->Execute("SELECT * FROM $table WHERE id = -1");
				$insertSQL = $this->db->GetInsertSQL($rs, $record); 
				$this->db->Execute($insertSQL);
	}
	
	private function insert($table,$record)
	{
			//Make the final SQL Insert
			$rs=$this->db->Execute("SELECT * FROM $table WHERE id = -1");
			$insertSQL = $this->db->GetInsertSQL($rs, $record); 
			$this->db->Execute($insertSQL);
			
	}
		
	private function outputClim20($clim20)
	{
		echo <<<EOB
			<LINK href="http://www.admixweb.com/downloads/csstablegallery/bluedream.css" rel="stylesheet" type="text/css">
			<table id="1">
				<thead>
					<tr>
						<th scope="col" id="1">CoopID</th><th scope="col" id="1">Mean Temperature</th><th scope="col" id="1">Total Precipitation</th><th scope="col" id="1">Total Snowfall</th>
					</tr>
				</thead>
				<tbody>
EOB;
		
				foreach ($clim20 as $coopId=>$dataset):	
					echo "<tr>";		
						foreach($dataset as $value):
							echo 	"<td>$value</td>";	
						endforeach;
					echo "</tr>";
				endforeach;
				
		echo <<<EOK
				</tbody>
					<tfoot>
						<tr>
							<td>Clim24 NOAA data Normals</td>
						</tr>
					</tfoot>
				</table>
EOK;
	}

	
	private function extractFromHTML($url,$pattern,$exclude,$removeChar)
	{

			$contents = file_get_contents($url);
			
			//Use XPath and DOM
			$doc = new DOMDocument();
			$doc->preserveWhiteSpace = false;
			$doc->loadHTML($contents);
			$xpath = new DOMXpath($doc);
			$elements = $xpath->evaluate($pattern);
			
			if (!is_null($elements)):
		  		foreach ($elements as $element):		  		
		   			// echo "<br/>[". $element->nodeName. "]";
		    		$nodes = $element->childNodes;
			    	foreach ($nodes as $node):
			    		if(!in_array($node->nodeValue,$exclude) )			    				
			    			$matches[]= str_replace($removeChar,'',$node->nodeValue);	
			    	endforeach;
		 		 endforeach;
			endif;
			
			return $matches;	
	}
	
	private function curlDLMutliple($urls,$save_to)
	{
		$mh = curl_multi_init();
		
		foreach ($urls as $i => $url) {
		    $g=$save_to.basename($url);
		    if(!is_file($g)){
		        $conn[$i]=curl_init($url);
		        $fp[$i]=fopen ($g, "w");
		        curl_setopt ($conn[$i], CURLOPT_FILE, $fp[$i]);
		        curl_setopt ($conn[$i], CURLOPT_HEADER ,0);
		        curl_setopt($conn[$i],CURLOPT_CONNECTTIMEOUT,60);
		        curl_multi_add_handle ($mh,$conn[$i]);
		    }
		}
		do {
		    $n=curl_multi_exec($mh,$active);
		}
		while ($active);
		foreach ($urls as $i => $url) {
		    curl_multi_remove_handle($mh,$conn[$i]);
		    curl_close($conn[$i]);
		    fclose ($fp[$i]);
		}
		
		curl_multi_close($mh);	
		
	}
	
	/*
	private function curl_get_file_contents($url)
	{		
		
			//$fh = fopen('/tmp/curl.txt','w') or die($php_errormsg);
			$c = curl_init();
	        curl_setopt($c, CURLOPT_URL, $url);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			//curl_setopt($c,CURLOPT_AUTOREFERER,1);
	        curl_setopt($c,  CURLOPT_USERAGENT, $this->v->userAgent);
	        curl_setopt($c,CURLOPT_TIMEOUT,$this->curltimeOut); 
	       // curl_setopt($c, CURLOPT_VERBOSE, 1);
	       // curl_setopt($c,CURLOPT_FOLLOWLOCATION,1);
	        //curl_setopt($c,CURLOPT_COOKIESESSION,1);
	        //curl_setopt($c,CURLOPT_FRESH_CONNECT,1);
	        //curl_setopt($c, CURLOPT_STDERR, $fh);
	        $contents = curl_exec($c);
	        curl_close($c);
	       // fclose($fh) or die($php_errormsg);
			
	        

	        //echo  $this->v->userAgent.' '.$this->v->url;
	        if ($contents) return $contents;
	            else return FALSE;
	}
	*/
/*
	private function getFiles($path,$exclude)
	{
		
		if($exclude=='' || $exclude==NULL ) $exclude = $this->v->exclude;
		$it = new RecursiveDirectoryIterator($path);
		foreach(new RecursiveIteratorIterator($it) as $file):
				if	( !in_array($file->getFilename(),$exclude)) $fileList[$file->getFileName()]= $file->getPathName();
		
		endforeach;
		//if(count($fileList)==1): //Fix Later
		//	die("No Files Found");
		//else:
		return $fileList;
		//endif;
	}
	
	private function getFiles2($path,$filter,$exclude=NULL)
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
	
*/

	/*
	private function pdftotext($file)
	{
		$ret = shell_exec('/opt/local/bin/pdftotext -layout -enc Latin1 '.$file.' -');
		
		switch($ret):
						case 127:
							die("Could not find pdftotext tool.");
							break;
						
						case  1:
							die("Could not find pdf file.");
							break;	
						
						//Success!
						default:
							return trim(ereg_replace( ' +', ' ', $ret));
							break;
		endswitch;
							
	}
	*/
	
	private function convert2Array($data,$num2Remove=0)
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
	/*
	private function searchArray($pattern,$array)
	{
			foreach($array as $value):
				// If there is a match, add it to the matches array
				if(preg_match($pattern,$value)) $matches[]=$value;
			endforeach;	
			
			return $matches;	
	}
	*/
	

	/*
	 * Use this function to get a SINGLE item from the matched line
	 */
	private function getMean($array,$pattern,$matchLineNumber=1,$matchItem=1,$totalLines=1)
	{
				
				if($matchItem !=-1)$matchItem--;
				//Move the match back to a Zero index
				//$matchLineNumber--; if($matchItem!=1)
				
				
				//Check each line of the array for the pattern and store each match in the lineMatches Array
				foreach($array as $line):
					if(preg_match($pattern,$line)) $lineMatches[]=$line;
				endforeach;

				//var_dump($lineMatches);
				$matchLineNumer>1? $offset=($matchLineNumber*totalLines)-1 : $offset=0;
				
				$totalLines !='EOF'? $total = $offset+$totalLines: $total = count($lineMatches);
				
				
				for($i=$offset; $i<$total; $i++):
					
					// Explode the chosen matchLine Number (The matchItem is on THIS line)
					$lineValues = explode(" ",$lineMatches[$i]); //
					
					
					foreach($lineValues as $value): 
						
						if(is_numeric($value)):
							$numbers[]=floatval($value);
							//echo "$value a number";
						else:
							//echo "$value NOT number";
						endif; 
					
					endforeach;
					
					
					//If $matchItem is -1, get the last value in the array
					if($matchItem == -1):
						$count = count($numbers);
						
						$fields=array('YRS','JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC','ANN');
						for($i=$count;$i>=0; $i--):
							$key=$fields[$i];
							$value = $numbers[$i];
							$values[$key]=$value;
						endfor;
						
						//Get the array key: Tuscalusa, AL (AP)
						preg_match($pattern,$lineMatches[$i],$matches);
						$key=$matches[0];
	
					
						//$value=array_pop($lineValues);
															
					else:
						$value = $lineValues[$matchItem]; 
						$value = $this->validateMean($value);
					endif;
	
					//Get the City,State as the key like: BIRMINGHAM AP,AL 
					$value = $this->validateMean($value);
					preg_match($pattern,$lineMatches[$i],$matches);
					$key=$matches[0];
	
					die(var_dump($result[$key]=$value));  //array('jan'=>'20.4')
				 
				endfor;
				
				//die(var_dump($result));
				return $result;
				
	}
	/*
	 * Use this function to get a multiple item from the matched line
	 */
	private function extractValues($array,$pattern,$fieldHeaders=1,$totalLines=1)
	{
				
				$pattern = '/'.$pattern.'/';
				
				//Check each line of the array for the pattern and store each match in the lineMatches Array
				foreach($array as $line):	
					if(preg_match($pattern,$line,$m)): //currently matches whole line. Later, Try to get the match to exclude the City Name
						$city[] =trim($m[0]);//Store all the city names
						$lineMatches[]= str_replace($m[0],'',$line); //Use str_replace in the meantime to remove the city from the array
					endif;				
				endforeach;
				
				
				$cityData = $this->createCityDataArray($lineMatches,$city);
				
				$sqlInsert = $this->addKeysToCityDataArray($cityData,$fieldHeaders);
				
				return $sqlInsert;
				
	}
	
	
	/*
	 * Explode numbers from the matched line in and array and add the city name as its first element
	 */
	private function createCityDataArray($numbers,$city)
	{
			
			foreach($numbers as $key=>$value):
					$result[$key]= explode(" ",trim($value)); //trim all the values
					array_unshift($result[$key],$city[$key]); //Add city back to the beginning of the array
			endforeach;
			
			return $result;
	}
	
	
	/*
	 * Add the Table Field Headers back to the city data array for MYSQL insertion
	 */
	private function addKeysToCityDataArray($cityData,$fieldHeaders)
	{
		foreach($cityData as $key1 => $value1):
					
					foreach($value1 as $key2=>$value2):	
						$arrayValues[$fieldHeaders[$key2]]=$value2; //Add NAME, YRS, JAN,FEB...ANN headers as keys for each value
					endforeach;	
					
					$arrayIndex[]=$arrayValues; //Add arrayInner to the final array	
		
		endforeach;
				
				
		return $arrayIndex;
	}
	
	private function buildClim20InsertStatement($result,$category)
	{
		//Convert all State Abreviations to full State Names.. jan => january  and convert the numbers to floats
				foreach($result as $key=>$value):
					$insert[$this->convert2FullMonthName($key)]= (floatval($value)); 
				endforeach;
				
				//delete last item, 'ann' to get the total sum of the array
				$total= $insert; array_pop($total);
				$total = array_sum($total);

				$insert['total']=$total;
				$insert['mean']=$insert['ann'];
				$insert['coopId']=$this->v->coopId;
				$insert['coopFile']=$this->v->coopFilePath;
				$insert['category']=$category;
				
				return $insert;
	}

	private function buildClim84InsertStatement($result,$category)
	{
		$total=0;

		//Convert all State Abreviations to full State Names.. jan => january  and convert the numbers to floats
		foreach($result as $key=>$value):			
			$insert[$this->convert2FullMonthName($key)]= $value; //number_format($value / $this->daysinMonth($key),2)
			$total = $total + $value;
		endforeach;

		$insert['total']= $total ;
		$insert['mean']=$total/12;
		$insert['coopId']=$this->v->coopId;
		$insert['coopFile']=$this->v->coopFilePath;
		$insert['category']=$category;
		
		//die(var_dump($insert));
		
		return $insert;
	}
	
/*
 * Add latitude and longitude values to the CCD data tables
 */	
public function addLatLonToCCD($field,$table,$limit=1)
{

	$rsOuter = $this->db->Execute("	SELECT {$field} FROM {$table} 
									WHERE latitude ='0.00' OR longitude ='0.00' 
									LIMIT {$limit}");
	
	while (!$rsOuter->EOF) 
	{
		$gps=array();
		#var_dump($rsOuter->fields);
		
		$gps = $this->googleGeoCode($rsOuter->fields['name'],$format='csv'); 
		
	
		#$gps = $this->googleGeoCode('HobGobble AP, Xa',$format='csv'); 
		
		if($gps==NULL):
			 $cityState = $this->extractCityStateFromString($name,$gps);
			 $gps = $this->googleGeoCode($cityState,$format='csv'); 
			 	if($gps==NULL) $this->e->latlon[]="Could not find gps for $name or $cityState";
		endif; 
		
		
		if($gps != NULL):		
			
			$name = $this->db->qstr($rsOuter->fields['name']);
			$rsInner = $this->db->Execute("UPDATE {$table} 	SET 		latitude = '{$gps[2]}', 
																		longitude = '{$gps[3]}' 
															WHERE		{$field} = 	{$name}
											");
		endif;
		
		
		
		$rsOuter->MoveNext();	
	}
	
	#$result = $this->db->GetOne("SELECT name FROM {$table} WHERE latitude=0.00 OR longitude=0.00");
	#if($result != FALSE) echo "Execute the loop again";
}
	
	/*
	 * Add extra fields to a insert query..May use later
	 */
	private function addLatLonFields($result,$ignoreTableFields,$limit=NULL)
	{
		
		$count=0;
		foreach($result as $key1=>$value1):  //0,1,2,3,4....			
			
			foreach($value1 as $key2=>$value2):
							
				if($key2=='name'):
					$gps = $this->googleGeoCode($value2,$format='csv'); 

					/*
					 * Check Accuracy. Need to be a Sub-region level or above. http://code.google.com/apis/maps/documentation/reference.html#GGeoAddressAccuracy
					 * If accuracy is too low, simplify the name: GREENVILLE-SPARTANBURG Airport, SC => GREENVILLE, SC
					 */
					
					
					if($gps[1] < 3 && $gps[0] !=603):	
						$cityState = $this->simplifyNameforLatLon($record['name']);
						$gps = $this->googleGeoCode($cityState,$format='csv');			
					endif;
					

					# Make sure latitude and longitude values are returned from Google. If not, skip adding the array
					if(($gps[2] && $gps[3] !='')):
						$result[$key1]['latitude']=$gps[2];
						$result[$key1]['longitude']=$gps[3];
					endif;
				endif;
			
			endforeach;		

			# Limit for Testing
			if($count>=$limit):
				break;
				die(var_dump($result));
			else:
				$count++;
			endif;
			
			//Don't swamp google maps
			sleep(8);
		
		endforeach;
		
		return $result;
	}
	
	private function extractCityStateFromString($str,$gps)
	{		
				
				preg_match('/^([\w]+)[\s]+/',$str,$city);  	# Extract City
				preg_match('/\b([A-Z]{2})\b$/',$str,$state);# Extract State
			
				# Log the info
				$this->e->latlon[]=$record['name'].' too inaccurate at '.$gps[1]. ' now trying '.$city[1].', '.$state[0];
				
				return $city[1].', '.$state[1];				
	}
	
	/*
	 * Convert Codes in the $result[0]['name']  => 'AP' becomes Airport
	 */
	private function convertNameCodes($result,$pattern,$replace)
	{
				$replace = ' '.$replace.' ';
				$pattern ='/[ -]+'.$pattern.'[ ,]+/';
				
				foreach($result as $key1=>$value1):
					foreach ($value1 as $key2=>$value2):
						if($key2=='name') $result[$key1][$key2]= preg_replace($pattern,$replace,$value2);
					endforeach;
				endforeach;

				return $result;
	}
	
	
	private function mysqlInsertCCD($result,$table,$comment, $limit=5,$truncate=FALSE)
	{			
				
				# Truncate Table if $truncate is True
				if($truncate===TRUE) $this->truncateTable($table);
													
				# Iterate over each record ....The $result looks like [0][city=>houston,yrs=>7,jan=>6....dec=7]			
				foreach ($result as $record):									
					
					# Add the field values to the record
					foreach($record as $key=>$value):	
						$insert[$key]= $value;																					
					endforeach;	
				
					# Add Latitude and Longitude to the record
					
					
					//Insert the record into the database
					$this->insert($table,$insert);		
					
					//Break the insert script if the limit has been exceeded
					if($count>=$limit):
						break;
					else:
						$count++;
					endif;
															
				endforeach;	
				
				//Make the comment easier to read
				$comment= ucfirst(strtolower($comment));
				
				//Add the comment to the table
				$this->db->Execute("ALTER TABLE $table COMMENT = '{$comment}'");
							
	}
	
	
	
	
	
	private function validateMean($data)
	{

		//Remove  +, #, @, N/A 
		$data =str_replace(array('+','#','@','N/A'),"",$data);
		
		
		//If mean is blank, make it zero
		if($data=='')$data=0;
		
		
		if( !is_numeric($data)):
			die('An error occured.<br/>'.$data.' is an illegal character in file: '.$this->v->coopFile);
			$data= -9999 ;//Some kind of error occured cause the mean has letters in it
		endif;
		
		return $data;
		
	}
	
	/*
	 * Converts month abbreviations like Dec to December
	 * For some reason, MYSQL does like like 'dec' in the Insert statement
	 */
	private function convert2FullMonthName($month)
	{

		$month = strtolower($month);		
		switch ($month):
			case 'jan': return 'january'; break;
			case 'feb': return 'february'; break;
			case 'mar': return 'march'; break;
			case 'apr': return 'april'; break;
			case 'may': return 'may'; break;
			case 'jun': return 'june'; break;
			case 'jul': return 'july'; break;
			case 'aug': return 'august'; break;
			case 'sep': return 'september'; break;
			case 'oct': return 'october'; break;
			case 'nov': return 'november'; break;
			case 'dec': return 'december'; break;
			case 'ann': return 'ann'; break;
			default: 	return $month;
		endswitch;
		
	}
	
	private function daysinMonth($month)
	{
		$month = strtolower($month);		
		switch ($month):
			case 'jan': return 31; break;
			case 'feb': return 28; break;
			case 'mar': return 31; break;
			case 'apr': return 30; break;
			case 'may': return 31; break;
			case 'jun': return 30; break;
			case 'jul': return 31; break;
			case 'aug': return 31; break;
			case 'sep': return 30; break;
			case 'oct': return 31; break;
			case 'nov': return 30; break;
			case 'dec': return 31; break;
		endswitch;
	}
	
	/*
	private function removeFileExtension($filename)
	{
		return substr($filename, 0, (strlen ($filename)) - (strlen (strrchr($filename,'.'))));
	}
	
	*/
	
	private function stringBetween($var, $start, $end) {
	    
		if($end=='EOF'):
			$start = strpos($var,$start);
			return substr($var,$start,999999999);
		else:
			
			return preg_match('{' . preg_quote($start) . '(.*?)' . preg_quote($end) . '}s', $var, $m)
		        ? $m[1]
		        : '';
	    endif;
	}
	
	private function textBetweenPages($start,$end,$txt)
	{
		$pattern = "/^[ ]?$start(.*?)^[ ]?$end/ms";
		return preg_match($pattern,$txt,$m) ? $m[1] : 'nada found';
	}
	
	private function stringBetween_noRegex($var, $start, $end) {
	  
		$start = strpos($var,$start);
		$end = strpos($var,$end);

		//echo "$start ,   $end";
		if($end=='EOF'):
			$start = strpos($var,$start);
			return substr($var,$start,999999999);
		else:
			return substr($var,$start,$end);

	    endif;
	
	}
	
	
	private function getFieldHeaders($pattern,$txt,$addFields=NULL,$excludeFields=NULL)
	{
		preg_match('/'.$pattern.'/ms',$txt,$m);	
		
		if ($m[1]!=null):
		 	
			//Explode the field string into the $fieldHeaders array 
			$fieldHeaders = explode(" ",trim($m[1]));		
			
			
			//ADD FIELDS 
			foreach ($addFields as $key=>$value): 		
				//Looks like ^=>['A','B'] $=>['D','E']
				
				//Beginning of array
				if($key=='^'):
					foreach ($addFields[$key] as $value2) array_unshift($fieldHeaders,$value2);
				
				//End of array
				elseif($key=='$'):
					//foreach ($addFields[$key] as $value2) $fieldHeaders[]=$value2;
				endif;	
					
			endforeach;
			
			
			//EXCLUDE FIELDS 
			foreach ($fieldHeaders as $key=>$value):
				if(in_array($value,$excludeFields)===TRUE) unset( $fieldHeaders[$key] );
			endforeach;
				
			//Send Back
			return $fieldHeaders;
		
		else:
			$this->e[]="Fields Headers were not able to be created";
			return "nothing found";
		endif;
		
	}
	
	/*
	 * For every accepted Data field header NOT in the $exclude array, triple it. So, array('Jan') becomes array('JanCL','JanPC','JanCD')
	 * Else, just return the array element like 'Yrs'
	 */
	private function addFieldSubHeaders($fieldHeaders,$fieldSubHeaders, $exclude)
	{		
		foreach($fieldHeaders as $value):
			if(!in_array($value,$exclude)):
				foreach ($fieldSubHeaders as $value2):
					$result[]=$value.$value2;
				endforeach;
			else:
				$result[]=$value;
			endif;		
		endforeach;
		
		return $result;
		
	}
	
	

	
	//---------TO BE USED LATER?------------------------------------------------------------------------------//
	

	
	
	
	//---------To DELETE  DEPRECATED--------------------------------------------------------------------------//
	private function processClim20b($data,$limit=5)
	{
		$this->v->clim20=array();
		$count=0;
		
		foreach ($data AS $key=>$value):
				
				//Get the CoopID to make updates to the database
				$coopId = $this->removeFileExtension($key); 
				
				//Convert the pdf to text with pdftotext command line utility
				$txt= $this->pdftotext($value);
				
				//Convert the text into an array delimited by new lines
				$lines =  $this->convert2Array($txt,$beginAt=0);
	
				//Get TempMean for the year
				$t =  $this->getFigures($lines,'/\b^Ann\b/',$matchNumber=1,$itemCount=13); //Jan Feb....Dec  Ann
			
				
				//Get Precipitation Total  per Year	
				$precipTotal =  $this->getFigures($lines,'/\b^Ann\b/',$matchNumber=2,$itemCount=13);
									
				
				//Get SnowFall Total per year
				$snowfallTotal =  $this->getFigures($lines,'/\b^Ann\b/',$matchNumber=3,$itemCount=13);
				
	
				//"INSERT INTO table (id, thedate,note) values (" 
				
				//Insert Temperature Means from Jan-Dec and the Annual Mean 
				$rsUpdateTemp= $this->db->Execute("	INSERT INTO clim20 ('',coopId,category,jan,feb.mar,apr,may,june,jul,aug,sep,oct,nov,dec,ann)
													Values('','{$coopId}','temp',
													{$t[1]}','{$t[2]}','{$t[3]}','{$t[4]}', '{$t[5]}','{$t[6]}', '{$t[7]}','{$t[8]}', '{$t[9]}','{$t[10]}','{$t[11]}', '{$t[12]}','{$t[13]}')" );
				
				
				
				//Insert Precipitation Totals Jan-Dec Annual Total
				$rsUpdatePrec= $this->db->Execute("	INSERT INTO clim20 ('',coopId,category,jan,feb.mar,apr,may,june,jul,aug,sep,oct,nov,dec,ann)
													Values('','{$coopId}','precip',
													{$t[1]}','{$t[2]}','{$t[3]}','{$t[4]}', '{$t[5]}','{$t[6]}', '{$t[7]}','{$t[8]}', '{$t[9]}','{$t[10]}','{$t[11]}', '{$t[12]}','{$t[13]}')");
				
				
				//Insert Snow Totals Jan-Dec Annual Total
				$rsUpdateSnow= $this->db->Execute("	INSERT INTO clim20 ('',coopId,category,jan,feb.mar,apr,may,june,jul,aug,sep,oct,nov,dec,ann)
													Values('','{$coopId}','snow',
													{$t[1]}','{$t[2]}','{$t[3]}','{$t[4]}', '{$t[5]}','{$t[6]}', '{$t[7]}','{$t[8]}', '{$t[9]}','{$t[10]}','{$t[11]}', '{$t[12]}','{$t[13]}')");			
				
													
													
													//Store these values int he variables object for debugging
				$this->v->clim20[]=array('coopId'=>$coopId,'tempMean'=>$tempMean,'precipTotal'=>$precipTotal,'snowfallTotal'=>$snowfallTotal);

				
					
				//Break if the limit has been met/exceeded
				if($count>=$limit-1):
					break;
				endif;	

				$count++;
									
		endforeach;
	
		return $this->v->clim20;
	}
	
	
	/*
	 * Use this function to get MULTIPLE items on from the matched line
	 */
	private function getFigures($array,$pattern,$matchLine=0,$itemCount=13)
	{
				
				//Move the match back to a Zero index
				$matchLine--; $itemCount--;
				
				
				//Check each line of the array for the pattern and store each match in the lineMatches Array
				foreach($array as $line):
					if(preg_match($pattern,$line)) $lineMatches[]=$line;
				endforeach;

				
								
				// Explode the chosen matchLine Number (The matchItem is on THIS line)
				$lineValues = explode(" ",$lineMatches[$matchLine]); //

				while (count($lineValues>$itemCount)):
					array_shift($lineValues);
				endwhile;
				
				return $lineValues;
				
				//var_dump($lineValues);
				
				//die();
				
				// Get the MatchItem on the chosen matchLine, then check it
				//return $this->validateMean($value); //3rd value after your deleted 'Ann'
				
				
				//$tempMean =  $this->getMean2($lines,'/\bAnn\b/',$matchNumber=0,$item=2);		
	}	
	
	
	
private function processClim20_Annnual($data,$limit=5)
	{
		$this->v->clim20=array();
		$count=0;
		
		foreach ($data AS $key=>$value):
				
				//Get the CoopID to make updates to the database
				$coopId = $this->removeFileExtension($key); 
				
				//Convert the pdf to text with pdftotext command line utility
				$txt= $this->pdftotext($value);
				
				//Convert the text into an array delimited by new lines
				$lines =  $this->convert2Array($txt,$beginAt=0);
	

				//Get TempMean for the year
				$tempMean =  $this->getMean($lines,'/\b^Ann\b/',$matchLineNumber=1,$matchItem=2,$total=1);
				

				//Get Precipitation Total  per Year	
				$precipTotal =  $this->getMean($lines,'/\b^Ann\b/',$matchLineNumber=2,$matchItem=2,$total=1);
									
				//Get SnowFall Total per year
				$snowfallTotal =  $this->getMean($lines,'/\b^Ann\b/',$matchLineNumber=3,$matchItem=2,$total=1);
				
	
				
				
				//Update weather temp,precip, and snow values 
				$rsUpdate= $this->db->Execute("	UPDATE masterStationsHistoryReport 	
												SET 	tempMean 		= 	'{$tempMean}',
														precipTotal		= 	'{$precipTotal}', 
														snowfallTotal	=	'{$snowfallTotal}'
												WHERE 	coopId			=	'{$coopId}'
												LIMIT 999999");
				
				

				//Store these values int he variables object for debugging
				$this->v->clim20[]=array('coopId'=>$coopId,'tempMean'=>$tempMean,'precipTotal'=>$precipTotal,'snowfallTotal'=>$snowfallTotal);

				
					
				//Break if the limit has been met/exceeded
				if($count>=$limit-1):
					break;
				endif;	

				$count++;
									
		endforeach;
	
		return $this->v->clim20;
	}
	
	
	public function updateRelHumidity($url,$table)
	{
		//Send the URL to the elevation web service
		$html = $this->curl_get_file_contents($url);
		
		//Convert the html to a line by line array and remove the field headers
		$lines = $this->convert2Array($html,2);

		
		
		//Iterate over every line and get values
		foreach ($lines as $line)
		{
			//Split the lines into Data
			$values = explode(",", $line);
			
			//Convert the line into the variables you need: city, state, and mean temp
			$this->v->station = $values[0];		
			
			$this->v->state= trim(substr($values[1],0,3));		
			
			//Get the Last Temperature Value (The Mean Average)
			$tempValues = explode("  ",$values[1]);
			$count = count($tempValues)-1;
			$this->v->afternoon = $tempValues[$count]; 
			$this->v->morning = $tempValues[($count-1)]; 
			

			

			//Insert the record into the noaaMeanTemp Database
  			$this->db->Execute("	Update{$table} 
  									SET 	relhM	=	'{$this->v->morning}',
  											relhA 	=	'{$this->v->afternoon}'
  									WHERE	
  										
  										morning,afternoon) values (
  									'{$this->v->station}',
  									'{$this->v->state}',
   									'{$this->v->morning}',
  									'{$this->v->afternoon}')");			
		}

	}


}
?>