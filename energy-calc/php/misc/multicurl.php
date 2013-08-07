<?php
 
$urls=array(
'http://f.askapache.com/mp3/12-lessons-for-those-afraid-of-css.mp3',
'http://f.askapache.com/mp3/27-request-methods-for-use-with-apache-and-rewritecond-and-htaccess.mp3',
'http://f.askapache.com/mp3/301-redirect-with-mod_rewrite-or-redirectmatch.mp3',
'http://f.askapache.com/mp3/404-errorpages.mp3',
'http://f.askapache.com/mp3/503-service-temporarily-unavailable.mp3',
'http://f.askapache.com/mp3/adsense-robots.mp3',
'http://f.askapache.com/mp3/alexa-toolbar-firefox.mp3',
'http://f.askapache.com/mp3/allowing-access-from-1-static-ip-and-deny-the-rest.mp3',
'http://f.askapache.com/mp3/apache-authentication-in-htaccess.mp3');
 
$save_to='/home/user/htdocs/mp3/';
 
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
?>