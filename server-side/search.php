<?php 
	//script for querying the data for JQuery autocomplete plugin 

	 // server variables used to access database
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "Gunsel Airfreight";

	$con = mysql_connect($servername, $username, $password);  
	if(! $con ) {
	  die('Could not connect: ' . mysql_error());
	}

	mysql_select_db($dbname);
	$sql = "SELECT country,iata FROM `Gunsel rates`;";
	$result=mysql_query($sql, $con);
	$row = mysql_fetch_array($result);
	$json = array();

	// the result of the query - the country and its corresponding IATA codes received from db - are added to an associative array which is JSON-stringified then sent back to the client
	while($row = mysql_fetch_array($result))     
	 {
	    $json[] = ['value' => $row['iata'], 'data' => ['category' => $row['country']]];
	}

	$jsonstring = json_encode($json);
	echo $jsonstring;
?>