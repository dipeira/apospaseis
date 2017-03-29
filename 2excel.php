    <?php
	//$contents = $_POST['data'];
	//if (isset ($_POST['data']))
	//{
			$contents = $_POST['data'];
                        //if (mb_detect_encoding($str, 'UTF-8') === false)
                            //$contents = mb_convert_encoding($contents,"utf-8","iso-8859-7");
	//}
	//	elseif (isset ($_GET['data']))
	//		$contents = $_GET['data'];
                        
	
    $filename ="export.xls";
    
	header('Content-type: application/ms-excel');
    header('Content-Disposition: attachment; filename='.$filename);
    echo $contents;
    ?>