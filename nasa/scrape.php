<?php
$userAgent = "Googlebot/2.1 (http://www.googlebot.com/bot.html)";
$lat = 36;
$long = -119;
$target_url = "http://eosweb.larc.nasa.gov/cgi-bin/sse/grid.cgi?email=grahama%40me.com&step=2&lat=".$lat."&lon=".$long."&num=062127&p=grid_id&p=swvdwncook&p=swv_dwn&veg=17&hgt=+100&submit=Submit";

$ch = curl_init();
curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
curl_setopt($ch, CURLOPT_URL,$target_url);
curl_setopt($ch, CURLOPT_FAILONERROR, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$html = curl_exec($ch);
if (!$html) {
	echo "
cURL error number:" .curl_errno($ch);
	echo "
cURL error:" . curl_error($ch);
	exit;
}

$dom = new DOMDocument();
@$dom->loadHTML($html);

$xpath = new DOMXPath($dom);
$td = $xpath->evaluate("/html/body/table//td");

for ($i = 0; $i < $hrefs->length; $i++) {
	$href = $td->item($i);
	$url = $td->getAttribute('td');
	storeLink($url,$target_url);
}

print_r($url);

?>

