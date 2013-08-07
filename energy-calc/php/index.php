<?php
error_reporting('E_ALL');
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
define('SITE_IN', 1);  include('./defines.php');


/*
	In the URL, you would enter http://rand/php/index.php?510 constitution avenue Fort Snelling, MN
	This will print out the array of $result variables
*/

$nasa = new Nasa();
//$result = $nasa->getInsolationByAddress('Saana,Yemen',$format='csv');
//$result = $nasa->getInsolationByAddress('510 constitution avenue Fort Snelling, MN',$format='csv');
//$result = $nasa->getInsolationByAddress('601 Nevada Way, Boulder City, Nevada 89005',$format='csv');

//die(($_SERVER['QUERY_STRING']));


$result = $nasa->getInsolationByAddress(urldecode($_SERVER['QUERY_STRING']),$format='csv');

echo 'The projection for '.urldecode($_SERVER['QUERY_STRING']). ' is <b>' .$result['projections']['BtuPerDay']['numberOnly'].'</b> BTU/day/collector.';  
die(var_dump($result));

//$noaa = new Noaa();
//$result = $noaa->processCCD('/Users/robert/Sites/noaa/climatenormals/ccd/',$limit=999999,$truncate=FALSE);

#$result = $noaa->addLatLonToCCD($field='name',$table='ccdTempNormal',$limit=9999);


//echo "heelo";

//$m = new Manufacturers();
//$m->Armstrong();
/*$m->htmlScraper($url = "http://localhost/rand/html/ResourceLibraryC0.html",
				$scrape=array(	'href'=>"/html/body//div[@class='view-content view-content-Resource-Library']//a",
								'node'=>"/html/body//div[@class='view-content view-content-Resource-Library']//td[@class='view-field view-field-node-title']")
				);
*/
//$m->Pvi();

//$result = $n->getInsolationByAddress('San Marcos, TX',$format='csv');
//$result = $noaa->addLatLonToCCD($field='name',$table='ccdWindspeedNormal',$limit=9999);

//die(var_dump($result));


//echo "insolation values for Saana, Yemen are: ".$insolation. 'kWh/m2/day';



function __autoload($className){
	
	require_once (DOC_ROOT.'/rand/php/class/adodb5/adodb.inc.php');
	require_once (DOC_ROOT.'/rand/php/class/MyUtility.php');
	
	if(is_file(DOC_ROOT.'/rand/php/class/'.$className.'.php')):
		require_once (DOC_ROOT.'/rand/php/class/'.$className.'.php');

	elseif($className=='ADODB'): 
		require_once (DOC_ROOT.'/rand/php/class/adodb5/adodb.inc.php');
	
	elseif(is_file(DOC_ROOT.'/rand/php/class/smarty/'.$className.'.php')):
		require_once (DOC_ROOT.'/rand/php/class/smarty/'.$className.'.php');
	endif;
	
	
}



?>
