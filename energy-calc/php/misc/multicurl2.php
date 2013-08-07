
<?php



	function multicurl($urls){

	 
		$curls 		= array();
		$result 	= array();
	 
		$mh 		= curl_multi_init();
	 
			foreach ($urls as $id) { 
		
		    	$curls[$id] = curl_init(); 
				curl_setopt($curls[$id], CURLOPT_URL, $id);
			    curl_setopt($curls[$id], CURLOPT_RETURNTRANSFER, 1);
			    curl_multi_add_handle($mh, $curls[$id]);
		
			}
	 
	  	$running = null;
	 
	  	do { curl_multi_exec($mh, $running); } while($running > 0);
	 
	
			foreach ($curls as $id => $c) {
		
			    $result[$id] = curl_multi_getcontent($c);
			    curl_multi_remove_handle($mh, $c);
		
			}
	
		curl_multi_close($mh);
	
	return $result;


	}



	function saveimage($url, $name){

		$dir = dirname(__FILE__).'/';

		$name = str_replace(" ", "_", $name);

		$image = file_get_contents($url);

		file_put_contents(''.$dir.'images/'.$name.'.jpg', $image);

	return true;

	
	}

	function pagecount($page){
	

		$count =  preg_match_all('#href="videos\?c=.*&pt=thumbs&o=new&page=(.*)"#', $page, $out);

			if($count > 2 ){
	
				$pages = $out[1][$count-2];
	
			}else{
	
				$pages = 2;
	
			}

	return $pages;

	}




	function homepagecount($page){
	

		$count =  preg_match_all('#href="index\?page=(.*)"#', $page, $out);

			if($count > 2 ){
	
				$pages = $out[1][$count-2];
	
			}else{
	
				$pages = 2;
	
			}

	return $pages;

	}





	function thumbs($page){

		
		preg_match_all('#<td colspan="3" bgcolor="\#E2E2E2">(.*)<\/td>#Uis', $page, $out);


		foreach($out[0] as $item){


			preg_match('#<a href="(.*)"><img src="(.*)"#Uis', $item, $go);

			$array[] = "$go[1]|$go[2]";


		}

	return $array;


	}





	function parsemovies($pages, $image){


		$c = "0";

		foreach($pages as $key => $detailpage){

			$table1 = mysql_fetch_array(mysql_query("SELECT id FROM `table1` WHERE `url` = '$key'"));

			preg_match('#<h1 style="display\:inline\;">(.*)<\/h1>#', $detailpage, $title);
			preg_match('#<object.*<\/object>#Us', $detailpage, $object);
			preg_match('#href="(.*)">fullscreen<\/a>#', $detailpage, $fullscreenurl);
			preg_match('#\[imdb\]<\/a>.*<br\/>(.*)<div>#Uis', $detailpage, $description);

			$title 	= str_replace("'", "", $title[1]);
			$urlid 	= $table1['id'];
	
				if(isset($description[1])){
					$descr 	= trim(str_replace("'", "", $description[1]));
				}else{
					$descr 	= '';
				}

				$add_data = mysql_query("INSERT INTO `table2` (id, id_url, title, embedded, description, category, name_tv_shows, fullscreen, image ) VALUES ('', '$urlid', '$title', '$object[0]', '$descr', 'Movie', '', '$fullscreenurl[1]', '$image[$c]')");

					if($add_data){

//						saveimage($image[$c], $title);

						$pars_done 	= mysql_query("UPDATE `table1` SET status_pars = '1' WHERE `id` = '$urlid'");

					}

			$c++;
		}

	return true;
	

	}




	function startparsemovies($pages){


		$count	= '0';
	
		for($i=1;$i<$pages;$i++){
	
	
			if(file_exists("stop.txt"))	break;
			if($count > 50) break;
	
			$page 		= file_get_contents("http://quicksilverscreen.com/videos?c=2&pt=thumbs&o=new&page=$i");
	
			$thumbs 	= thumbs($page);
			$urls 		= array();
			$image		= array();
			
				foreach($thumbs as $item){
	
					$get_url_and_image 	= explode("|", $item);
					$url 				= "http://quicksilverscreen.com/$get_url_and_image[0]";
	
					$query 				= mysql_num_rows(mysql_query("SELECT url FROM `table1` WHERE `url` = '$url'"));
					$query2 			= mysql_num_rows(mysql_query("SELECT status_pars FROM `table1` WHERE `url` = '$url' and `status_pars` = '1'"));
						
						if($query == 0){
	
							$ready_to_pars 		= mysql_query("INSERT INTO `table1` VALUES ('', '$url', '')");
	
						}
	
						if($query2 == 0){
			
							$urls[] 			= $url;
							$image[]			= $get_url_and_image[1];
	
						}else{
	
						$count++;
	
						}
	
				}
	
	
				if(isset($urls)){
		
					$detailpages = multicurl($urls);
			
					parsemovies($detailpages, $image);
		
				}
	
		}

	return true;


	}




	function thumbstvshow($page){

		
		preg_match_all('#<td colspan="3" bgcolor="\#E2E2E2">(.*)<\/td>#Uis', $page, $out);


		foreach($out[0] as $item){


			preg_match('#<a href="(.*)"><img src="(.*)"#Uis', $item, $go);

			$url = str_replace("watch?video", "http://quicksilverscreen.com/watch?video", $go[1]);

			$array[$url] = "$go[2]";


		}

	return $array;


	}




	function getshowthumbs($page){


		preg_match('#<td valign="top" width="400px">(.*)<\/table>#Uis', $page, $getlink);

		preg_match_all('#<a href="(videos\?c=.*)">(.*)#', $getlink[1], $links);
		
		$links = str_replace("videos", "http://quicksilverscreen.com/videos", $links[1]);

	return $links;


	}



	function parsetvshow($pages, $image, $showname){


		$c = "0";

		foreach($pages as $key => $detailpage){

			$table1 = mysql_fetch_array(mysql_query("SELECT id FROM `table1` WHERE `url` = '$key'"));

			preg_match('#<h1 style="display\:inline\;">(.*)<\/h1>#', $detailpage, $title);
			preg_match('#<object.*<\/object>#Us', $detailpage, $object);
			preg_match('#href="(.*)">fullscreen<\/a>#', $detailpage, $fullscreenurl);
			preg_match('#<\/h1>.*<br\/>.*(.*)<div>#Uis', $detailpage, $description);

			$title 	= str_replace("'", "", $title[1]);
			$urlid 	= $table1['id'];

				if(isset($description[1])){
					$descr 	= trim(str_replace("'", "", $description[1]));
				}else{
					$descr 	= '';
				}


				$add_data = mysql_query("INSERT INTO `table2` (id, id_url, title, embedded, description, category, name_tv_shows, fullscreen, image ) VALUES ('', '$urlid', '$showname', '$object[0]', '$descr', 'TVShows', '$title', '$fullscreenurl[1]', '$image[$c]')");

					if($add_data){

//						saveimage($image[$c], $title);

						$pars_done 	= mysql_query("UPDATE `table1` SET status_pars = '1' WHERE `id` = '$urlid'");

					}

			$c++;
		}

	return true;


	}



	function startparsetvshow($linktothumbs){


		foreach($linktothumbs as $item){
	
			$getthumbs 		= file_get_contents($item);
	
			$pages 			=  pagecount($getthumbs);
	
			for($i=1;$i<$pages;$i++){
	
				if(file_exists("stop.txt"))	break;
	
	
				$thumbspage 	= file_get_contents("$item&page=$i");	
				preg_match('#<p><b>.*"(.*)"#',$thumbspage, $showname);
				$showname 	= trim($showname[1]);
				$thumbs 	= thumbstvshow($thumbspage);
				$urls 		= array();
				$img		= array();
		
					
						foreach($thumbs as $url => $image){
			
							$query 				= mysql_num_rows(mysql_query("SELECT url FROM `table1` WHERE `url` = '$url'"));
							$query2 			= mysql_num_rows(mysql_query("SELECT status_pars FROM `table1` WHERE `url` = '$url' and `status_pars` = '1'"));
								
								if($query == 0){
			
									$ready_to_pars 		= mysql_query("INSERT INTO `table1` VALUES ('', '$url', '')");
			
								}
			
								if($query2 == 0){
					
									$urls[]	= $url;
									$img[]	= $image;
			
								}
			
						}
		
				if(isset($urls)){
	
					$detailpages = multicurl($urls);
			
					parsetvshow($detailpages, $img, $showname);
	
				}
	
			}
		
		}


	return true;


	}




	function startparsehome($pages){


		$count	= '0';
	
		for($i=1;$i<$pages;$i++){
	
			if(file_exists("stop.txt"))	break;
			if($count > 50) break;
	
			$page 		= file_get_contents("http://quicksilverscreen.com/index?page=$i");
	
			$thumbs 	= thumbs($page);
			$urls 		= array();
			$image		= array();
			
				foreach($thumbs as $item){
	
					$get_url_and_image 	= explode("|", $item);
					$url 				= "http://quicksilverscreen.com/$get_url_and_image[0]";
	
					$query 				= mysql_num_rows(mysql_query("SELECT url FROM `table1` WHERE `url` = '$url'"));
					$query2 			= mysql_num_rows(mysql_query("SELECT status_pars FROM `table1` WHERE `url` = '$url' and `status_pars` = '1'"));
						
						if($query == 0){
	
							$ready_to_pars 		= mysql_query("INSERT INTO `table1` VALUES ('', '$url', '')");
	
						}
	
						if($query2 == 0){
			
							$urls[] 			= $url;
							$image[]			= $get_url_and_image[1];
	
						}else{
	
						$count++;
	
						}
	
				}
	
	
				if(isset($urls)){
		
					$detailpages = multicurl($urls);
			
					parsemovies($detailpages, $image);
		
				}
	
		}

	return true;


	}



?>
