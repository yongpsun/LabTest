<?php
    $dir = __DIR__;
    require_once("$dir/lib.php");

    validateSession();
    
    $carId = null;

    if( isset($_POST["carId"]) ) {
        $carId =  trim( $_POST["carId"] );
    }

    if( !$carId ) {
        $msg = "No car has been specified to be deleted.";
        handleServerStatus( "Failure", $msg );
    }
    
    $carObj = new CAR();
    $succeeded = $carObj->deleteCar( $carId );
 
    if( $succeeded ) {
        //$return["Status"] = "Success";
        //$return["Msg"] = "Car (Id: $carId) has been updated.";
        
        $msg = "Car (Id: $carId) has been deleted.";
        handleServerStatus( "Success", $msg );
    }
    else {
        $msg = "Failed to delete car (Id: $carId).";
        handleServerStatus( "Failure", $msg );
    }
?>