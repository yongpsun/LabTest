<?php

    $dir = __DIR__;
    require_once("$dir/lib.php");
    
    $userName = "";
    $password = "";
    if( isset($_POST["inputUserName"]) ) {
        $userName = trim( html_entity_decode( $_POST["inputUserName"] ) );
    }
    
    if( isset($_POST["inputPassword"]) ) {
        $password = trim( html_entity_decode( $_POST["inputPassword"] ) );
    }
    
    if( !isset($userName) || strlen($userName)  < 1 ) {
        $msg = "User name is required.";
        returnHome( $msg );
    }

    if( !isset($password) || strlen($password) < 1 ) {
        
        $msg = "Password is required.";
        returnHome( $msg );
    }
    
    if( strlen($password) < 6 ) {
        $msg = "Password must contain at least 6 characters.";
        returnHome( $msg );
    }
    
    verifyUser($userName, $password);

    
    function verifyUser( $userName, $password )
    {
        global $g_pdo;
        
        //$hashedPwd = password_hash( $password, PASSWORD_DEFAULT );
        // $msg = "password: $password, hashed Pwd " . $hashedPwd;
        // myLog($msg);
        
        $sql = "SELECT * FROM user WHERE UserName = ? 
                AND Active = '1';";

        $params = array( $userName );
        
        $userInfo = null;
        
        try {
            $stmt = $g_pdo->prepare( $sql );
            $stmt->execute( $params );

            $userInfo = $stmt->fetch( PDO::FETCH_ASSOC );
 
        }
        catch(PDOException $ex) {
            $msg = "Failed to find user (User Name: $userName): \n"
                . "SQL " . $sql . "\n"
                . $ex->getMessage();
                
             mylog( $msg );
             
             $userInfo = null;
        }
        
        if( !$userInfo ) {
            $msg = "User $userName invalid.";
            returnHome( $msg );
        }

        $isValidPwd = password_verify($password, $userInfo["Password"] );

        if( !$isValidPwd ) {
            $msg = "Password invalid.";
            returnHome( $msg );
        }
        
        // Sign in succeeded!
        session_start();
        $_SESSION["UserName"] = $userName;
        
        //$msg = "_SESSION in ajaxSignIn, " . print_r($_SESSION, true);
        //mylog($msg);
        
        header("Location: manage.php");
        exit();
    }

?>