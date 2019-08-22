<!DOCTYPE html>

<html lang="en">
  <head>
		
	</head>
    <body>
    <?php 
    	require_once "../requires-sd/config-sd.php";
    	
    if(MAINTENANCE_MODE == 'off'){
      echo "Page Not Found !";
	 //redirectPage(SITE_URL);
		}else{
      echo "Sorry!Site is under maintenance mode.";
    }
    ?>
      	
    </body>
</html>

