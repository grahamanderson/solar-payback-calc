<?php
error_reporting('E_ERROR');
/*
authored by Josh Fraser (www.joshfraser.com)
released under Apache License 2.0
*/
// a little example that fetches a bunch of sites in parallel and echos the page title and response info for each request
require("./myRollingCurl.php");

$t=new Test();

$urls=$t->buildArray();
$rc = $t->rollingCurl($urls);



class Test {

	public $rc,$callback;

	
	function __construct()
	{
		
  		//var_dump(get_class_methods ($this));

	}
	
	public function buildArray()
	{

	// top 20 sites according to alexa (11/5/09)
	$urls = array("http://www.google.com",
	              "http://www.facebook.com",
	              "http://www.yahoo.com",
	              "http://www.youtube.com",
	              "http://www.live.com",
	              "http://www.wikipedia.com",
	              "http://www.blogger.com",
	              "http://www.msn.com"
	            );	
	return $urls;
	
	}
	
	
	public function rollingCurl($urls)
	{	
		
		
		$callback = '$this->attributeHTMLScraper($output,$info,"/html/body//a")';
		$this->rc =  new myRollingCurl($callback,$this);
		
		$this->rc->window_size = 20;
		
		foreach ($urls as $url):
		    $request = new Request($url);
		    $this->rc->add($request);
		endforeach;
		
		$this->rc->execute();
	}
	
	public function sayHello($msg)
	{		
		echo $msg;
	}

	//public function attributeHTMLScraper($array)
	public function attributeHTMLScraper($response,$info,$scrape='/html/body//a')
		{
			
			//echo "response is: ".$response; die();
			//echo $scrape; die();
			//var_dump($response); var_dump($info);
			//$html = $this->curl_get_file_contents($url);
			$dom = new DOMDocument();
			$dom->loadHTML($response);
			

			$xpath = new DOMXPath($dom);
			$hrefs = $xpath->evaluate($scrape);
			
			 
			if(!is_null($hrefs)):
				for ($i = 0; $i < $hrefs->length; $i++):
					$href = $hrefs->item($i);
					$url = $href->getAttribute('href');
					$result[] =$url;
				endfor;
			endif;
			
			//die(var_dump($result));
			
			//Return a simple variable if 1 value is returned. Else return an array
			if(count($result)==1):
				return $result[0];
			else:
				return $result;	
			endif;
					
			
			return $result;
		}

		public function rcCallback()
		{
			
		}
	

}
?>