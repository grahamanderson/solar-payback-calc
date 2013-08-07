<?php 
//Autoload script from: http://wp.drapeko.com/2009/03/28/autoloading-in-php/
define('DOC_ROOT',$_SERVER['DOCUMENT_ROOT']);


$v_dirs = array (
   array(
      'path' => DOC_ROOT.'/rand/php/class/adodb5/',
      'recursive' => true
   ),array(
      'path' => DOC_ROOT.'/rand/php/class/smarty/Smarty.class.php',
      'recursive' => true
   )
);

$autoload_list = array (
  'classes' => array (

  	'Manufacturers' => array ('path' => DOC_ROOT.'/rand/php/class/Manufacturers.php','extends' => array ('MyUtility')), 
	'MyUtility'=> array ('path' => DOC_ROOT.'/rand/php/class/MyUtility.php')),  
  	'myRollingCurl' => array ('path' => DOC_ROOT.'/rand/php/class/myRollingCurl'),
	'Noaa' => array ('path' => DOC_ROOT.'/rand/php/class/Noaa.php'),
	'Nasa' => array ('path' => DOC_ROOT.'/rand/php/class/Nasa.php'),
    'ADOConnection' => array ('path' => DOC_ROOT.'/rand/php/class/adodb5/adodb.inc.php'),
	'Smarty' => array ('path' => DOC_ROOT.'/rand/php/class/smarty/Smarty.class.php')
    

  );
?>