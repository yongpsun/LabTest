<?php
    $host = '127.0.0.1';
    $db   = 'labtestcar';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';
    
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    try {
        $g_pdo = new PDO($dsn, $user, $pass, $options);
        
    } 
    catch (PDOException $ex) {
        $msg = "Failed to connect to db $db.\n"
            . $ex->getMessage();
        
        mylog( $msg );
    }
    
    function mylog( $msg )
    {
        $dir = __DIR__;
        $logFile = "$dir/labtest.log";

        error_log( $msg . "\n", 3, $logFile);
    }
    
    
    function returnHome( $msg )
    {
        $url = "Location: index.php";
        
        if( $msg ) {
            $url .= "?Error=$msg";
        }
        
        header( $url );
        exit();
    }
    
    
    function handleServerStatus( $status, $msg )
    {
        $return["Status"] = $status;
        $return["Msg"] = $msg;
        echo json_encode(  $return );
        exit();
    }
    
    
    function validateSession()
    {
        session_start();
        if( !isset($_SESSION) || !isset($_SESSION["UserName"] ) ) {
            $msg = "Please sign in before accessing the page.";
            
            returnHome( $msg );
        }
    }
    
    // case insensitive version of in_array()
    // https://gist.github.com/sepehr/6351397
    function in_arrayi( $needle, $haystack )
    {
        return in_array( strtolower($needle), 
                array_map('strtolower', $haystack) );
    }
    
    // case insensitive version of array_key_exists()
    // https://stackoverflow.com/questions/4240001/php-array-keys-case-insensitive-lookup
    function array_ikey_exists( $key, $haystack )
    {
        return array_key_exists( strtolower($key), 
                    array_change_key_case($haystack) );
    }
    
    class CAR
    {
        public $id = 0;
        public $MPG = null;
        public $Cylenders = 0;
        public $Make = "";
        public $Model = "";
        public $ModelYear = "";
        public $Origin = "";
        
        private $MakeId = 0;
        private $MakeModelId = 0;
        private $ModelYearId = 0;
        
        // Associative array $MakeList will has lower case Make for the key, 
        // MakeId as the value.
        private static $MakeList = array();
        
        // Associative array $MakeModelList will has lower Model for the key,
        // MakeModelId as the value.
        private static $MakeModelList = array();
        
        // Associative array $ModelYearList will has Year for the key,
        // ModelYearId as the value.
        private static $ModelYearList = array();
        
        static $carList;
        
        const ORIGINS = [ "US", "Europe", "Japan" ];

        function __construct() 
        {
        }


        static function getCarInfo( $carId )
        {
            global $g_pdo;
            
            $sql = "SELECT C.id, C.MPG, C.Cylenders, C.Origin,
                    MM.Model, M.Make, MY.Year AS ModelYear
                    FROM car AS C
                    LEFT JOIN makemodel AS MM
                    ON C.MakeModelId = MM.id
                    LEFT JOIN make AS M
                    ON MM.MakeId = M.id
                    LEFT JOIN modelyear AS MY
                    ON C.ModelYearId = MY.id
                    WHERE C.id = ?;
                ";
            
            $params = array( $carId );
           
            $carObj = null;

            try {
                $stmt = $g_pdo->prepare( $sql );
                $stmt->execute( $params);
                $stmt->setFetchMode(PDO::FETCH_CLASS, "CAR");
                $carObj = $stmt->fetch();
            }
            catch(PDOException $ex) {
                $msg = "Failed to get car info (id: $carId): \n"
                    . "SQL " . $sql . "\n"
                    . $ex->getMessage();
                    
                mylog( $msg );
                    
                $carObj = null;
            }

            return $carObj;
        }

        
        function deleteCar( $carId )
        {
            global $g_pdo;
            
            $succeeded = true;
            
            $sql = "UPDATE car 
                    SET Active = '0' 
                    WHERE id = ?;";
            
            $params = array( $carId );
            
            try {
                $stmt = $g_pdo->prepare( $sql );
                $succeeded = $stmt->execute( $params );
            }
            catch(PDOException $ex) {
                $msg = "Failed to delete car (id: $this->id): \n"
                    . "SQL " . $sql . "\n"
                    . $ex->getMessage();

                mylog( $msg );
                
                $succeeded = false;
            }
            
            return $succeeded;
        }

        function deleteAllCars()
        {
            global $g_pdo;
            
            $succeeded = true;
            
            $sql = "UPDATE car
                    SET Active = '0';";
            
            try {
                $stmt = $g_pdo->prepare( $sql );
                $succeeded = $stmt->execute();
            }
            catch(PDOException $ex) {
                $msg = "Failed to delete all cars: \n"
                    . "SQL " . $sql . "\n"
                    . $ex->getMessage();
                    
                mylog( $msg );
                    
                $succeeded = false;
            }

            return $succeeded;
        }
        

        function updateCar()
        {
            global $g_pdo;
            
            // Do not allow user to change Make/Model.
            // $succeeded = $this->updateMake();
            /*
            if( $succeeded ) {
                $succeeded = $this->updateMakeModel();
            }
            */
            // $succeeded = $this->findMakeModelIdById();

            $succeeded = $this->updateModelYear();
            
            if( $succeeded ) {
                $sql = "UPDATE car
                        SET Cylenders = ?, MPG = ?,
                        ModelYearId = ?, Origin = ? 
                        WHERE id = ?;";
                
                $params = array(
                                $this->Cylenders, 
                                $this->MPG, 
                                $this->ModelYearId, 
                                $this->Origin, 
                                $this->id );
                
                try {
                    $stmt = $g_pdo->prepare( $sql );
                    $succeeded = $stmt->execute( $params );
                }
                catch(PDOException $ex) {
                    $msg = "Failed to update car (id: $this->id): \n"
                        . "SQL " . $sql . "\n"
                        . $ex->getMessage();
                    
                    mylog( $msg );
                    
                    $succeeded = false;
                }
            }

            return $succeeded;
        }
        
        
        static function getMakeList()
        {
            global $g_pdo;
            
            $sql = "SELECT LOWER(Make), id FROM make";

            try {
                $stmt = $g_pdo->prepare( $sql );
                $stmt->execute();
                self::$MakeList = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            }
            catch(PDOException $ex) {
                $msg = "Failed to get Make list: \n"
                    . "SQL " . $sql . "\n"
                    . $ex->getMessage();
                
                mylog( $msg );
            }

            /*
            if( self::$MakeList ) {
                $msg = "makeList \n" . print_r( self::$MakeList, true );
                mylog( $msg );
            }
            else {
                $msg = "Failed to get Make list: \n";
                    
                mylog( $msg );
            } */
        }

        
        static function getMakeIdByMake( $Make )
        {
            $MakeId = null;
            
            if( isset($Make) && strlen($Make) > 0 ) {
                
                $MakeLC = strtolower( $Make );
                
                if( isset(self::$MakeList[$MakeLC]) ) {
                    $MakeId = self::$MakeList[$MakeLC];
                }
            }
            return $MakeId;
        }

        function findMakeId()
        {
            $this->MakeId = self::getMakeIdByMake($this->Make);
        }

        
        function findMakeModelIdById()
        {
            global $g_pdo;
            
            $sql = "SELECT C.MakeModelId, M.id AS MakeId
                    FROM car AS C
                    LEFT JOIN makemodel AS MM
                    ON C.MakeModelId = MM.id
                    LEFT JOIN make AS M
                    ON MM.MakeId = M.id
                    WHERE C.id = ?;
                ";
            
            $params = array( $this->id );
            
            $this->MakeModelId = null;
            $this->MakeId = null;
            try {
                $stmt = $g_pdo->prepare( $sql );
                $stmt->execute( $params);
                $row = $stmt->fetch();

                if( $row ) {
                    if( $row["MakeModelId"] ) {
                        $this->MakeModelId = $row["MakeModelId"];
                    }
                    if( $row["MakeId"] ) {
                        $this->MakeId = $row["MakeId"];
                    }
                }
            }
            catch(PDOException $ex) {
                $msg = "Failed to get car make id (id: $this->id): \n"
                    . "SQL " . $sql . "\n"
                    . $ex->getMessage();
                    
                mylog( $msg );
                $this->MakeModelId = null;
                $this->MakeId = null;
            }
            
            $succeeded = false;
            if( $this->MakeModelId && $this->MakeId ) {
                $succeeded = true;
            }
 
            return $succeeded;
        }
        

        function updateMake()
        {
            global $g_pdo;

            self::getMakeList();

            $this->findMakeId();
            $succeeded = true;
            if( !$this->MakeId ) {

                $sql = "INSERT INTO make (Make)
                    Value (?);";
                
                $params = array( $this->Make );
                
                try {
                    $stmt = $g_pdo->prepare( $sql );
                    $succeeded = $stmt->execute( $params );
    
                    if( $succeeded ) {
                        $this->MakeId = $g_pdo->lastInsertId();
                    }
                }
                catch(PDOException $ex) {
                    $msg = "Failed to add car Make (Make: $this->Make): \n"
                        . "SQL " . $sql . "\n"
                        . $ex->getMessage();
                    
                    mylog( $msg );
                    
                    $succeeded = false;
                }
            }

            return $succeeded;
        }

        // $ModelMakeList is an associative array with the Model as
        // the key and Make as the value. 
        static function addAllMakes( $ModelMakeList )
        {
            global $g_pdo;
            
            self::getMakeList();
            
            /*
            $msg = "In addAllMakes, all Makes: \n"
                . print_r( self::$MakeList, true );
            
            myLog( $msg);
            */
 
            $MakeToAddList = array();
            if( $ModelMakeList ) {
                
                foreach( $ModelMakeList as $Make ) {
                    $MakeId = self::getMakeIdByMake($Make);
                    
                    if( !$MakeId ) {
                        // Case insensitive version of in_array()
                        if( !in_arrayi($Make, $MakeToAddList) ) {
                            $MakeToAddList[] = $Make;
                        }
                    }
                }
            }
       
            $succeeded = true;

            if( $MakeToAddList ) {
                
                $sql = "INSERT INTO make (Make) VALUES ";
                
                $count = count( $MakeToAddList );
                $params = array();
                
                for( $i = 0; $i < $count; $i++ ) {
                    
                    $Make = $MakeToAddList[$i];
                    
                    $sql .= "(?)";
                    if( $i < $count - 1 ) {
                        $sql .= ", ";
                    }
                    $params[] = $Make;
                }
                
                try {
                    $stmt = $g_pdo->prepare( $sql );
                    $succeeded = $stmt->execute( $params );
                }
                catch(PDOException $ex) {
                    $msg = "Failed to add all car Makes: \n"
                        . "SQL " . $sql . "\n"
                        . "Parameters: " . print_r( $params, true )
                        . $ex->getMessage();
                    
                    mylog( $msg );
                    
                    $succeeded = false;
                }
            }
            return $succeeded;
        }
        
        
        static function getMakeModelList()
        {
            global $g_pdo;
            
            $sql = "SELECT LOWER(Model), id
                    FROM makemodel";
            
            try {
                $stmt = $g_pdo->prepare( $sql );
                $stmt->execute();
                self::$MakeModelList = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            }
            catch(PDOException $ex) {
                $msg = "Failed to get Make Model list: \n"
                    . "SQL " . $sql . "\n"
                    . $ex->getMessage();
                    
                mylog( $msg );
            }

            /*
            if( self::$MakeModelList ) {
                $msg = "makeModelList \n" . print_r( self::$MakeModelList, true );
                mylog( $msg );
            }
            else {
                $msg = "Failed to get Make Model list.";
                
                mylog( $msg );
            } */
        }
        
        
        static function getMakeModelIdByModel( $Model )
        {
            $MakeModelId = null;
            if( isset($Model) && strlen($Model) > 0 ) {
                
                $ModelLC = strtolower( $Model );
                if( isset(self::$MakeModelList[$ModelLC]) ) {
                    $MakeModelId = self::$MakeModelList[$ModelLC];
                }
            }
            
            return $MakeModelId;
        }
        
        
        function findMakeModelId()
        {
            $this->MakeModelId = self::getMakeModelIdByModel( $this->Model );
        }
        
 
        function updateMakeModel()
        {
            global $g_pdo;

            self::getMakeModelList();
            $this->findMakeModelId();
            
            $succeeded = true;
            if( !$this->MakeModelId ) {
                $sql = "INSERT INTO makemodel (MakeId, Model)
                            VALUES (?, ?);";
                
                $params = array( $this->MakeId, $this->Model );
                
                try {
                    $stmt = $g_pdo->prepare( $sql );
                    $succeeded = $stmt->execute( $params );
                    if( $succeeded ) {
                        $this->MakeModelId = $g_pdo->lastInsertId();
                    }
                }
                catch(PDOException $ex) {
                    $msg = "Failed to add car Make/Model "
                        . "(Make: $this->Make, Model: $this->Model): \n"
                        . "SQL " . $sql . "\n"
                        . $ex->getMessage();
                    
                    mylog( $msg );
                    
                    $succeeded = false;
                }
            }

            return $succeeded;       
        }
        
        
        // $ModelMakeList is an associative array with the Model as
        // the key and Make as the value.
        // This function should run after self::addAllMakes().
        static function addAllMakeModels( $ModelMakeList )
        {
            global $g_pdo;
            
            self::getMakeList();
            self::getMakeModelList();
            
            $ModelMakeIdToAddList = array();
            if( $ModelMakeList ) {
                
                foreach( $ModelMakeList as $Model => $Make ) {
                    
                    $MakeModelId = self::getMakeModelIdByModel($Model);
                    if( !$MakeModelId ) {
                        // Case insensitive version of array_ikey_exists.
                        if( !array_ikey_exists($Model, $ModelMakeIdToAddList) ) {
                            // $MakeId should never be null here, since 
                            // self::addAllMakes() should have run. 
                            $MakeId = self::getMakeIdByMake($Make);
                            $ModelMakeIdToAddList[$Model] = $MakeId;
                        }
                    }
                }
            }
            /*
            $msg = "In addAllMakeModels, ModelMakeIdToAddList : \n"
                . print_r($ModelMakeIdToAddList, true);
            
            mylog($msg);
            */
 
            $succeeded = true;
            
            if( $ModelMakeIdToAddList ) {
                
                $sql = "INSERT INTO makemodel (MakeId, Model) VALUES ";
                
                $count = count( $ModelMakeIdToAddList );
                $params = array();
                $i = 0;
                
                foreach( $ModelMakeIdToAddList as $Model => $MakeId  ) {
                    $sql .= "(?, ?)";
                    if( $i < $count - 1 ) {
                        $sql .= ", ";
                    }
                    $params[] = $MakeId;
                    $params[] = $Model;
                    
                    $i++;
                }
                
                try {
                    $stmt = $g_pdo->prepare( $sql );
                    $succeeded = $stmt->execute( $params );
                }
                catch(PDOException $ex) {
                    $msg = "Failed to add all car Make/Models: \n"
                        . "SQL " . $sql . "\n"
                        . "Parameters " . print_r($params, true)
                        . $ex->getMessage();
                        
                    mylog( $msg );
                        
                    $succeeded = false;
                }
            }
            return $succeeded;
        }
        
        
        static function getModelYearList()
        {
            global $g_pdo;
            
            $sql = "SELECT Year, id FROM modelyear";
            
            try {
                $stmt = $g_pdo->prepare( $sql );
                $stmt->execute();
                self::$ModelYearList = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                
            }
            catch(PDOException $ex) {
                $msg = "Failed to get Model Year list: \n"
                    . "SQL " . $sql . "\n"
                    . $ex->getMessage();
                    
                mylog( $msg );
            }

            /*
            if( self::$ModelYearList ) {
                $msg = "Model Year List \n" . print_r( self::$ModelYearList, true );
                 mylog( $msg );
             }
             else {
                 $msg = "Failed to get Model Year list.";
                 
                 mylog( $msg );
             } */
        }
        
        
        static function getModelYearIdByYear( $ModelYear )
        {
            $ModelYearId = null;
            
            if( isset($ModelYear) && strlen($ModelYear) > 0 ) {
                if( isset(self::$ModelYearList[$ModelYear] ) ) {
                    $ModelYearId = self::$ModelYearList[$ModelYear];
                }
            }
            
            return $ModelYearId;
        }
        
        
        function findModelYearId()
        {
            $this->ModelYearId = self::getModelYearIdByYear( $this->ModelYear );
        }
        
        function updateModelYear()
        {
            global $g_pdo;
            
            self::getModelYearList();
            
            $this->findModelYearId();
            
            $succeeded = true;
            if( !$this->ModelYearId ) {
                
                $sql = "INSERT INTO modelyear (Year)
                        VALUES (?);";
                
                $params = array( $this->ModelYear );
                
                try {
                    $stmt = $g_pdo->prepare( $sql );
                    $succeeded = $stmt->execute( $params );
                    
                    if( $succeeded ) {
                        $this->ModelYearId = $g_pdo->lastInsertId();
                    }
                }
                catch(PDOException $ex) {
                    $msg = "Failed to add Model Year (Year: $this->ModelYear): \n"
                        . "SQL " . $sql . "\n"
                        . $ex->getMessage();
                    
                    mylog( $msg );
                    
                    $succeeded = false;
                }
            }
            
            return $succeeded;
        }

        
        // $ModelYearList is an array containing all the Year
        // to be added. It may contain some Years that are already 
        // in db table ModelYear. 
        static function addAllModelYears( $ModelYearList )
        {
            global $g_pdo;
            
            self::getModelYearList();

            $ModelYearToAddList = array();
            if( $ModelYearList ) {
                
                foreach( $ModelYearList as $ModelYear ) {
                    $ModelYearId = self::getModelYearIdByYear($ModelYear);
                    
                    if( !$ModelYearId ) {
                        if( !in_array($ModelYear, $ModelYearToAddList) ) {
                            $ModelYearToAddList[] = $ModelYear;
                        }
                    }
                }
            }
            
            $succeeded = true;
            
            if( $ModelYearToAddList ) {
                
                $sql = "INSERT INTO modelyear (Year) VALUES ";
                
                $count = count( $ModelYearToAddList );
                $params = array();
                
                for( $i = 0; $i < $count; $i++ ) {
                    
                    $ModelYear = $ModelYearToAddList[$i];
                    
                    $sql .= "(?)";
                    if( $i < $count - 1 ) {
                        $sql .= ", ";
                    }
                    $params[] = $ModelYear;
                }
                
                try {
                    $stmt = $g_pdo->prepare( $sql );
                    $succeeded = $stmt->execute( $params );
                }
                catch(PDOException $ex) {
                    $msg = "Failed to add all car Model Years: \n"
                        . "SQL " . $sql . "\n"
                        . $ex->getMessage();
                        
                    mylog( $msg );
                        
                    $succeeded = false;
                }
            }

            return $succeeded;
        }
        
        
        static function getAllCarForeignIds( $carObjList )
        {
            if( $carObjList ) {
                self::getMakeList();
                self::getMakeModelList();
                self::getModelYearList();
                
                foreach( $carObjList as $carObj ) {
                    $carObj->findMakeId();
                    $carObj->findMakeModelId();
                    $carObj->findModelYearId();
                }
            }
        }
        
        
        static function addAllCars( $carObjList, $ModelMakeList, $ModelYearList )
        {
            global $g_pdo;

            if( $carObjList ) {
                $succeeded = self::addAllMakes($ModelMakeList);
                
                if( $succeeded ) {
                    $succeeded = self::addAllMakeModels($ModelMakeList);
                }
                
                if( $succeeded ) {
                    $succeeded = self::addAllModelYears($ModelYearList);
                }
                
                if( $succeeded ) {
                    self::getAllCarForeignIds( $carObjList );
                    
                    //$msg = "After getAllCarForeignIds, carObjList: \n"
                    //    . print_r($carObjList, true);
                        
                    //myLog($msg);
                    
                    $count = count( $carObjList );
                    $params = array();

                    $sql = "INSERT INTO car (MakeModelId, MPG, Cylenders, ModelYearId, Origin) 
                            VALUES ";
                    
                    for( $i = 0; $i < $count; $i++ ) {
 
                        $carObj = $carObjList[$i];
                        $sql .= "(?, ?, ?, ?, ?)";
                        if( $i < $count - 1 ) {
                            $sql .= ", ";
                        }
                        $params[] = $carObj->MakeModelId;
                        $params[] = $carObj->MPG;
                        $params[] = $carObj->Cylenders;
                        $params[] = $carObj->ModelYearId;
                        $params[] = $carObj->Origin;
                    }
                    
                    try {
                        $stmt = $g_pdo->prepare( $sql );
                        $succeeded = $stmt->execute( $params );
                    }
                    catch(PDOException $ex) {
                        $msg = "Failed to add all cars: \n"
                            . "SQL " . $sql . "\n"
                            . $ex->getMessage();
                        
                        mylog( $msg );
                        
                        $succeeded = false;
                    }
                }
            }

            return $succeeded;
        }
        
        
        static function getAllCars()
        {
            global $g_pdo;
            
            $sql = "SELECT C.id, C.MPG, C.Cylenders, C.Origin,
                    MM.Model, M.Make, MY.Year AS ModelYear
                    FROM car AS C
                    LEFT JOIN makemodel AS MM
                    ON C.MakeModelId = MM.id
                    LEFT JOIN make AS M
                    ON MM.MakeId = M.id
                    LEFT JOIN modelyear AS MY
                    ON C.ModelYearId = MY.id
                    WHERE C.Active = '1' 
                    ORDER BY M.Make, MM.Model ASC
                ";

            $stmt = $g_pdo->prepare( $sql );
            $stmt->execute();
            self::$carList = $stmt->fetchAll(PDO::FETCH_CLASS, "CAR");
            
            // $msg = "carList \n" . print_r( self::$carList, true );
            //mylog( $msg );
        }
        
        
        static function validateCarData( $Make, $Model, $MPG,
                                $Cylenders, $ModelYear, $Origin,
                                $skipMakeModel, &$msg )
        {
            $valid = true;
            
            if( !$skipMakeModel ) {
                if( !isset($Make) || strlen($Make) < 1 ) {
                    $msg = "Car make cannot be empty.";
                    $valid = false;
                }
                if( $valid ) {
                    if( !preg_match("/^[a-z0-9\-]+$/i", $Make) ) {
                        $msg = "Only alphanumeric characters or dashes are allowed for car make.";
                        $valid = false;
                    }
                }
            
                if( $valid ) {
                    if( !isset($Model) || strlen($Model) < 1 ) {
                        $msg = "Car model cannot be empty.";
                        $valid = false;
                    }
                }
            }
            
            if( $valid ) {
                if( !ctype_digit($Cylenders) ) {
                    $msg = "Car cylenders must be an integer.";
                    $valid = false;
                }
            }
            if( $valid ) {
                if( !is_numeric($MPG) ) {
                    $msg = "MPG must be a number.";
                    $valid = false;
                }
            }
            if( $valid ) {
                if( !preg_match("/^\d{2}$/", $ModelYear) ) {
                    $msg = "Car model year must be a 2-digit number.";
                    $valid = false;
                }
            }
            
            if( $valid ) {
                // Case insensitive version of in_array().
                if( !in_arrayi($Origin, self::ORIGINS) ) {
                    $msg = "Car origin must be 'US', 'Europe', or 'Japan'.";
                    $valid = false;
                }
            }
            
            return $valid;
        }
    }
    
?>