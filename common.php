<?php
	// C
	function curl_download($list) {	// accepts array of urls, returns array of content/data
		// initialize the multihandler
		$mh = curl_multi_init();
		
		$channels = array();
		foreach ($list as $key => $url) {
			// initiate individual channel
			$channels[$key] = curl_init();
			curl_setopt_array($channels[$key], array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				//CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_SSL_VERIFYHOST	=> 2,
				CURLOPT_SSL_VERIFYPEER	=> false
			));
			
			// add channel to multihandler
			curl_multi_add_handle($mh, $channels[$key]);
		}
		
		// execute - if there is an active connection then keep looping
		$active = null;
		do {
			$status = curl_multi_exec($mh, $active);
			//if ($status === false) echo "Error : " . curl_error($mh);
		} while ($active && $status == CURLM_OK);
		
		$data = array();
		foreach ($channels as $chan) {
			$data[] = curl_multi_getcontent($chan);
			curl_multi_remove_handle($mh, $chan);
			curl_close($chan);
		}
		// close the multihandler
		curl_multi_close($mh);
		
		return $data;
	}
	
	function customsearch_storeApiCache($catid, $dataid, $json, $search_string, $nav) {
		$error_msg = "";
		$json = gzcompress($json, 9);
		
		$conn = new mysqli(constant('DB_HOST'), constant('DB_USER'), constant('DB_PASSWORD'), constant('DB_NAME'));
		// DELETE EXISTING
		if (!($stmt = $conn->prepare("DELETE FROM `cache_api` WHERE `catid` = ? AND `dataid` = ? AND `search_string` = ? AND `nav` = ?"))) {
			$error_msg = "Prepare failed: (" . $conn->errno . ") " . $conn->error;
		}
		if (!$stmt->bind_param("ddss", $catid, $dataid, $search_string, $nav)) {
			$error_msg = "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (!$stmt->execute()) {
			$error_msg = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		// INSERT
		if (!($stmt = $conn->prepare("INSERT INTO `cache_api` (`catid`,`dataid`,`data`,`search_string`, `nav`) VALUES (?,?,?,?,?)"))) {
			$error_msg = "Prepare failed: (" . $conn->errno . ") " . $conn->error;
		}
		if (!$stmt->bind_param("ddsss", $catid, $dataid, $json, $search_string, $nav)) {
			$error_msg = "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (!$stmt->execute()) {
			$error_msg = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		return $error_msg;
	}
	function customsearch_getApiCache($catid, $id, $search_string, $nav) {
		$o = new stdClass();
		$o->json = "";
		$o->epoch = constant("SECONDS_IN_24HOURS") + 1;
		
		$query = "SELECT UNIX_TIMESTAMP(`datetime`) AS `epoch`, `data` FROM `cache_api` WHERE `catid` = ? AND `dataid` = ? AND `search_string` = ? AND `nav` = ?";
		$conn = new mysqli(constant('DB_HOST'), constant('DB_USER'), constant('DB_PASSWORD'), constant('DB_NAME'));
		if (mysqli_connect_errno()) { $html = "Failed to connect to MySQL: " . mysqli_connect_error(); }
		$statement = $conn->prepare($query);
		$statement->bind_param("ddss", $catid, $id, $search_string, $nav);
		$statement->execute();
		$rst = $statement->get_result();
		if ($row = $rst->fetch_assoc()) {
			$o->epoch = $row['epoch'];
			$o->json = $row['data'];
			if ($o->json == "") { $o->json = "[]"; }	// return empty array to avoid pulling data from website again.
			else { $o->json = gzuncompress($o->json); }
		}
		return $o;
	}
	
	function customsearch_removeHTMLTags($html) {
		while(1) {
			$wherefrom = strpos($html, "<");
			if ($wherefrom === false) { break; }
			$whereto = strpos($html, ">", $wherefrom + 1);
			if ($whereto === false) { break; }
			$html = substr($html, 0, $wherefrom) . substr($html, $whereto + 1);
		}
		return $html;
	}
	
	function customsearch_youtube_to_unix_time($datetimestr) {	// returns epoch time, seconds from 1/1/1970
		$datetimeary = explode("T", $datetimestr);
		$dateary = explode("-", $datetimeary[0]);
		$timeary = explode(":", $datetimeary[1]);
		$secsary = explode(".", $timeary[2]);
		return mktime($timeary[0], $timeary[1], $secsary[0], $dateary[1], $dateary[2], $dateary[0]);
	}
	
	// E
	function ewchar_to_utf8($matches) {
		$ewchar = $matches[1];
		$binwchar = hexdec($ewchar);
		$wchar = chr(($binwchar >> 8) & 0xFF) . chr(($binwchar) & 0xFF);
		return iconv("unicodebig", "utf-8", $wchar);
	}
	
	// G
	function get_cookie() {
		return $_REQUEST["params"]["c"];
	}
	function get_current_page_url() {
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") { $pageURL .= "s"; }
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}
	function get_english($table, $transword) {
		$rst = search_main_get_sqlrow("SELECT `word` FROM `$table` WHERE `transword` = ?", "translations", "s", array($transword));
		return $rst["word"];
	}

	// I
	function is_user_logged_in() {
		return isset($_REQUEST['signedin']);
	}
	function image_base64($imgblob, $mime)	{
		$base64 = base64_encode($imgblob);
		return ('data:' . $mime . ';base64,' . $base64);
	}
	function image_uri($imgblob) {
		$imgdata = getimagesizefromstring($imgblob);
		$imgbase64 = image_base64($imgblob, $imgdata["mime"]);
		$o = new stdClass();
		$o->imagebase64 = $imgbase64;
		$o->width = $imgdata[0];
		$o->height = $imgdata[1];
		$o->mime = $imgdata["mime"];
		return $o;
	}
	function imagesearch_storeImageCache($md5, $blob) {
		$error_msg = "";
		$blob = gzcompress($blob, 9);
		
		$conn = new mysqli(constant('DB_HOST'), constant('DB_USER'), constant('DB_PASSWORD'), constant('DB_NAME'));
		// DELETE EXISTING
		if (!($stmt = $conn->prepare("DELETE FROM `cache_images` WHERE `md5` = ?"))) {
			$error_msg = "Prepare failed: (" . $conn->errno . ") " . $conn->error;
		}
		if (!$stmt->bind_param("s", $md5)) {
			$error_msg = "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (!$stmt->execute()) {
			$error_msg = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		// INSERT
		if (!($stmt = $conn->prepare("INSERT INTO `cache_images` (`md5`,`image`) VALUES (?,?)"))) {
			$error_msg = "Prepare failed: (" . $conn->errno . ") " . $conn->error;
		}
		if (!$stmt->bind_param("ss", $md5, $blob)) {
			$error_msg = "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		if (!$stmt->execute()) {
			$error_msg = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		return $error_msg;
	}
	function imagesearch_getImageCache($md5) {
		$blob = null;
		
		$query = "SELECT `image` FROM `cache_images` WHERE `md5` = ?";
		$conn = new mysqli(constant('DB_HOST'), constant('DB_USER'), constant('DB_PASSWORD'), constant('DB_NAME'));
		if (mysqli_connect_errno()) { $html = "Failed to connect to MySQL: " . mysqli_connect_error(); }
		$statement = $conn->prepare($query);
		$statement->bind_param("s", $md5);
		$statement->execute();
		$rst = $statement->get_result();
		if ($row = $rst->fetch_assoc()) {
			$blob = $row['image'];
			$blob = gzuncompress($blob);
		}
		return $blob;
	}
	
	// S
	function special_unicode_to_utf8($str) {
		return preg_replace_callback("/\\\u([[:xdigit:]]{4})/i", "ewchar_to_utf8", $str);
	}	
	function search_main_get_sqlrow($query, $db_name = "", $types = null, $params = null) {
		$arrkeys = array();
		$arrvals = array();
		
		if ($db_name == "") { $db_name = constant("DB_NAME"); }
		$conn = new mysqli(constant("DB_HOST"), constant("DB_USER"), constant("DB_PASSWORD"), $db_name);
		if (mysqli_connect_errno()) { print("Failed to connect to MySQL: " . mysqli_connect_error()); }		
		$statement = $conn->prepare($query);
		if($types&&$params)
        {
            $bind_names[] = $types;
            for ($i=0; $i<count($params); $i++) 
            {
                $bind_name = 'bind' . $i;
                $$bind_name = $params[$i];
                $bind_names[] = &$$bind_name;
            }
            $return = call_user_func_array(array($statement,'bind_param'),$bind_names);
        }
		$statement->execute();
		$result = $statement->get_result();
		$finfo = $result->fetch_fields();	
		if ($row = $result->fetch_assoc()) {
			for ($index = 0; $index < count($finfo); $index++) {
				$arrkeys[] = $finfo[$index]->name;
				$arrvals[] = $row[$finfo[$index]->name];
			}
		}
		
		return array_combine($arrkeys, $arrvals);
	}
	function search_main_get_sqlquery($query, $db_name = "", $types = null, $params = null) {
		$arr = array();
		
		if ($db_name == "") { $db_name = constant("DB_NAME"); }
		$conn = new mysqli(constant("DB_HOST"), constant("DB_USER"), constant("DB_PASSWORD"), $db_name);
		if (mysqli_connect_errno()) { print("Failed to connect to MySQL: " . mysqli_connect_error()); }		
		$statement = $conn->prepare($query);
		if($types&&$params)
        {
            $bind_names[] = $types;
            for ($i=0; $i<count($params); $i++) 
            {
                $bind_name = 'bind' . $i;
                $$bind_name = $params[$i];
                $bind_names[] = &$$bind_name;
            }
            $return = call_user_func_array(array($statement,'bind_param'),$bind_names);
        }
		$statement->execute();
		$result = $statement->get_result();
		$finfo = $result->fetch_fields();	
		while ($row = $result->fetch_assoc()) {
			$arrkeys = array();
			$arrvals = array();
			for ($index = 0; $index < count($finfo); $index++) {
				$arrkeys[] = $finfo[$index]->name;
				$arrvals[] = $row[$finfo[$index]->name];
			}
			$arr[] = (object) array_combine($arrkeys, $arrvals);
		}
		
		return $arr;
	}
	function search_main_get_sql($query, $db_name = "", $types = null, $params = null) {
		$list = array();
		
		if ($db_name == "") { $db_name = constant("DB_NAME"); }
		
		$conn = new mysqli(constant("DB_HOST"), constant("DB_USER"), constant("DB_PASSWORD"), $db_name);
		if (mysqli_connect_errno()) { print("Failed to connect to MySQL: " . mysqli_connect_error()); }		
		$statement = $conn->prepare($query);
		if($types&&$params)
        {
            $bind_names[] = $types;
            for ($i=0; $i<count($params); $i++) 
            {
                $bind_name = 'bind' . $i;
                $$bind_name = $params[$i];
                $bind_names[] = &$$bind_name;
            }
            $return = call_user_func_array(array($statement,'bind_param'),$bind_names);
        }
		$statement->execute();
		$result = $statement->get_result();
		
		while ($row = $result->fetch_assoc()) {
			$o = new stdClass();
			$o->text = $row["text"];
			$o->value = $row["value"];
			$list[] = $o;
		}
		
		return $list;
	}
	
	// T
	function toProperCase($str) {
		return preg_replace_callback("/\w\S*/",
		function($txt) {
			$noCaps = array('of','a','the','and','an','am','or','nor','but','is','if','then', 'else','when','at','from','by','on','off','for','in','out','to','into','with');
			if(in_array(strtolower($txt[0]), $noCaps)) { return strtolower($txt[0]); } return ucwords($txt[0]);
		},
		$str);
	}
?>