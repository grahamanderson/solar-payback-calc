<?php

$f= new Formulas();
//print $f->convert_kw2btu(5.39). ' btu/f<sup>2</sup>/day';
 
// Calculate Water Heater Savings
$v->savings->whtr = $f->calc_whtrSavings(	$pricePerYear=4500,
											$lifeExtension=4,
											$maintenanceSavings=400);

// Calculate Solar Savings
$v->savings->solar = $f->calc_solarSavings(	$totalMbtuReq=.302773,
											$pricePerMbtu=10,
											$whtrEfficiency=.80);

print	'<table border="1">'. 
		'<th>Savings</th>	<th>year</th>	<th>month</th>	<th>day</th>'.
		'<tr>'.
			'<td>Solar</td>'.
			'<td>'.$v->savings->solar->year.'</td>'.
			'<td>'.$v->savings->solar->month.'</td>'.
			'<td>'.$v->savings->solar->day.'</td>'.
		'</tr>'.
		'<tr>'.
			'<td>Water Heater</td>'.
			'<td>'.$v->savings->whtr->year.'</td>'.
			'<td>'.$v->savings->whtr->month.'</td>'.
			'<td>'.$v->savings->whtr->day.'</td>'.
		'</tr>'.
		'<tr>'.
			'<td>Total</td>'.
			'<td>'.($v->savings->solar->year 	+ 	$v->savings->whtr->year).'</td>'.
			'<td>'.($v->savings->solar->month 	+ 	$v->savings->whtr->month).'</td>'.
			'<td>'.($v->savings->solar->day		+	$v->savings->whtr->day ).'</td>'.
		'</tr>'.
		'</table>';
	
var_dump($v);
class Formulas 
{
	
	
	function __construct()
	{		
		
	}
	
	
	
	//-----------PUBLIC FUNCTIONS---------------------------------------------------------//
	
	
	//--CONVERISONS--/////////////////////////////////////////////////////////
	
	// Convert kW/m2/day Insolation to Btu/f2/day
	public function convert_kw2btu($kw)
	{
		return $kw * 317.1;			
	}
	
	//Convert btu/f2/day Insolation to kw/m2/day
	public function convert_btu2kw($btu)
	{
		return $btu/317.1;		
	}
	
	// Convert  Watts per hour to Btus per hour
	public function convert_watt2btu($watt)
	{
		return  3.412*$watt;
	}
	
	
	//Given Therms, BTUs, or Megajoules Send the object, $obj back with all equivalent Energy Units
	public function convert_energyUnits($n,$type)
	{
		switch($type)
		{
			case "therm": 
				$obj->therm=$n;
				$obj->kwh=$n*29.3;
				$obj->btu=$n*100000;
				$obj->mj=$n*105.5;
				break;
			
			case "kwh":
				$obj->kwh=$n;
				$obj->therm=$n/29.3;
				$obj->btu=$n*100000/29.3;
				$obj->mj=$n*105.5/29.3;
				break;
			
			case "mj":
				$obj->mj=$n;
				$obj->therm=105.5/$n;
				$obj->kwh=$n*105.5/29.3;
				$obj->btu=$n*100000/105.5;				
				break;
		}
		return $obj;
	}
	
	

		

	
	
	//--CALCUATIIONS--/////////////////////////////////////////////////////////
	
	// Calculate Btus given Gallons, Temperature Rise, and DeltaT
	public function calc_btu($gal,$deltaT)
	{
		return $gal * 8.33 * $deltaT;
	}
	
	//Apricus Absorber Area Calc
	public function calc_absorberArea($n)
	{
		return .08*$n;
	}
	

	//Ratio Calc: Use: 
	// Use Known San Antonio(The Arc) BTUs per day for the Apricus Ap30 known
	// Use Known San Antonio and Target Insolation Values
	// Return the the BTU Output for the Target City
	public function calc_btuRatio($obj)
	{
		$obj->result = ($obj->kwm2_X * $obj->btu_Arc)/ $obj->kwm2d_Arc;
		return $obj;
		
	}
	
	public function calc_collectors($cityBtuf2d,$totalBtu)
	{
		return $totalBtu/$cityBtuf2d;
	}
	
	
	//<savings Methods>//
	public function calc_solarSavings($mbtuReqPerDay,$mbtuPrice,$whtrEfficiency)
	{
		
		$obj->year	= $mbtuReqPerDay * $mbtuPrice * $whtrEfficiency * 365;
		$obj->month = $obj->year / 12; 
		$obj->day 	= $obj->year / 365; 
		return $obj;
		
	}

	
	public function calc_whtrSavings($costPerYear,$lifeExtension,$maintenanceSavings)
	{	
		
		$obj->year	=	($costPerYear/$lifeExtension) + $maintenanceSavings;
		$obj->month	=	$obj->year	 / 12; 
		$obj->day	=	$obj->year / 365; 
		return $obj;		
	}
	
	//</savings Methods>//
	
	public function calc_btuApricus($obj)
	{
		
	}


	
		public function calc_mbtu($price,$fuel)
	{
			
			
			switch($fuel)
			{
				case "propane":
					return $price/91500; 
				break;
				
				case "naturalGas":
					return $price;  // already calculated in mbtu
				break;				
				
				case "oil":
					return $price/138500;
				break;				
				
				case "electric":
					return $price/3412;
				break;	
			}
			
	}
	
	
	public function calc_fuelCosts($fuel)
	{
			
			
			switch($fuel)
			{
				case "propane":
					//91500 BTUs per gallon
				break;
				
				case "naturalGas":
					//1028 Btus f3 per gallon
				break;				
				
				case "oil":
					//138500 BTUs per gallon
				break;				
				
				case "electric":
					//3412 BTUs expended
				break;	
			}
	}
	
	
	
	
	//-----------private functions------------------------------
	
	
	
	//Getter Setter Stuff
	// gets a value
	function get($var)
	{
		return $this->vars[$var];
	}

	// sets a key => value
	function set($key,$value)
	{
		$this->vars[$key] = $value;
	}
	
// loads a key => value array into the class
	function load($array)
	{
		if(is_array($array))
		{
			foreach($array as $key=>$value)
			{
				$this->vars[$key] = $value;
			}
		}
	}

	// empties a specified setting or all of them
	function unload($vars = '')
	{
		if($vars)
		{
			if(is_array($vars))
			{
				foreach($vars as $var)
				{
					unset($this->vars[$var]);
				}
			}
			else
			{
				unset($this->vars[$vars]);
			}
		}
		else
		{
			$this->vars = array();
		}
	}
		
	
}

?>
