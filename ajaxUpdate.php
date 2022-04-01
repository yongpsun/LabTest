<?php

    $dir = __DIR__;
    require_once("$dir/lib.php");
    
    validateSession();
    
    $carId = null;
    $Cylenders = "";
    $MPG = "";
    $ModelYear = "";
    $Origin = "";
 
    if( isset($_POST["carId"]) ) {
        $carId = trim( $_POST["carId"] );
    }

    // Do not allow user to change Make/Model.
    // $Make =  trim( html_entity_decode( $_POST["Make"] ) );
    // $Model =  trim( html_entity_decode( $_POST["Model"] ) );
    
    if( isset($_POST["Cylenders"]) ) {
        $Cylenders =  trim( html_entity_decode( $_POST["Cylenders"] ) );
    }
    
    if( isset($_POST["MPG"]) ) {
        $MPG =  trim( html_entity_decode( $_POST["MPG"] ) );
    }
    
    if( isset($_POST["ModelYear"]) ) {
        $ModelYear =  trim( html_entity_decode( $_POST["ModelYear"] ) );
    }
    
    if( isset($_POST["Origin"]) ) {
        $Origin =  trim( html_entity_decode( $_POST["Origin"] ) );
    }

    /*
    $msg = "@@@@ ajaxUpdate, carID " . $carId
            . ", Origin " . $Origin;
     mylog( $msg );
    */

    if( !$carId ) {
        $msg = "No car has been specified to be updated.";
        handleServerStatus( "Failure", $msg );
    }

    $msg = "";
    $valid = CAR::validateCarData( "", "", $MPG,
                    $Cylenders, $ModelYear, $Origin,
                    true, $msg );
    
    if( !$valid ) {
        handleServerStatus( "Failure", $msg );
    }

    $carObj = new CAR();
    $carObj->id = $carId;
    $carObj->Cylenders = $Cylenders;
    $carObj->MPG = $MPG;
    $carObj->ModelYear = $ModelYear;
    $carObj->Origin = $Origin;

    $succeeded = $carObj->updateCar();
 
    if( $succeeded ) {
        $msg = "Car (Id: $carId) has been updated.";
        handleServerStatus( "Success", $msg );
    }
    else {
        $msg = "Failed to update car (Id: $carId).";
        handleServerStatus( "Failure", $msg );
    }
?>