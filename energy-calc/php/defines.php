<?php
	if (!defined('SITE_IN')) die('Direct access not allowed!');
	//Make xdebug dump everything
	ini_set('xdebug.var_display_max_children', 9000 );
	
	define('DOC_ROOT',$_SERVER['DOCUMENT_ROOT']);
	define('ARC_BTU_DAILY_AVG_OUTPUT',40000); 
	define('SAN_ANTONIO_INSOLATION',4.65);
	
	define('NASA_DOMAIN_IP','http://198.119.134.212');
	define('NASA_DOMAIN_URL','http://eosweb.larc.nasa.gov');
	
	
	define('DB_USER','grahama');
	define('DB_PASS','musiccows');
	define('DB_TYPE','mysql');
	define('DB_HOST','localhost');
	define('DB_NAME','rand');
	define('DB_PORT','8889');
	define('DB_MYDEBUG','false');
	
	define('CURL_USERAGENT',  'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
	
	define('PDFTOTEXT_LOC','/opt/local/bin/pdftotext');
	

?>