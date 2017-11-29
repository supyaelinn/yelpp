<?php 
	// set_time_limit(0);
	require("simple_html_dom.php");


	if(!empty($_GET['query']))
	{
		$query = str_replace(" ", "+", $_GET['query']);
		
		$url = "https://www.yelp.com/location_suggest/v2?prefix=" . $query;
		
		$html = file_get_contents($url);
		
		$html = str_get_html($html);
		
		$name = explode("\"title\": \"", $html);

		$arr = array();
		
		for ($i=1; $i <= count($name) ; $i++) { 
			$arr2 = array();
			$arr2['id'] = $i;
			$arr2['text'] = explode("\"", $name[$i])[0];
			
			$arr[] = $arr2;
		}

		echo json_encode(array("results"=>$arr));exit;
	}

 ?>