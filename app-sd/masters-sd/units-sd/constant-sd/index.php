<?php

$reqAuth = true;
require_once("../../../requires-sd/config-sd.php");
include(DIR_ADMIN_CLASS."constant-sd.lib.php");
include '../../../requires-sd/PHPExcel/Classes/PHPExcel.php';
include '../../../requires-sd/PHPExcel/Classes/PHPExcel/Writer/Excel2007.php';

$module = "constant-sd";
$table = "tbl_language_constant";
$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;

$styles = array(array("data-tables/DT_bootstrap.css", SITE_ADM_PLUGIN),
	array("bootstrap-switch/css/bootstrap-switch.min.css", SITE_ADM_PLUGIN));

$scripts = array("core/datatable.js",
	array("data-tables/jquery.dataTables.js", SITE_ADM_PLUGIN),
	array("data-tables/DT_bootstrap.js", SITE_ADM_PLUGIN),
	array("bootstrap-switch/js/bootstrap-switch.min.js", SITE_ADM_PLUGIN));

chkPermission($module);
$Permission = chkModulePermission($module);

$metaTag = getMetaTags(array("description" => "Admin Panel",
	"keywords" => 'Admin Panel',
	'author' => AUTHOR));

$id = isset($_GET["id"]) ? (int) trim($_GET["id"]) : 0;
$postType = isset($_POST["type"]) ? trim($_POST["type"]) : '';
$type = isset($_GET["type"]) ? trim($_GET["type"]) : $postType;

$headTitle = $type == 'add' ? 'Add' : ($type == 'edit' ? 'Edit' : 'Manage') . ' Constant';
$winTitle = $headTitle . ' - ' . SITE_NM;
$breadcrumb = array($headTitle);

$objContent = new Constant($module);
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['type'])  && $_POST['type'] == 'import_constant') {
	error_reporting(E_ALL);
	$insert_counter = $update_counter = 0;
	$response = array('status'=>false, 'error'=>'something went wrong');
	$valid_files_array = array('xls', 'xlsx');
	if(!empty($_FILES['file']['name']) && empty($_FILES['file']['error'])) {
		$ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
		if(in_array($ext, $valid_files_array)) {
			$file_name = DIR_CONSTANT_TMP."import_".$module."_".$adminUserId.'.'.$ext;
			move_uploaded_file($_FILES['file']['tmp_name'], $file_name);
			try {
				$inputFileType = PHPExcel_IOFactory::identify($file_name);
				$objReader = PHPExcel_IOFactory::createReader($inputFileType);
				$objPHPExcel = $objReader->load($file_name);
			} catch(Exception $e) {
				$response['error'] = 'Please upload file and try again';
				echo json_encode($response);
				exit;
			}

			$sheet = $objPHPExcel->getSheet(0);
			$highestRow = $sheet->getHighestRow();
			$highestColumn = $sheet->getHighestColumn();

			$cols = array();
			$avail_cols = $db->select($table, array('*'), array(), 'ORDER BY ID ASC LIMIT 0, 1')->result();
			$avail_cols = array_keys($avail_cols);
			$headers = $sheet->rangeToArray('A1' . ':' . $highestColumn . '1', NULL, TRUE, FALSE);
			
			if(!empty($headers)) {
				foreach ($headers[0] as $key => $value) {
					if(!empty($value)) {
						if(in_array($value, $avail_cols)){
							$cols[] = strtolower($value);
						}						
					}
				}

				$rowData = $sheet->rangeToArray(
					'A2:' . $highestColumn . $highestRow,
					NULL,TRUE,FALSE
				);

				if($rowData) {
					$insert_array = $tmp_array = array();
					foreach ($rowData as $key => $value) {
						$tmp_array = array();
						foreach ($value as $key_inner => $value_inner) {
							if(array_key_exists($key_inner, $cols)) {
								if(empty(preg_match('/"/', $value_inner))) {
									$tmp_array[$cols[$key_inner]] = $value_inner;
								} else {
									$response['error'] = 'Double quotes is not allowed in constant value';
									echo json_encode($response);
									exit;
								}
							}
						}
						$insert_array[] = $tmp_array;
					}
					
					if(!empty($insert_array)) {
						foreach ($insert_array as $key => $value) {
							$constant_name = $value['constant'];
							if(!empty($constant_name)) {
								$check = getTablevalue($table, 'id', array('constant'=>$constant_name));
								if(empty($check)) {
									$value['created_date'] = date("Y-m-d H:i:s");
									$insert = $db->insert($table, $value)->getLastInsertId();
									if($insert) { $insert_counter++; }
								} else {
										// update
									unset($value['constant']);
									$is_update = $db->update($table, $value, array('constant'=>$constant_name))->affectedRows();
									if($is_update) { $update_counter++; }
								}
							}
						}
						//makeConstantFile();
						$response['status'] = true;
						$response['success'] = 'Constant has been imported successfully, '.$insert_counter.' constant(s) inserted and '.$update_counter.' constant(s) updated';
					} else {
						$response['error'] = 'Format of file is not valid, Please first export the constant file in xls format and modify it as per requirement and upload it.';
					}
				} else {
					$response['error'] = 'Format of file is not valid, Please first export the constant file in xls format and modify it as per requirement and upload it.';
				}
			} else {
				$response['error'] = 'Format of file is not valid, Please first export the constant file in xls format and modify it as per requirement and upload it.';
			}
		} else {
			$response['error'] = 'Please select valid file';
		}
	}
	echo json_encode($response);
	exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	$objContent->contentSubmit($_POST,$Permission);

}

$pageContent = $objContent->getPageContent();
require_once(DIR_ADMIN_TMPL . "compiler-sd.skd");
