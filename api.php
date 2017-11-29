<?php 
	ini_set('max_execution_time', 1000);
	// set_time_limit(0);
	// require("db/config.php");
	require("simple_html_dom.php");
	require("common.php");

	/**
	* 
	*/
	class Yelp 
	{
		
		function __construct()
		{
			
		}
		public function getAllList()
		{
			$query = $_POST['query'];
			$city = str_replace(",","%2C",str_replace(" ", "%20", $_POST['city']));
			//New+York%2C+NY
			//$token = "FQ1A4CO0PTLLFHGWKUD0QQDEMH3C21MVTHVLE2HY3G5NDHE1";
			// $url = "https://www.yelp.com/search?find_desc=".$query."&find_loc=".$city."&ns=1";
			$start = 0;
			$list = array();
			
			While($start <= 100)
			{
				$html = $this->getHtml($query,$city,$start);

				if(is_object($html)){	
					// if(is_object($html->find('ul[class=ylist]',0))){
						// $tgDiv = $html->find('div[class=search-results-content]',0);				
				 		foreach($html->find('li[class=regular-search-result]') as $element){
				 			$obj = new stdClass();
				 			$obj->title = trim($element->find('a[class=biz-name]',0)->plaintext);
				 			$obj->url = "https://www.yelp.com" . $element->find('a[class=biz-name]',0)->href;
				 			$obj->category = "";
				 			if(is_object($element->find('span[class=category-str-list]',0)))
				 			{
				 				$obj->category = trim($element->find('span[class=category-str-list]',0)->plaintext);
				 			}
				 			$obj->address = "";
				 			if(is_object($element->find('address',0)))
				 			{
				 				$obj->address = trim($element->find('address',0)->plaintext);	
				 			}
				 			$obj->phone = "";
				 			if(is_object($element->find('span[class=biz-phone]',0)))
				 			{
				 				$obj->phone = trim($element->find('span[class=biz-phone]',0)->plaintext);	
				 			}
				 			$list[] = $obj;
				 		}
					// }

				}
				
				$start += 10;
			}
			

			
			return $list;
		}
			

		// }

		public function getHtml($query,$city,$start)
		{
			$url = "https://www.yelp.com/search/snippet?find_desc=".$query."&find_loc=".$city."&start=$start&parent_request_id=8307fa133c6e3aa2&request_origin=user";

			$html = file_get_contents($url);
			
			$html = str_get_html($html);

			$html = explode("\", \"platform_info\"",explode("\"search_results\": ", $html)[1])[0];
			$html = str_replace("\\\"","",str_replace("\\n", "", special_unicode_to_utf8($html)));

			$html = ltrim($html,"\"");

			$html = str_get_html($html);

			return $html;
		}

	}
	
 ?>