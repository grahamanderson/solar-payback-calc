<?php

require_once('./adodb5/adodb.inc.php');

!isset($_GET['lat'])? $v['lat']=36 : $v['lat']=$_GET['lat'];
!isset($_GET['lon'])? $v['lon']=-119 : $v['lon']=$_GET['lon'];
!isset($_GET['city'])? $v['city']='unknown' : $v['city']=$_GET['city'];
!isset($_GET['state'])? $v['state']='unknown' : $v['state']=$_GET['state'];



// Get Latitude and Longitude from Database
$db = ADONewConnection('mysql');
$db->debug = true;
$db->port=8889;
$db->Connect('localhost', 'grahama', 'musiccows', 'rand');

$sql = "Select latitude,longitude 
		From zipcode 
		Where city= '{$v['city']}' 
		AND state= '{$v['state']}' 
		Limit 1";

$rs = $db->Execute($sql);

while (!$rs->EOF) 
{
  $v['lat']=$rs->fields['latitude']; 
  $v['lon']=$rs->fields['longitude'];
  //print $rs->fields['latitude'].', '.$rs->fields['longitude'].'<BR>';
  $rs->MoveNext();
}
          






//Construct query String to send to Nasa
$v['userAgent']="Googlebot/2.1 (+http://www.google.com/bot.html)";
$v['nameserver']= "http://eosweb.larc.nasa.gov";
$v['ip'] = "http://198.119.134.212";
$v['url'] = $v['ip']."/cgi-bin/sse/grid.cgi?email=grahama%40me.com&step=2&lat=".$v['lat']."&lon=".$v['lon']."&num=062127&p=grid_id&p=swvdwncook&p=swv_dwn&veg=17&hgt=+100&submit=Submit";



//Load the remote Nasa Insolation Page into Dom and then traverse with Xpath
$html = curl_get_file_contents($v);

$doc = new DOMDocument();
$doc->preserveWhiteSpace = false;
$doc->loadHTML($html);
$xpath = new DOMXpath($doc);

//Use Xpath to get the td cell "Average Yearly Insolation"
$elements = $xpath->evaluate("/html/body/div[6]/table/tr[2]/td[14]");


if (!is_null($elements)) {
  foreach ($elements as $element) {
    	$nodes = $element->childNodes;
    	foreach ($nodes as $node) {
      		echo 	"<h2>Monthly Averaged Insolation Incident On A Horizontal Surface</h2>
      				Insolation for {$v['city']},{$v['state']} 
      				at {$v['lat']}&deg; Latitude 
      				by {$v['lon']}&deg; Longitude 
      				is: <b>".$node->nodeValue.'<b> 
      				kW/m<sup>2</sup>/day';
      				
      	}
	}
}

// Store Insolation Value
$sql = "Update zipcode 
		SET insolation='{$node->nodeValue}' 
		WHERE city = '{$v['city']}' AND state='{$v['state']}'";

$rs = $db->Execute($sql);


function curl_get_file_contents($v)
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c,  CURLOPT_USERAGENT, $v['userAgent']);
        curl_setopt($c, CURLOPT_URL, $v['url']);
        $contents = curl_exec($c);
        curl_close($c);

        if ($contents) return $contents;
            else return FALSE;
    }


?>
