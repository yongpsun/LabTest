<?php
    $dir = __DIR__;
    require_once("$dir/lib.php");

    validateSession();
    
    // Associative array with Model as the key, 
    // and Make as the value. 
    $ModelMakeList = array();
    
    if( isset($_FILES["inputUpload"]) ) {
        $err = $_FILES["inputUpload"]["error"];
        $fileName = $_FILES["inputUpload"]["name"];

        if( $err !== UPLOAD_ERR_OK) {
            $msg = "Failed to upload file '$fileName'. Error " . $err;
            mylog($msg);
        }
        else {
            $msg = "File '$fileName' has been uploaded succeessfully.";
            mylog($msg);
            
            $tmpName = $_FILES["inputUpload"]["tmp_name"];
            
            if( $tmpName && file_exists($tmpName) ) {
                $csvDataList = array_map( "str_getcsv", file($tmpName) );
                $carObjList = array();
                $ModelMakeList = array();
                $ModelYearList = array();
                
                foreach( $csvDataList as $rowData ) {
                    
                    $carData = str_getcsv( $rowData[0], ";" );
                    $carObj = getCarObjList( $carData, $ModelMakeList, $ModelYearList );
                    
                    if( $carObj ) {
                        $carObjList[] = $carObj;
                    }
                    
                }
                
                /*
                $msg = "After parsing the data, carObjList: \n"
                    . print_r($carObjList, true);
                    
                myLog($msg);

                $msg = "After parsing the data, ModelMakeList: \n"
                    . print_r($ModelMakeList, true);
                
                myLog($msg);
                
                $msg = "After parsing the data, ModelYearList: \n"
                    . print_r($ModelYearList, true);
                    
                myLog($msg);
                */
                
                CAR::addAllCars( $carObjList, $ModelMakeList, $ModelYearList );
            }
            else {
                $msg = "Temp file '$tmpName' for upload not found.";
                mylog($msg);
            }
        }
    }

    header("Location: manage.php");
    exit();
    
    
    function getCarObjList( $carData, &$ModelMakeList, &$ModelYearList )
    {
        $carObj = null;
        if( $carData ) {
            $MakeModel = $carData[0];
            
            $tempList = explode( " ", $MakeModel, 2);
            
            $Make = "";
            $Model = "";
            if( isset($tempList) ) {
                if( isset($tempList[0]) ) {
                    $Make = $tempList[0];
                }
                
                if( isset($tempList[1]) ) {
                    $Model = $tempList[1];
                }
            }

            $MPG = $carData[1];
            $Cylenders = $carData[2];
            $ModelYear = $carData[7];
            $Origin = $carData[8];

            $msg = "";
            $valid = CAR::validateCarData( $Make, $Model, $MPG,
                                $Cylenders, $ModelYear, $Origin,
                                false, $msg );

            if( !$valid ) {
                myLog($msg);
                
                $msg = "Uploaded data in this row will be filtered out: \n"
                    . "Make: $Make, Model: $Model, "
                    . "MPG: $MPG, Cylenders: $Cylenders, "
                    . "ModelYear: $ModelYear, Origin: $Origin";
                    
                mylog($msg);
            }
            else {
                if( isset($Model) && strlen($Model) > 0 ) {
                    // Case insensitive version of array_key_exists().
                    if( !array_ikey_exists($Model, $ModelMakeList) ) {
                        $ModelMakeList[$Model] = $Make;
                    }
                }
 
                if( !in_array($ModelYear, $ModelYearList) )  {
                    $ModelYearList[] = $ModelYear;
                }
                
                $carObj = new CAR();
                $carObj->Make = $Make;
                $carObj->Model = $Model;
                $carObj->MPG = $MPG;
                $carObj->Cylenders = $Cylenders;
                $carObj->ModelYear = $ModelYear;
                $carObj->Origin = $Origin;
            }
        }
        
        return $carObj;
    }
?>