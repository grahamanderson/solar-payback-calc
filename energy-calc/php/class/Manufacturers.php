<?php

Class Manufacturers extends MyUtility
{
	 //Intitialize variable, database, error objects
	public  $v,$db,$e,$truncate,$rc;

	
	

	function __construct()
	{		
		parent::__construct();

		
		// Set up database
		$db = ADONewConnection('mysql');
		$db->debug = true;
		$db->port=8889;
		$db->Connect('localhost', 'grahama', 'musiccows', 'rand');
		$this->db = $db;
		
		
		#Arrays for Armonstrong
		$this->v->titles=array();
		$this->v->hrefs=array();

	}
	

	public function sayHello()
	{
		echo "say hello";
	}
	
	
	
	public function Armstrong()
	{
			//die();
			# Get the last Page of Relevant Armostrong Product. Specficially Mixing valves
			$this->v->lastPage=$this->lastPageIndex(		$url='http://www.armstronginternational.com/resource-library?page=0%2C0&filter0[0]=1213&filter1=%2A%2AALL%2A%2A&filter2=&filter3=',
															$xpath="/html/body//a[@title='Go to last page']", 	//Search for this particular anchor tag in the qbove url
															$searchString='/page=0%2C(\d{1,2})/'				//In the found xpath match, search for the actual page paramter after page=%2C[here]
															);				
			#For Testing Only
			$this->v->lastPage=1;
								
			
			# Build url list
			$this->v->urls = $this->buildURLList($this->v->lastPage);
			
			
			# Pass all the urls into RollingCurl
			$rc = $this->rc->rollingCurl($this->v->urls);
			
			
			# Remove the Parent Index from each array so its a long list
			$this->v->titles = $this->flattenArray($this->v->titles);
			$this->v->hrefs = $this->flattenArray($this->v->hrefs);

			var_dump($this->v);
			var_dump($this->e);
			
			#Merge the two arrays for mysql insertion statement
			$this->v->insert=$this->mergeTwoArrays($this->v->titles,$this->v->hrefs);
			
			#Insert into Database and store the converted text into the database for searching
			
			# Store files locally with curl or wget
			
			# Make thumbnails with Imageick
			
			# Make Interface in iui for iPhone/Web
			
	}
	


	
	public function Pvi()
	{
		# wget all PVI urls
		
		# Recurse through them wih Directory Iterator
		$manufacturer = 'PVI';
		$files= $this->getFiles(	$path='/Users/robert/Dropbox/Rand/Manufacturers/PVI/',
									$filter=array('pdf','doc'),
									$exclude='Price Lists'/*,'9.pdf','17.pdf','18.pdf','19.pdf', '413.pdf','414.pdf'*/);
	
		
		$count=0; $limit=10;
		
		foreach ($files AS $key=>$value):	
				
				$extension = $this->getFileExtension($key);
				$originalFileName = $value;
				
				if($extension=='pdf'):
					# Convert the pdf to text with pdftotext command line utility
					$textDump= $this->pdftotext($value,$saveTo='');
				else:
					$textDump = $this->antiword($value,$saveTo='');
				endif;
				
				
				# Override for now
				$path = '/Users/robert/Dropbox/Rand/Manufacturers/PVI/Products/';
				$file = '6328.pdf';
				$value = $path.$file;
				$key=$file;
				
				 
				$textDump= $this->pdftotext($value,$saveTo='');
				$textDump = $this->cleanString($textDump);
				$lines = preg_split('/[\r\n]+/',$textDump); # SPlit into array
				
				$fileName = $key;
				$path = $value;
				
																									

				
				#----------------------------------------TURBOPOWER-------------------------------------------------------------------------------------------------------------------------
				if(preg_match('/START-UP[ ]+REPORT[ ]+[\r\n]/',$textDump)):										#  Start-Up report
					$category="startupReport";
						
				elseif(preg_match('/START-UP[ ]OF[ ]+[A-Z-\s]*[ ]+WATER[ ]+HEATERS/',$textDump)):				# START-UP OF OIL-FIRED WATER HEATERS [new line]
					$category = 'startup'; $docType= 1;
				elseif(preg_match('/TURBOPOWER[ ]BOILERS[ ]*[\r\n][ ]+AIR[ ]+INTAKE[ ]+ASSEMBLY/',$textDump)):	# TURBOPOWER BOILERS [line break]AIR INTAKE ASSEMBLY
					$docType = 2;
				
				elseif(preg_match('/TURBOPOWER[ ]+OIL[ ]+PACKAGED[ ]WATER[ ]HEATER/',$textDump)):				# TURBOPOWER OIL PACKAGED WATER HEATER								
					$category = 'Turbopower';
					if(preg_match('/\bA\b[ ]+\bB\b[ ]+\bJ\b[ ]+\bK\b/[ ]+\bHP\b/',$textDump)) :  				# A B J K HP
						$docType=3;
					elseif(preg_match('/\bA\b[ ]+\bB\b[ ]+\bJ\b[ ]+\bHP\b/',$textDump)) :						# A B J HP
						$docType=4;
					endif;
						
				elseif(preg_match('/TURBOPOWER[ ]+GAS\/OIL[ ]+PACKAGED[ ]WATER[ ]HEATER/',$textDump)):			# TURBOPOWER GAS/OIL PACKAGED WATER HEATER								
					
					$category = 'Turbopower';
					if(preg_match('/\bA\b[ ]+\bB\b[ ]+\bC\b[ ]+\bJ\b/[ ]+\bHP\b/',$textDump)) :  				# A B J K HP
						$docType=5;
					elseif(preg_match('/\bA\b[ ]+\bB\b[ ]+\bHP\b/',$textDump)) :								# A B HP
						$docType=6;
					endif;
				
				
				#----------------------------------------DURAWATT-------------------------------------------------------------------------------------------------------------------------
					
				elseif(preg_match('/DURAWATT[ ]+ELECTRIC/',$textDump)):											
					$category = 'Durawatt'; $docType=7;	
				
				elseif(preg_match('/STANDARD[ ]+FEATURES[ ]AND[ ]+EQUIPMENT/',$textDump)):						# STANDARD FEATURES AND EQUIPMENT
					$docType = 8;
					# Will Divide this one too
				
				elseif(preg_match('/SPECIFICATION[ ]+FOR[ ]+[A-Z-\s]*[\r\n]/',$textDump)):						# SPECIFICATION FOR
					$docType = 9;	
					# Will Need to Divide this one too
				
				
				#----------------------------------------MAXIM-------------------------------------------------------------------------------------------------------------------------
				elseif(preg_match('/MAXIM[ ]+PACKAGED[ ]+OIL[ ]+WATER[ ]+HEATER[ ]*[\r\n]/',$textDump)):								
					$category = 'Maxim';
					$docType = 10;	

				
				#----------------------------------------ISX-------------------------------------------------------------------------------------------------------------------------
				elseif(preg_match('/\bISX\b/',$lines[0])):																				 
					$product ='ISX';
				
					if(preg_match('/ORDERING[ ]+INFORMATION/',$textDump)):																# Order Form
						$subCat = 'orderForm';

					elseif(preg_match('/Standard[ ]+and[ ]+Optional[ ]+Equipment/',$textDump)):											# Standard Optional Features List
						$subCat = 'standardOptional';

					elseif(preg_match('/START-UP[ ]+REPORT/',$textDump)):																# startUp Report (user input)	
						$subCat='startupReport';
					
					
					#Configuration---------------
																	
					elseif(preg_match('/Product[\s]+Configuration,[\s]+Steam[\s]+Valve[\s]+Size,[\s]+and[\s]+Condensate[\s]+Trap[\s]+Size/',$lines[0])):												

						# Could Get the First Sub Page
						$result = $this->extractRecordsFromTable2(	
											
								$manufacturer='PVI',
								$product,
								$docType='configuration',																																
								$titleLoc = 0,
								$descriptionLoc = array(1,2),
								$textDump,$lines,
								$path,
								$fileName,
								#--------------------------------------------------------------------------------------------------------------
								$fldHeaders=array('gpm',		'productConfiguration',	'steamValve',			'condensationTrap'),
								$pattern=	'([0-9]{1,2})[\s]+ ([A-Z]{1}[0-9]{1})[\s]+ 	([\*0-9\/-]{1,6})[ ]+	([\*0-9\/-]{1,6})[\s]*',		
								#--------------------------------------------------------------------------------------------------------------	
								
																	
								#-Extra for parameters for sub pages and pattern repeats, etc---------------------------------------------------
								$patternRepeat=7,
								$subPageNamePattern = 	'/[0-9]{3}[\s]+F[\s]+Outlet[\s]+Water/i',						
								$subPageSplitPattern	= '/(?<!Various)[\s]+Supply[\s]+Steam[\s]+Pressure/i',
								$fieldHeaderSubCategories=array(5,10,15,30,50,100,150)

																	
																);											
	
					endif;
						/*	

					# Specification---------------
					elseif(preg_match_all(	'/[A-Z][0-9][ ]+'.																			# Product Configuration
											'[0-9]{1,3}[ ]+'.																			# C Overall Width
											'[0-9]{1,3}[ ]+'.																			# D Overall Height
											'[0-9]{1,3}[ ]+'.																			# E Overall Depth
											'[0-9]{1,3}[ ]+'.																			# F Length of Skid
											'[0-9]{1,2}/',																				# G Heat Exchanger Steam Connection ANSI 150#
											'[0-9]{2,4}',																				# Approx. Shipping Weight (lbs)																			
											$textDump, $matches, PREG_SET_ORDER)):		
						$subCat='specification';
						
					endif;
					*/
																
					elseif(strstr($lines[0],'ISX Instantaneous Steam-fired Water Heater')):												
				
									#Product Config		# C Overall Width	# D Overall Height	# E Overall Depth	# F Length of Skid	# G Heat Exchanger  #Shipping Weight
						$pattern = '/([A-Z][0-9]) [\s]+ ([0-9]{1,3}) [\s]+ 	([0-9]{1,3}) [\s]+ 	([0-9]{1,3}) [\s]+ ([0-9]{1,3}) [\s]+ 	([0-9]{1,3}) [\s]+	([0-9]{2,4})/x';
						
						# Could Get the First Sub Page
						$result = $this->extractRecordsFromTable(	
																	$manufacturer='PVI',
																	$product,$docType='specification',
																	$subPageTitles= NULL,														
																	$titleLoc = 0,
																	$descriptionLoc = NULL,
																	$textDump,$lines,
																	$path,
																	$fileName,
																	$pattern,	
																	$patternRepeat=NULL,
																	$fieldHeadersCategories=array('prodConfig','cWidth','dHeight','eDepth','fSkid','gHeatExch','shippingWeight'),
																	$fieldHeaderSubCategories=NULL
																	
															);			
			
					die(var_dump($result));
				
				#----------------------------------------CSX-------------------------------------------------------------------------------------------------------------------------
					
				elseif(preg_match('/\bCSX\b/',$textDump)):																				
					$category='CSX';

					
					if(preg_match('/CSX[ ]+Instantaneous[ ]+Steam-to-Water[ ]+Heater[ ]*[\r\n]/',$textDump)):							#Brochure
						$subCat='brochure';
					
					elseif(preg_match(	'/([A-Z][0-9])[ ]+'.																			# Product Configuration
										'([0-9]{1,3})[ ]+'.																				# C Overall Width
										'([0-9]{1,3})[ ]+'.																				# D Overall Height
										'([0-9]{1,3})[ ]+'.																				# E Overall Depth
										'([0-9]{1,3})[ ]+'.																				# F Skid Spacing
										'([0-9]{1,2})',																					# G Heat Exchanger Steam Connection ANSI 150#
										'([0-9]{2,4})',																					# Approx. Shipping Weight (lbs)
										$textDump,$matches,PREG_SET_ORDER)):	
						$subCat='specification';
						
					
					elseif(preg_match('/SPECIFICATION[ ]+FOR[ ]+INSTANTANEOUS[ ]+STEAM/')):												# specForm (user input)
						$subcat='specForm';	
						
					
					elseif(preg_match_all(	'/(CSX-[0-9]{2,3}-[0-9]{1,3}-[0-9]{1,3})[ ]+'.												# Model Number
											'([0-9]{2,3})[ ]+'.																			# Domestic Water Temperature °F
											'([0-9]{1,3})[ ]+'.																			# Domestic Water Temperature Delivered (gpm)
											'([0-9]{1,3})[ ]+'.																			# Steam Pressure Supplied(psi)
											'([0-9]{1,3})[ ]+'.																			# MBtu/hr Input
											'([0-9]{1,3})[ ]+'.																			# Steam Flow in HX (lb /hr)
											'([A-Z][0-9])[ ]+'.																			# Product Configuration
											'([a-z-0-9-\/]*)[ ]+'.																		# Steam Valve
											'([0-9-\/])*/',																				# Condensate Trap NPT
											$textDump,$matches,PREG_SET_ORDER)):							
						$subCat = 'specification';
													
					elseif(preg_match('/START-UP[ ]+REPORT/',$textDump)):																# startUp Report (user input)	
						$subCat='startupReport';
						
					endif;

				endif;
				
				
				echo "category is: ".$category.'<br/>subcategory is: '.$subCat.'<br/>description is: '.$description.'<br/>';
				var_dump($records);
				die();
				
				# Convert the string to an array and trim it
				$doc = preg_split('/[\r\n]+/',$textDump);
				
				# Trim each item line in the array
				$doc = $this->cleanArray($doc);
				
				
				# What Doctype is it
				if(preg_match('/START-UP/',$doc[0])): 
					$docType =1;
				
				elseif(preg_match('/TURBOPOWER[ ]+OIL[ ]+PACKAGED[ ]+WATER[ ]HEATER/',$doc[0])):
					if(strstr($doc[2],'HORIZONTAL')):
						$docType=2;  #Horizontal ...adds the 'K' value to the pattern
					else:
						$docType =3;  #Vertical
					endif;
				
				elseif(preg_match('/QUICKDRAW[ ]+OIL[ ]+PACKAGED[ ]+WATER[ ]{1}HEATER/',$doc[0])):
					 $docType =3;
				
				elseif(preg_match('/TURBOPOWER[ ]BOILERS/',$doc[0] && preg_match('/AIR[ ]INTAKE[ ]ASSEMBLY/',$doc[1]))):
					$docType=4;
				
				/*elseif():*/
				endif;	 
				
				
				
				
				
				
				$sp = '[ ]+';	//One or more spaces												# Sample Numbers	mfgPvi Table Fields				
				$pattern = '/(^\b[\d]{1,4}\b'.$sp.'\b[\w]{1}\b'.$sp.'\b[A-Z-0-9]{4,12}\b)'.$sp.	# 250 N 175A-TPO 	=> modelNumber 
							'([0-9]{1,4})'.$sp.														# 250				=> 40-20
							'([0-9]{1,4})'.$sp.														# 200				=> 40-140
							'([0-9-,]{1,8})'.$sp.													# 199,000			=> input
							'([0-9-\.]{1,4})'.$sp.													# 1.4				=> gph
							'([0-9]{1,4})'.$sp.														# 27				=> a
							'([0-9]{1,3})'.$sp.														# 4					=> b
							'([0-9]{1,3})'.$sp.														# 17				=> j
							'([0-9-\/]{1,3})'.$sp.													# 1/3				=> hp
							'([0-9-\/]{1,10})'.$sp.													# 115/1/8			=> volt-ph-amps
							'([0-9]{1,5})/';            											# 1130				=> shippingWeight
			
				
				
				# Get a Title/Descript from the first 5 lines of the document array
				$description =  $this->extractFromRegex($doc,'/[A-Z-0-9 ]{3,999}/',$limit=5);
				
				# MYSQL Local Fields that are record specific
				$fieldHeaders = array('extractedFromDoc', 'modelNumber','40-20','40-140','input','gph','a','b','j','hp','volt-ph-amps','shippingWeight');
				
				# Global Fields that apply to every record in th PVI document
				$addFields = array('manufacturer'=>'PVI','description'=>$description,'textDump'=>substr($textDump,0,70).'...continued');
				
				
				$docRecords = $this->extractPVIDocType1($doc,$pattern,$fieldHeaders,$addFields,$limit);
				
				
		
	
				
				
				# SQL Insert DocRecord
	
				
				die(var_dump($docRecords));
			
				
				
				
	
		
							
				if(preg_match('/START-UP/',$lines[0],$m)):
					$this->removeSpecialChars($lines[0]);
					$insertSql[]=array('title'=>'NULL','description'=>$lines[0],'size'=>'NULL', 'fileName'=>$this->generateFileName($value,$extension),'originalFileName'=>$originalFileName);
	
				else:
					
					# Iterate through lines of text to find the first 3 lines containing the title
					foreach ($lines as $value3):
						
						# Match the line that DOES NOT contain the word boundry 'R'
						if(preg_match('/[ ]+[^\bR\b]/',$value3,$matches)):  
							$array[]=htmlentities($value3);

							
							# Got First 3 relevant lines: Title, Description, and Sizing
							if(count($array)>=3):

								
								# Is the Third Array Line Something like: 175 GALLONS.  If so Grab the '175', else set it to Null
								preg_match('/([\d]{1,4}) GALLONS/',$array[2],$matches)? $array[2]=$matches[1] : $array[2]='NULL';
								
								$array = $this->removeSpecialChars($array);
								
								//'title'=>'TURBO POWER BOILERS', 'description'=>'NICKELSHIELD NICKEL-PLATED STORAGE TANK', 'size'=>'TANK SERIES 175 GALLONS'
								$insertSql[]=array(	'manufacturer'=>$manufacturer,
													'productNumber'=>$this->removeFileExtension($originalFileName),
													'title'=>$array[0],'description'=>$array[1],'size'=>$array[2], 
													'fileName'=>$this->generateFileName($array,$extension),'originalFileName'=>$originalFileName,
													'textDump'=>$txt
													);
								
								unset($array);
								break;
								
							endif;	
						endif;
					
					endforeach;
				endif;
						
						/*

						*/
						//$result[$fileName] = $value;
						
						
				if($count>=$limit):
					break;
				else:
					$count++;
				endif;
				
				
		endforeach;

				
				
			
				
		
	
		die(var_dump($insertSql));

		
							
		
		# Take first 3 lines of PDf's, and the entire document as Text and insert the result into the database
		
		# Make thumnails of first page of pfd with imageMagick
		
		# Rename Content with a line extracted from the pdf	
		
		# Make Interface in iui for iPhone/Web
	}
	
	
	public function ELbi()
	{
		
		
	}
	
	
	public function Doucette()
	{
		
	}
	
	
	public function PrecisionVent()
	{
		
	}
	
	
	
	
	
	
	
	//-------------------------
	/*
	private function extractRecordsFromTable(	$manufacturer,
												$product,$docType,$subPageTitles,
												$title,$description,
												$textDump, $lines,
												$path,$fileName,
												$pattern,$patternRepeat=1,
												$fieldCategories,
												$fieldSubCategories=NULL
												)
	{
																		
				#Get Title
				$title =  ucwords(strtolower($lines[$title]));
				
				
				# Get Description
				foreach($description as $value) $str .= $lines[$value].' ';  $description = ucwords(strtolower($str));	
									
				# Split the Page (Work on Tomorrow)
				$subPages = preg_split($subPageTitles[1],$textDump);
				$pageCount=0;
				
				# Repeat the Pattern if Necessary
				$pattern = '/'.str_repeat($pattern, $patternRepeat).'/';
				
				foreach($subPages AS $subpage):
							
							# Intialize the array that hold the matches and fieldHeaders. 
							$matches= array(); $fieldHeaders = array();
				
				# Build Global Fields (common to every record in the extracted page)
							
							
							$subPageTitles[$pageCount] =$this->convertRegexToEnglishString($subPageTitles[$pageCount]);
							
							$globalFields=array('manufacturer'=>$manufacturer,'title'=>$title,'description'=>$description,'subPageTitle'=>$subPageTitles[$pageCount],
												'path'=>$path,'file'=>$fileName);
				
				# Get All the Matches Here**************************			

							if(preg_match_all($pattern,$subpage,$matches,PREG_SET_ORDER)):					# Currently does not differentiate temps(120,140,160,185)					
								$subCat = 'configuration';	
							else:
								//die("I got here to $subPageTitles[$pageCount]");
								break 1;																				# Currently does not recognize rows with blank spaces
							endif;
							
						
				# Create all the FIELD HEADERS for gpm5,productConfiuration5,....gpm10,....gpm150
							if($fieldSubCategories!=Null):
								foreach($fieldCategories as $value1):
										foreach($fieldSubCategories as $value2):
											$fieldHeaders[] = $value2.$value1; 
										endforeach;
								endforeach;
								#Add the matched string to the field headers array
								array_unshift($fieldHeaders,'extracted');
							endif;
				
							
				# Fuse Field Headers with the records extracted from the file
							foreach($matches as $match):
								$count = count($fieldHeaders);
								$record = array();
								
								foreach($match as $key=>$value):
									# Iterate over fields of each match to create an associative record
									for($i=0;$i<$count;$i++):			
										$record[$fieldHeaders[$i]]= $match[$i];								
									endfor;
								endforeach;
								
								# Add Extra (Global) Fields NOT Extracted from Document
								foreach($globalFields as $key2 =>$value2) $record[$key2]=$value2;
								
								# add this record to the records array
								$records[] = $record;	
							endforeach;
					
					$pageCount++; # Iterate the subpage title
					
					endforeach;		
					
					
							
					return $records;

						
	}
	
	*/
	
	private function extractRecordsFromTable2(	$manufacturer,$product,$docType,
												$titleLoc,
												$descriptionLoc=NULL,
												$textDump, $lines,
												$path,$fileName,
												$fldHeaders,
												$pattern,
												#---The extra stuff for Subpages, Pattern Repeats, etc----------------#
												$patternRepeat=1,
												$subPageSplitPattern=NULL,
												$subPageNamePattern=NULL,
												$fldSubHeaders=NULL
												)
	{
																				 
			
				# Initialze the result array
				$result = array();
				
				# TITLE
				$title =  ucwords(strtolower($lines[$titleLoc]));
				
				
				# DESCRIPTION
				if($descriptionLoc !=NULL):
					foreach($descriptionLoc as $value):
						$str .= $lines[$value].' ';  
					endforeach;
					$description = ucwords(strtolower($str));
				else:
					$description = 'Null';
				endif;	
									
				
				# SPLIT the Page into Subpages if required---------------------------------------------------------------------
				if($subPageSplitPattern !=NULL): 
					$subPages = preg_split($subPageSplitPattern,$textDump,PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
				else:
					$subPages = array($textDump);
				endif;
			
				
				# REPEAT PATTERN if Necessary and set the regular expression flag to 'x' IGNORE WHITE SPACE---------------------------------------------
				$pattern = '/'.str_repeat($pattern, $patternRepeat).'/x';
				
				
				foreach($subPages AS $subpage):
							
					# Intialize the array that hold the matches and fieldHeaders, subPages 
					$matches= array(); $fieldHeaders = array(); $subPageRecords = array();
			
					
					# Add a subpage Title---------------------------------------------------------------------------------------------------------------------	
					if($subPages != NULL):
							if(preg_match($subPageNamePattern,$subpage,$m)):  
							//if(preg_match('/[0-9]{3}[\s]+F[\s]+Outlet[\s]+Water/i',$subpage,$m)):
							$subPageTitle = $this->cleanString($m[0]);
						endif;
					endif;
					
					
					# Build Global Fields (common to every record in the extracted page)-----------------------------------------------------------------------	
					$globalFields=array('manufacturer'=>$manufacturer,'title'=>$title,'description'=>$description,'subPageTitle'=>$subPageTitle,
										'path'=>$path,'file'=>$fileName);
			
					
					# Get All the Matches Here------NOTE: Currently does not recognize rows with blank spaces--------------------------------------------------		
					if(preg_match_all($pattern,$subpage,$matches,PREG_SET_ORDER)):									
					else:
						#break the foreach loop if there are no matches	
						//echo "no matches for $pagecount";
						//break;															
					endif;
						
					
					# Create all the FIELD HEADERS: Headers and SubHeaders-------------------------------------------------------------------------------------
					$fieldHeaders = $this->combineAllFieldHeaders($fldHeaders,$fldSubHeaders);			
							
					
					# Combine Global Fields, Field Headers, and all the Matches from the Regular Expression----------------------------------------------------
					$subPageRecords = $this->fuseMatchesFieldHeadersGlobals($globalFields,$fieldHeaders,$matches);

					# Insert  subPageRecords into $result array
					$pageRecords[] = $subPageRecords;

				endforeach;	//Iterate to the next Subpage if there is one	
					
				# Remove the Top Index and any null values of the $pageRecords array
				$result = $this->flattenArray($pageRecords);
				
				die(var_dump($result));
				return $result;

						
	}

	private function extractPVIDocType1($doc,$pattern,$fieldHeaders,$addFields,$limit=NULL)
	{
		
		
		$count = count($fieldHeaders);

		# Iterate over each line and look for the pattern. If it exists, store each match in the $m array
		foreach($doc as $line):
			
			# We found a line that Matched!
			if(preg_match($pattern,$line,$m)):  
									
				#Add Field Headers to extracted Extracted from the PVI Document
					for($i=0;$i<$count;$i++):
						$data[$fieldHeaders[$i]]= $m[$i];	
					endfor;
					
				# Add Extra (Global) Fields NOT Extracted from Document
				foreach($addFields as $key =>$value) $data[$key]=$value;
					
				# Add the finished record to the the result array
				$result[]=$data;
			endif;

		endforeach; //On to next line that matches the patern
		
		die(var_dump($result));
		
		return $result;
	}
	
	
	private function addGlobalFields($records)
	{
		//array_merge()
		
		/*
		foreach($records as $record):
			
				$newRecord
		endforeach;
		*/
		
		return $records;
		
	}
	
	private function extractItemInfo($lines,$pattern)
	{
		
		foreach($lines as $value):
			if(preg_match($pattern,$value,$m)):
				$data[] = array('modelNumber'=>$m[1],'specData'=>$m[2]);
			endif;		
		endforeach;
		
		return $data;
	}
	
	
	
	private function extractFromRegex($doc,$pattern,$limit)
	{
		//$pattern = '/[A-Z-0-9 ]{3,999}/';
		for($i=0;$i<$limit;$i++):
			if(preg_match($pattern,$doc[$i],$m)):
				$result[]=$m[0];
			endif;
		endfor;
		
		# Convert the array values to a string and Captialize the words
		foreach($result as $value)  $str .= ucwords(strtolower($value)). ' ';
		
		
		return $str;
		
	}
	
	private function generateFileName($str,$extension)
	{
			if(!is_array($str)):
				# Process filename
				$name = preg_replace("/^[^a-z0-9]?(.*?)[^a-z0-9]?$/i", "$1", $str);		
				$name = str_replace(' ','-',$name);
				$name = ucfirst(strtolower($name));
				$name = $name.'.'.$extension;
			else:
				foreach($str AS $key=>$value):
					if($value !='NULL') $name .= $value;  # Just Concantenate everything for now
					
				endforeach;
				$name=$name.'.'.$extension;
				$name = preg_replace('/[ ]+/','-',$name);
			
			endif;
			
			return $name;
	}
	
	private function removeSpecialChars($array)
	{
			
		foreach ($array as $key=>$value):
			$array[$key] = preg_replace('/&[\w]{3,10};/','',$value);
		endforeach;
		
		return $array;
	}
	
	private function rollingCurl($urls)
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
	
	
	private function request_callback($response, $info) 
	{		
			
			$titlesPerPage = $this->nodeHTMLScraper($response,"/html/body//div[@class='view-content view-content-Resource-Library']//td[@class='view-field view-field-node-title']");
			$hrefsPerPage = $this->attributeHTMLScraper($response,"/html/body//div[@class='view-content view-content-Resource-Library']//a[@href]");
			$this->v->titles[]=$titlesPerPage;
			$this->v->hrefs[]=$hrefsPerPage;			
	}
		
		
	private function lastPageIndex($url,$xpath,$pattern)
	{
				
				$url=$this->getAttribute($url,$xpath);
				
				if(preg_match($pattern,$url,$m)):
					return $m[1]+1;
				else:
					return '404';
				endif;
	}
	
	
	private function buildURLList($lastPage,$url=NULL)
	{
			#Build UrLs to send to Rolling Curl
			for($i=0;$i<$lastPage+1;$i++):
					$urls[]='http://www.armstronginternational.com/resource-library?page=0%2C'.$i.'&filter0[0]=1213&filter1=%2A%2AALL%2A%2A&filter2=&filter3=';
			endfor;	
			return $urls;	
	}
	
	

	


		
		# Turn this into a method with a scraper array to get multiple items in one go
		public function htmlScraper($url,$scrape)
		{

			$html = $this->curl_get_file_contents($url);
			$dom = new DOMDocument();
			$doc->preserveWhiteSpace = false;
			$dom->loadHTML($html);
			
			$xpath = new DOMXPath($dom);
			
			foreach($scrape as $key =>$value):
				
				$elements = $xpath->evaluate($value);
				$length =$elements->length; 
				
				if(!is_null($elements)):
					for ($i = 0; $i < $length; $i++):
						$element = $elements->item($i);

						switch ($key):
								case 'href':	$hrefs[] =	$element->getAttribute($key) ; break;
								case 'node': 	$nodes[] =	$element->nodeValue; break;								
						endswitch;						
					endfor;
				endif;
				
				
				/*
				//Return a simple variable if 1 value is returned. Else return an array
				if(count($result)==1):
					return $result[0];
				else:
					return $result;	
				endif;
				*/
				# add to array
				
			endforeach;
			
			
			//$hrefs = $this->flattenArray($hrefs); $nodes= $this->flattenArray($nodes);
			$result = $this->mergeTwoArrays($hrefs,$hrefName='url',$nodes,$nodeName='description');	
			
			//die(var_dump($result));
			$this->links=$result;
			return $this->links;
		}
		
		
		
	
		
		
	
	private function curl_get_file_contents($url)
	{		
		
			//$fh = fopen('/tmp/curl.txt','w') or die($php_errormsg);
			$c = curl_init();
	        curl_setopt($c, CURLOPT_URL, $url);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			//curl_setopt($c,CURLOPT_AUTOREFERER,1);
	        curl_setopt($c,  CURLOPT_USERAGENT, $this->v->userAgent);
	        curl_setopt($c,CURLOPT_TIMEOUT,$this->curltimeOut); 
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
	
	
	private function getFieldHeaders($table,$ignoreFields)
	{
		//GET FIELD HEADERS From the Table. IGNORE The fields not used for find data like 'id','latitude','longitude',etc
				$allFieldHeaders= $this->db->MetaColumnNames($table,$numericIndex=TRUE);	
								
				foreach($allFieldHeaders as $value):
					if(!in_array($value,$ignoreFields)) $fieldHeaders[]=$value;
				endforeach;
				$count = count($fieldHeaders);
				
			return $fieldHeaders;
	}	
	
	
	
	private function specDataInsert($specItems,$fieldHeaders)
	{
		
		# Extract the specData into an array
			foreach ($specItems as $line):
					
					# Create each Record to insert into the DB
					$data=array(); $combined=array();

					//var_dump($specItems);
					foreach($line as $key=>$value):
						
						$value = trim($value);
						
						if($key=='specData'):	
							
								# Explode the record data and delete the array index
								$data[]=  explode(" ", $value);
								$data = $data[0];
								
								//if($count != count($data)) die('array counts of field Headers and data DO NOT match');
								
								#Add Field Headers to record
								$count = count($fieldHeaders);
								for($i=0;$i<$count;$i++):
									$record[$fieldHeaders[$i]]= $data[$i];	
								endfor;
								
						# Add Model Number 
						elseif($key=='modelNumber'):
							$record['modelNumber'] = $value;
						
						endif;
						
				# This data should probably be passed into this function rather than tacked on the end		
				# Add the extra fields
				$record['manufacturer']='PVI';
				$record['description'] ="This is a dummy description for now";
				$result['originalFileName']=$originalFileName;
				$result['fileName']='newfilname';
				$result['thumbnail']=$specItem['modelNumber'].'.jpg';
				$result['textDump']=$txtDump;
				$result['price']='price coming soon';
						
					endforeach;
					
					# Add Each Parsed Record (in the pdf file) to the result array
					$result[]=$record;
					
					
				
					//Store each Record and delete the temporary arrays
			endforeach;//On to next record
			

		return $result;
	}
		
	

	//----Deprecated--------------------------------------------------------------------------//

	
}

	

?>
