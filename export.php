<?php 
	    
		if(!empty($_POST)){
		$data = $_POST['item'];
		// file name for download
	    $fileName = "Yelp_" . date('Ymd') . ".xls";
	    ob_start();
	    // headers for download
	    header("Content-Type: application/octet-stream");
	    header("Content-Disposition: attachment; filename=\"$fileName\"");
		// header("Content-Type: application/vnd.ms-excel");
		header( "Content-type: application/vnd.ms-excel; charset=UTF-8" );
		$flag = false;
	    foreach($data as $row) {
	        if(!$flag) {
	            // display column names as first row
	            echo implode("\t", array_keys($row)) . "\n";
	            $flag = true;
	        }
	        // filter data
	        array_walk($row, 'filterData');
	        echo implode("\t", array_values($row)) . "\n";

	    }
	    ob_end_flush();
	 //    $response =  array(
	 //        'op' => 'ok',
	 //        'file' => "data:application/vnd.ms-excel;base64,".base64_encode(implode("\t", array_values($row)) . "\n")
	 //    );

		// die(json_encode($response));	

	    exit;
			// echo json_encode(array("result"=>true));
	}else{
		echo json_encode(array("result"=>false));
	}

	function filterData(&$str)
    {
        $str = preg_replace("/\t/", "\\t", $str);
        $str = preg_replace("/\r?\n/", "\\n", $str);
        if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
    }
 ?>