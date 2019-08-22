<style type="text/css">
	.maintenance-image img{
		height: 100%;
		width: 100%;
	}
</style>
<?php
	$module = 'maintenance_mode-sd';
	$reqAuthXml = $_SERVER["SERVER_NAME"].'##'.$module;
	require_once "../../requires-sd/config-sd.php";

	if(MAINTENANCE_MODE == 'y') {
		echo "
			<!DOCTYPE html>
			<html lang='en'>
			<head>
				<meta charset='utf-8'>
				<title>Sorry we are down for maintenance - ".SITE_NM."</title>
				<meta name='viewport' content='width=device-width, initial-scale=1'>
				<style>
                .maintenance-image{
                    position:relative;
                                        
                       }
                </style>
                
			</head>
			<body>
                <div class='maintenance-image'>
				    <img src='".SITE_UPD."Maintenance_mode.png' align='center'>
                </div>
			</body>
			</html>
		";
	} else {
		redirectPage(SITE_URL);
	}
	exit;
?>