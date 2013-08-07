<?php
require_once('./adodb5/adodb.inc.php');

$r = new Rand();
//$r->addPopulationIndex();
$r->cityInsolationLoop();
//$r->addTemperature();

//print "insolation is: ".$r->queryGPSbyCityState('Austin','Texas')."kWm<sup";

//Price of Propane Cents/Gallon
//http://tonto.eia.doe.gov/dnav/pet/pet_pri_wfr_a_EPLLPA_PWR_cpgal_w.htm
///html/body/form[@id='query']/table[4]/tbody/tr[6]/td[7]

//Price of Natural Gas: Commercial Price, Monthly: 	(Dollars per Thousand Cubic Feet, except where noted)
//http://tonto.eia.doe.gov/dnav/ng/ng_pri_sum_a_EPG0_PCS_DMcf_m.htm
// Data by Area: http://tonto.eia.doe.gov/dnav/ng/ng_pri_sum_a_EPG0_FWA_DMcf_a.htm

class Rand 
{
	public  $v,$db;
	
	function __construct()
	{		
		//Set up intial variables;
		$v->sleep = 7;
		$v->start = 0;
		$v->limit = 999999999; //End of Rows
		//$v->userAgent= 'Googlebot/2.1 (+http://www.google.com/bot.html)';
		$v->userAgent= 'Mozilla/5.0 (X11; U; Linux i686; it-IT; rv:1.9.0.2) Gecko/2008092313 Ubuntu/9.25 (jaunty) Firefox/3.8';
		$this->v= $v;

		// Set up database
		$db = ADONewConnection('mysql');
		$db->debug = true;
		$db->port=8889;
		$db->Connect('localhost', 'grahama', 'musiccows', 'rand');
		$this->db = $db;
		
		
	}
		
	
	//-----------public functions------------------------------
	public function cityInsolationLoop()
	{
		
		//die("limit is: ".$v->limit);
		//Build variable array From any GET variables
		/*
		!isset($_GET['lat'])? $v->lat=36 : $this->v->lat=$_GET['lat'];
		!isset($_GET['lon'])? $v->lon=-119 : $this->v->lon=$_GET['lon'];
		!isset($_GET['city'])? $v->city='unknown' : $this->v->city=$_GET['city'];
		!isset($_GET['state'])? $v->state='unknown' : $this->v->state=$_GET['state'];
		*/
		
		
		$this->v->userAgent="Mozilla/5.0 (X11; U; Linux i686; pl-PL; rv:1.9.0.2) Gecko/20121223 Ubuntu/9.25 (jaunty) Firefox/3.8";
		//$this->v->userAgent="Googlebot/2.1 (+http://www.google.com/bot.html)";
		$this->v->nameserver= "http://eosweb.larc.nasa.gov";
		$this->v->ip = "http://198.119.134.212";
		$this->v->scrape='/html/body/div[6]/table/tr[2]/td[14]';
		
		
		
		//Get All the citys
			$rsOuter = $this->db->Execute("	SELECT Distinct city,state,latitude,longitude
											FROM zipcode
											WHERE insolation=-1 
											GROUP BY state, city
											ORDER BY population DESC
											Limit 999999 ");
			
			while (!$rsOuter->EOF) 
			{
				
				$this->v->city	=	$rsOuter->fields['city'];
				$this->v->state	=	$rsOuter->fields['state'];
				$this->v->lat	=	$rsOuter->fields['latitude'];
				$this->v->lon	=	$rsOuter->fields['longitude'];
				
				$this->v->url = $this->v->ip.
								"/cgi-bin/sse/grid.cgi?email=grahama%40me.com&step=2&lat=".
								$this->v->lat.
								"&lon=".
								$this->v->lon.
								"&num=062127&p=grid_id&p=swvdwncook&p=swv_dwn&veg=17&hgt=+100&submit=Submit";
				
				
				//Send the url to Nasa and Scrape the Avg Insolation Value store it in $v->insolation
				$result = $this->webScraper($this->v->url,$this->v->scrape);
				$this->v->insolation = $result[0];
				
				//Write the Insolation value into ALL records of that city and state in the database
				$rsInner = $this->db->Execute("	Update zipcode 
												SET insolation='{$this->v->insolation}' 
												WHERE city = '{$this->v->city}' 
												AND state= '{$this->v->state}'");
				
				echo 'insolation for '.$this->v->city.','.$this->v->state.' is: '.$this->v->insolation.' kW/m<sup>2</sup>/day';
				echo '<br/>';
				sleep($this->v->sleep);
				
				$rsOuter->MoveNext();
		}
		echo "I'm done";
	}
	
	public function addStateNames()
	{
		$rsOuter = $this->db->Execute("SELECT State,Abbreviation FROM states");
	
		while(!$rsOuter->EOF)
		{
			$rsInner=$this->db->Execute("UPDATE zipcode SET stateName = '{$rsOuter->fields['State']}' WHERE state = '{$rsOuter->fields['Abbreviation']}'"); 
			$rsOuter->MoveNext();
		}
		//echo "I'm done";
	}
	
	
	public function addTemperature()
	{
		$rsOuter= $this->db->Execute("	Select LOWER(city) AS city, 
										LOWER(REPLACE(stateName,' ','-')) AS stateName 
										from zipcode Limit 1");
		
		while(!$rsOuter->EOF)
		{
			$this->v->url='http://countrystudies.us/united-states/weather/'. $rsOuter->fields['stateName'].'/'.$rsOuter->fields['city'].'.htm';
			//echo $this->v->url;
			$this->v->xpath->temp='/html/body/div[2]/center/table/tr[4]/td'; //Gets Yearly Average of Temperature	
			$this->v->xpath->precip ='/html/body/div[2]/center/table/tr[5]/td'; //Gets Yearly Average of Precipitation			
			
			
			//Put the curl statement here to keep the webservice from being accessed twice
			
			
			//Get average Precipitation
			$values = $this->webScraper($this->v->url, $this->v->xpath->precip); 
			$total=0;
			
			foreach ($values as $value)
			{
				$value = floatval($value);
				echo $value.",";
				$total = $total + $value;
			}
			 
			$this->v->precip = $total/12;  //Divide by 12 months in year to get average
			echo ":  avg temp is: ".$this->v->precip.'<br/>';
			
			
			//Get average Temperature
			$values =$this->webScraper($this->v->url, $this->v->xpath->temp); 
			$total=0;
			
			foreach ($values as $value)
			{
				$value = floatval($value);
				echo $value.",";
				$total = $total + $value;
			}
			 
			$this->v->temp = $total/12;  //Divide by 12 months in year to get average
			echo ":  avg temp is: ".$this->v->temp;
		
			
			
			
			$rsOuter->MoveNext();
			//die('I am done');
			
		}
	}
	
	
	public function getPopulation()
	{
		//$this->v->userAgent="Googlebot/2.1 (+http://www.google.com/bot.html)";		
		
		//Get All the citys
			$rsOuter = $this->db->Execute("	SELECT city,state,zip,latitude,longitude
											FROM zipcode
											WHERE population=0 
											GROUP BY state, city
											ORDER BY population DESC ");
			
			while (!$rsOuter->EOF) 
			{
				$this->v->city	=	$rsOuter->fields['city'];
				$this->v->state	=	$rsOuter->fields['state'];
				$this->v->lat	=	$rsOuter->fields['latitude'];
				$this->v->lon	=	$rsOuter->fields['longitude'];
				
				//Send the Zip code to the Answers.com site in the url
				$this->v->url = 'http://www.answers.com/topic/'.$rsOuter->fields['city'].'-'.$rsOuter->fields['state'];
							
				
				//This Targets the Population table cell on Answers.com
				//$this->v->xpath ="/html/body/div[@id='pageWrapper']/div[@id='container']/div[@id='hmiddle']/div[@id='contents']/div[@id='right-column']/div[@id='new_left']/div[@id='Wikipedia_d']/div[@id='firstDs']/div[@id='wpcontent']/div[@id='wp_libra']/table[1]/tbody/tr[15]/td";
				$this->v->xpath="/html/body/div[2]/div/div/div[3]/div[2]/div[4]/div[9]/div[2]/div[5]/div/table/tr[15]/td";
	
				//var_dump($this->v); die();
				
				//Send userAgent and the Xpath variables in the 'v' object to the scrapeInsolation Method
				$this->v->population = $this->webScraper($this->v);
				
				//echo 'pop: '.$this->scrapeInsolation($this->v);
				echo "population is: ".$this->v->population;
				die();
				
				//Write the Population value into ALL records of that city and state in the database
				$rsInner = $this->db->Execute("	Update zipcode 
												SET population='{$this->v->population}' 
												WHERE city = '{$this->v->city}'
												AND  state = '{$this->v->state}' ");
				
				echo 'population for '.$this->v->city.','.$this->v->state.' is: '.$this->v->population.' kW/m<sup>2</sup>/day';
				echo '<br/>';
				sleep($this->v->sleep);
				
				$rsOuter->MoveNext();
		}
		echo "I'm done";
	}
	
	
	public function updateZipCode()
	{
		$rsOuter = $this->db->Execute("Select id,city,state FROM zipcode");
		
			while (!$rsOuter->EOF) 
			{

				//Get all the correct values from the cities_us table
				$rsPop =  $this->db->Execute("	SELECT insolation,population,elevation
												FROM cities_us
												WHERE city LIKE '%{$rsOuter->fields['city']}%'
												AND   state = '{$rsOuter->fields['state']}' 
												Limit 1"); 				
				
				
				//Dump them into the zipcode table
				$rs = $this->db->Execute("Update zipcode SET 
														population = '{$rsPop->fields['population']}', 
														insolation = '{$rsPop->fields['insolation']}',
														elevation = '{$rsPop->fields['elevation']}'
														WHERE id ='{$rsOuter->fields['id']}' LIMIT 1");
			
			$rsOuter->MoveNext();
			}
	}
	
	
	
	public function addPopulationIndex()
	{

		
		$rsOuter = $this->db->Execute("Select id,city,state FROM cities_us");
	
			while (!$rsOuter->EOF) 
			{
				$sql = "	SELECT POP_2008
							FROM population
							WHERE NAME LIKE '%{$rsOuter->fields['city']}%'
							AND   STATENAME = (SELECT State FROM states WHERE Abbreviation = '{$rsOuter->fields['state']}' )
							Limit 1";			
				$rsPop =  $this->db->Execute($sql); 				
				
				$rs = $this->db->Execute("Update cities_us SET population = '{$rsPop->fields['POP_2008']}' WHERE id ='{$rsOuter->fields['id']}' LIMIT 1");
			
			$rsOuter->MoveNext();
			}
		
		//SELECT NAME, POP_2008 FROM population  WHERE NAME LIKE '%Adak%' AND STATENAME = (SELECT State FROM states WHERE Abbreviation = 'AK' ) Limit 1
		
	}
	
public function addInsolation()
	{

		
		$rsOuter = $this->db->Execute("Select id,city,state FROM zipcode Limit 2,99999999");
	
			while (!$rsOuter->EOF) 
			{
				$sql = "	SELECT insolation
							FROM cities_us
							WHERE city LIKE '%{$rsOuter->fields['city']}%'
							AND   state = '{$rsOuter->fields['state']}' 
							Limit 1";			
				$rsPop =  $this->db->Execute($sql); 				
				
				$rs = $this->db->Execute("Update zipcode SET insolation = '{$rsPop->fields['insolation']}' WHERE id ='{$rsOuter->fields['id']}' LIMIT 1");
			
			$rsOuter->MoveNext();
			}
		
		//SELECT NAME, POP_2008 FROM population  WHERE NAME LIKE '%Adak%' AND STATENAME = (SELECT State FROM states WHERE Abbreviation = 'AK' ) Limit 1
		
	}
	
	public function addElevation()
	{

		
		$rsOuter = $this->db->Execute("Select id,city,state FROM cities_us LIMIT 100,999999999");
	
			while (!$rsOuter->EOF) 
			{
				$sql = "	SELECT ELEVATION
							FROM weather
							WHERE NAME LIKE '%{$rsOuter->fields['city']}%' AND ST = '{$rsOuter->fields['state']}'
							Limit 1";			
				$rsInner =  $this->db->Execute($sql); 				
				
				$rs = $this->db->Execute("Update cities_us SET elevation = '{$rsInner->fields['ELEVATION']}' WHERE id ='{$rsOuter->fields['id']}' LIMIT 1");
			
			$rsOuter->MoveNext();
			}
		
		
		
	}
	
	public function elevationWebService()
	{
			//http://gisdata.usgs.gov/xmlwebservices2/elevation_service.asmx/getElevation?X_Value=string&Y_Value=string&Elevation_Units=string&Source_Layer=string&Elevation_Only=string HTTP/1.1
			// X= Longitude, Y= Latitude, Elevation Units= FEET, Source_Layer=-1 (Best Available data at this point), Elevation_Only=0 (Means only Elevation is returned
			$rsOuter = $this->db->Execute("Select id,city,state,latitude,longitude FROM zipcode Where elevation=0 AND population Between 25000 AND 50000 Limit 0,999999");
			
			
			while (!$rsOuter->EOF) 
			{
				$this->v->url = 	'http://gisdata.usgs.gov/xmlwebservices2/elevation_service.asmx/getElevation?'.
							'X_Value='.$rsOuter->fields['longitude'].
							'&Y_Value='.$rsOuter->fields['latitude'].
							'&Elevation_Units=FEET'.
							'&Source_Layer=-1'.
							'&Elevation_Only=0';
				
				
				//Send the URL to the elevation web service
				$this->v->elevation = $this->curl_get_file_contents($this->v);

				
				//Strip the XML tags from the returned string
				$this->v->elevation = trim(strip_tags($this->v->elevation));
				
				//$xml = simplexml_load_string(strval($this->v->elevation));
				//echo "double element is: ".$xml->double[0];
				//var_dump($this->v->elevation); die();
				
				
				// Insert the Elevation figure into the database
				$rsInner = $this->db->Execute("Update zipcode SET elevation = '{$this->v->elevation}' WHERE id ='{$rsOuter->fields['id']}' LIMIT 1");
			
				
				//sleep(10);
				$rsOuter->MoveNext();
			}
		
		
	}
	
	
	
	//-----------private functions------------------------------
	private function queryGPSbyCityState($v,$db)
	{
		
		
		$sql = "Select latitude,longitude 
				From zipcode 
				Where city= '{$v->city}' 
				AND state= '{$v->state}' 
				Limit 1";
		
		$rs = $db->Execute($sql);
		
		while (!$rs->EOF) 
		{
		  $v->lat=$rs->fields['latitude']; 
		  $v->lon=$rs->fields['longitude'];
		  //print $rs->fields['latitude'].', '.$rs->fields['longitude'].'<BR>';
		  $rs->MoveNext();
		}
	
		return $v;
	}
	

	
	private function webScraper($url,$scrape)
	{
		
		
		//Load the remote Nasa Insolation Page into Dom and then traverse with Xpath
		$html = $this->curl_get_file_contents($url);
		$doc = new DOMDocument();
		$doc->preserveWhiteSpace = false;
		$doc->loadHTML($html);
		$xpath = new DOMXpath($doc);
		$elements = $xpath->evaluate($scrape); //Gets  Mean Temperatures
		
		//Traverse through the found node and get the data
		if (!is_null($elements)) {
  			foreach ($elements as $element) {
   				// echo "<br/>[". $element->nodeName. "]";
   				 $nodes = $element->childNodes;
    				foreach ($nodes as $node) {
      					$result[]= $node->nodeValue;					
    				}
  			}
		}
		return $result;	
		
	}
	
	private function updateInsolation($v,$db)
	{
		
		// Store Insolation Value for that city
		$sql = "Update cities_us 
				SET insolation='{$v->insolation}' 
				WHERE id = '{$v->id}'";
		
	
		$rs = $db->Execute($sql);
		if($rs) return true;
	}
	
	
	private function curl_get_file_contents($url)
	{		
		
			//$fh = fopen('/tmp/curl.txt','w') or die($php_errormsg);
			$c = curl_init();
	        curl_setopt($c, CURLOPT_URL, $url);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			//curl_setopt($c,CURLOPT_AUTOREFERER,1);
	        curl_setopt($c,  CURLOPT_USERAGENT, $this->v->userAgent);
	        curl_setopt($c,CURLOPT_TIMEOUT,15); 
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
	
}
	/*	
	echo 	"<h2>Monthly Averaged Insolation Incident On A Horizontal Surface</h2>
	      	Insolation for {$v->city},{$v->state} 
	      	at {$v->lat}&deg; Latitude 
	      	by {$v->lon}&deg; Longitude 
	      	is: <b>".$node->nodeValue.'<b> 
	      	kW/m<sup>2</sup>/day';
	*/
?>
