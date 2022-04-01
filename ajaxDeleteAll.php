<?php
    $dir = __DIR__;
    require_once("$dir/lib.php");
    
    validateSession();

    $succeeded = CAR::deleteAllCars();
    
    if( $succeeded ) {
        $msg = "All cars have been deleted.";
        handleServerStatus( "Success", $msg );
    }
    else {
        $msg = "Failed to delete all cars.";
        handleServerStatus( "Failure", $msg );
    }

?>