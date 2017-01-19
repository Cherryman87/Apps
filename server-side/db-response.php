<?php

   // server variables used to access database
   $servername = "localhost";
   $username = "root";
   $password = "";
   $dbname = "Gunsel Airfreight";
   

   //variables with data received from GET request
   $q = $_GET['q'];
   $w = $_GET['w'];
   $l = $_GET['l'];
   $h = $_GET['h'];
   $d = $_GET['d'];
   $a = $_GET['a'];
   $customsFee= ($_GET['customsFee'] == 'y' ? 300 : 0);
   $awbFee = ($_GET['awbFee'] == 'y' ? 500 : 0);


   //chargeable weight calcuated based on the shipment dimensions. Uses constant coefficient of 6000
   $cw = ($l*$h*$d)/6000;
   //variable for storing the final weight value used in the total cost calculation
   $chosenWeight;


   $conn = mysql_connect($servername, $username, $password);  
   if(! $conn ) {
      die('Could not connect: ' . mysql_error());
   }
   

   if ($w <= 0) {
      echo "something wrong with the weight you entered! please re-enter it"; 
      mysql_close($conn);
      exit();
   }


   //comparing the regular weight with chargeable weight based on dims and assigning the higher value to the 'chosenWeight' variable.
   if($w > $cw) {
      $chosenWeight = $w;
   } else {
      $chosenWeight = round($cw,2);
   } 


   //running different scenarios for different weight categories. Rates are categorized according to the so-called weight pools, i.e. Mininal, Nonimal, >45kg < 99g, >100kg < 249kg, >250kg < 499kg, > 500kg 
   //the first scenario acounts for the situations when the weight is lower than 45kg
   if ($chosenWeight < 45) {
      $sql = "SELECT M FROM `Gunsel rates` WHERE iata = '$q';";
      mysql_select_db($dbname);
      $retval = mysql_query( $sql, $conn);
      if(! $retval ) {
         die('Could not get data: ' . mysql_error());
       } 
      $row = mysql_fetch_assoc($retval); 
      $quoteForM = $row['M'];
      $sql = "SELECT N FROM `Gunsel rates` WHERE iata = '$q';";
      mysql_select_db($dbname);
      $retval = mysql_query( $sql, $conn);
      if(! $retval ) {
         die('Could not get data: ' . mysql_error());
       } 
      $row = mysql_fetch_assoc($retval); 
      $quoteForN = $row['N'];
      $totalForN = $quoteForN*$chosenWeight;
      if ($quoteForM > $totalForN) {

         //if the shipment type is DGR this means that the rate chosen for the final calculation is further increased by 25%, in addition a fixed fee of UAH 875 is added to the final cost
         if ($_GET['cargoType'] == 'DGR') {
            $q = strtoupper($q);
            $resultingQuote = $quoteForM*1.25;
            $totalCost = $resultingQuote + $customsFee + $awbFee + 875;
            if ($a) {
               $resultingQuote = $a;
               $totalCost = $resultingQuote*$chosenWeight + $customsFee + $awbFee;
               $totalCost = number_format($totalCost, 2, ',', ' '); 
            } 
            echo nl2br ("The quote for IATA code: '<em><strong>$q</strong></em>' with the weight of '<em><strong>$chosenWeight</strong></em>' kgs is: <h2><i>UAH $resultingQuote</i></h2> \n The total cost of airfreight would be: <h2><em>UAH \n$totalCost</em></h2>");
            mysql_free_result($retval);
            mysql_close($conn);
            exit();
         }

         //if the shipment type is TkPlus this means that the rate chosen for the final calculation is further increased by 25% without any other fees
         if ($_GET['cargoType'] == 'TkPlus') {
            $q = strtoupper($q);
            $resultingQuote = $quoteForM*1.25;
            $totalCost = $resultingQuote + $customsFee + $awbFee;
            if ($a) {
               $resultingQuote = $a;
               $totalCost = $resultingQuote*$chosenWeight + $customsFee + $awbFee;
               $totalCost = number_format($totalCost, 2, ',', ' '); 
            }
            echo nl2br ("The quote for IATA code: '<em><strong>$q</strong></em>' with the weight of '<em><strong>$chosenWeight</strong></em>' kgs is: <h2><i>UAH $resultingQuote</i></h2> \n The total cost of airfreight would be: <h2><em>UAH \n$totalCost</em></h2>");
            mysql_free_result($retval);
            mysql_close($conn);
            exit();
         }

         $resultingQuote = $quoteForM;
         $q = strtoupper($q);
         $totalCost = $resultingQuote + $customsFee + $awbFee;    
         if ($a) {
            $resultingQuote = $a;
            $totalCost = $resultingQuote*$chosenWeight + $customsFee + $awbFee;
            $totalCost = number_format($totalCost, 2, ',', ' '); 
         }
         echo "The quote for IATA code = '<em><strong>$q</strong></em>' with the weight of '<em><strong>$chosenWeight</strong></em>' kgs is: <h2><i>UAH $resultingQuote</i></h2> \n The total cost of airfreight would be: <h2><em>UAH \n$totalCost</em></h2>";
         mysql_free_result($retval);
         mysql_close($conn);
         exit();
      }
      $resultingQuote = $quoteForN;     
   }


   //all other weight scenarios calculate the final rate per 1 kg (the '$resultingQuote' variable) that will be applied in the end the final block of the script together with the '$totalCost' variable accounting for the total cost of the airfreight 
   if ($chosenWeight >= 45 && $chosenWeight < 100) {
      $sql = "SELECT w45 FROM `Gunsel rates` WHERE iata = '$q';";
      mysql_select_db($dbname);
      $retval = mysql_query( $sql, $conn);
      if(! $retval ) {
         die('Could not get data: ' . mysql_error());
       } 
      $row = mysql_fetch_assoc($retval); 
      $resultingQuote = $row['w45'];
   }


   if ($chosenWeight >= 100 && $chosenWeight < 250) {
      $sql = "SELECT w100 FROM `Gunsel rates` WHERE iata = '$q';";
      mysql_select_db($dbname);
      $retval = mysql_query( $sql, $conn);
      if(! $retval ) {
         die('Could not get data: ' . mysql_error());
       } 
      $row = mysql_fetch_assoc($retval); 
      $resultingQuote = $row['w100'];
   }


   if ($chosenWeight >= 250 && $chosenWeight < 500) {
      $sql = "SELECT w250 FROM `Gunsel rates` WHERE iata = '$q';";
      mysql_select_db($dbname);
      $retval = mysql_query( $sql, $conn);
      if(! $retval ) {
         die('Could not get data: ' . mysql_error());
       } 
      $row = mysql_fetch_assoc($retval); 
      $resultingQuote = $row['w250'];
   }


   if ($chosenWeight >= 500) {
      $sql = "SELECT w500 FROM `Gunsel rates` WHERE iata = '$q';";
      mysql_select_db($dbname);
      $retval = mysql_query( $sql, $conn);
      if(! $retval ) {
         die('Could not get data: ' . mysql_error());
       } 
      $row = mysql_fetch_assoc($retval); 
      $resultingQuote = $row['w500'];
   }  


   //if user enters other rate then '$resultingQuote' variable is re-written
   if ($a) {
      $resultingQuote = $a;
   }


   //DGR and TkPlus shipment types are considered as separate cases. The receive '$resultingQuote' from the previous if-statements and echo the output and close the connection
   if ($_GET['cargoType'] == 'DGR') {
      $q = strtoupper($q);
      $totalCost = $resultingQuote*1.25*$chosenWeight + $customsFee + $awbFee + 875;
      $totalCost = number_format($totalCost, 2, ',', ' '); 
      $resultingQuote = $resultingQuote*1.25;

     if ($a) {
         $resultingQuote = $a;
         $totalCost = $resultingQuote*$chosenWeight + $customsFee + $awbFee;
         $totalCost = number_format($totalCost, 2, ',', ' '); 
      }

      echo nl2br ("The quote for IATA code: '<em><strong>$q</strong></em>' with the weight of '<em><strong>$chosenWeight</strong></em>' kgs is: <h2><i>UAH $resultingQuote</i></h2> \n The total cost of airfreight would be: <h2><em>UAH \n$totalCost</em></h2>");
      mysql_free_result($retval);
      mysql_close($conn);
      exit();
   }


   if ($_GET['cargoType'] == 'TkPlus') {
      $q = strtoupper($q);
      $totalCost = $resultingQuote*1.25*$chosenWeight + $customsFee + $awbFee;
      $resultingQuote = $resultingQuote*1.25;
      $totalCost = number_format($totalCost, 2, ',', ' ');
       if ($a) {
         $resultingQuote = $a;
         $totalCost = $resultingQuote*$chosenWeight + $customsFee + $awbFee;
         $totalCost = number_format($totalCost, 2, ',', ' '); 
      }
      echo nl2br ("The quote for IATA code: '<em><strong>$q</strong></em>' with the weight of '<em><strong>$chosenWeight</strong></em>' kgs is: <h2><i>UAH $resultingQuote</i></h2> \n The total cost of airfreight would be: <h2><em>UAH \n $totalCost</em></h2>");
      mysql_free_result($retval);
      mysql_close($conn);
      exit();  
   }


   //the final block of the script uses '$resultingQuote' either from '45<99kg' or '100<249kg' or '250<499kg' or '>500kg' scenarios
   $q = strtoupper($q);
   $totalCost = $resultingQuote*$chosenWeight + $customsFee + $awbFee;
   $totalCost = number_format($totalCost, 2, ',', ' '); 
      echo nl2br ("The quote for IATA code: '<em><strong>$q</strong></em>' with the weight of '<em><strong>$chosenWeight</strong></em>' kgs is: <h2><i>UAH $resultingQuote</i></h2> \n The total cost of airfreight would be: <h2><em>UAH \n $totalCost</em></h2>");
      mysql_free_result($retval);
      mysql_close($conn);
?>

