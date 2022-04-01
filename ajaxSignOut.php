<?php
    $dir = __DIR__;
    require_once("$dir/lib.php");
    
    $origPage = null;
    if( isset($_POST["page"]) ) {
        $origPage =  trim( html_entity_decode( $_POST["page"] ) );
    }

    session_start();

    // $msg = "_SESSION in ajaxSignOut, " . print_r($_SESSION, true);
    // mylog($msg);
    
    if( !isset($_SESSION) || !isset($_SESSION["UserName"] ) ) {
        $msg = "Please sign in before accessing this page.";
        
        if( $origPage ) {
            handleServerStatus( "Failure", $msg );
        }
        else {
            returnHome($msg);
        }
    }

    unset( $_SESSION["UserName"] );
    session_destroy();
    
    $msg = "";
    if( $origPage ) {
        handleServerStatus( "Success", $msg );
    }
    else {
        returnHome( $msg );
    }
    
    
    exit();
?>