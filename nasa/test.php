<?php

!isset($_GET['lat'])? $req['lat']=36 : $req['lat']=$_GET['lat'];
!isset($_GET['lon'])? $req['lon']=-119 : $req['lon']=$_GET['lon'];




$req['userAgent']="Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)";
$req['nameserver']= "http://eosweb.larc.nasa.gov";
$req['ip'] = "http://198.119.134.212";
$req['url'] = $req['ip']."/cgi-bin/sse/grid.cgi?email=grahama%40me.com&step=2&lat=".$req['lat']."&lon=".$req['lon']."&num=062127&p=grid_id&p=swvdwncook&p=swv_dwn&veg=17&hgt=+100&submit=Submit";

//Get the remote HTML Page
$html = curl_get_file_contents($req);

$doc = new DOMDocument();
$doc->preserveWhiteSpace = false;
$doc->loadHTML($html);
$xpath = new DOMXpath($doc);

//Get the Average Yearly Insolation from the web page
$elements = $xpath->evaluate("/html/body/div[6]/table/tr[2]/td[14]");


if (!is_null($elements)) {
  foreach ($elements as $element) {
    	$nodes = $element->childNodes;
    	foreach ($nodes as $node) {
      		echo $node->nodeValue;
      	}
	}
}



function curl_get_file_contents($req)
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, curlopt_useragent, $req['userAgent']);
        curl_setopt($c, CURLOPT_URL, $req['url']);
        $contents = curl_exec($c);
        curl_close($c);

        if ($contents) return $contents;
            else return FALSE;
    }


?>
