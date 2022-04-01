<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Lab Test</title>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

<!-- jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!-- Popper JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>
<body>

<?php
    $dir = __DIR__;
    require_once("$dir/lib.php");

    validateSession();

    $carId = "";
    if( isset($_GET["carId"]) ) {
        $carId = $_GET["carId"];
    }

    $carObj = null;
    if( $carId ) {
        $carObj = CAR::getCarInfo($carId);
    }
    
    function getCarProp( $carObj, $prop )
    {
        $rtn = "";
        if( $carObj ) {
            $rtn = $carObj->$prop;
            
            if( !isset($rtn) ) {
                $rtn = "";
            }
        }
        echo $rtn;
    }
?>

<script type="text/javascript">

$(function() {

	$("#inputOrigin").val( "<?php getCarProp( $carObj, "Origin" );?>" );
	
	$("#btnUpdate").on('click', function() {
		updateCar();
	});
	
	$("#btnCancel").on('click', function() {
		window.location.href = "./manage.php";
	});
	
});


function updateCar()
{
	var carId = "<?php echo $carId;?>";
	// var Make = $.trim( $("#inputMake").val() );
	// var Model = $.trim( $("#inputModel").val() );
	var Cylenders = $.trim( $("#inputCylenders").val() );
	var MPG = $.trim( $("#inputMPG").val() );
	var ModelYear = $.trim( $("#inputModelYear").val() );
	var Origin = $.trim( $("#inputOrigin").val() );

	/*
	var msg = "For update carId " + carId 
			+ ", Cylenders " + Cylenders
			+ ", MPG " + MPG
			+ ", ModelYear " + ModelYear
			+ ", Rrigin " + Origin;
		
	alert( msg );
	*/

	/*
	if( Make === "" ) {
		alert( "Car make cannot be empty." );
		return false;
	}

	if( !Make.match(/^[0-9a-z\-]+$/i) ) {
		alert( "Only alphanumeric characters or dashes are allowed for car make." );
		
		return false;
	}

	if( Model === "" ) {
		alert( "Car model cannot be empty." );
		return false;
	}
	*/

	if( !Cylenders.match(/^[0-9]+$/i) ) {
		alert( "Car cylenders must be an integer." );
		return false;
	}

	if( isNaN(MPG) ) {
		alert( "MPG must be a number." );
		return false;
	}

	if( !/^\d{2}$/.test(ModelYear) ) {
		alert( "Car model year must be a 2-digit number." );
		return false;
	}

	var dataToSend = { "carId" : carId,
					"Cylenders" : Cylenders,
					"MPG" : MPG,
					"ModelYear" : ModelYear,
					"Origin" : Origin,
				};

	$.ajax({
		url: 'ajaxUpdate.php',
      	type: 'POST',
 		dataType: "JSON",
      	data: dataToSend,
		success: function(response){

          if(response.Status == "Success" ) {
          	//alert("Success " + response.Msg);
          	window.location.href = "./manage.php";
          }
          else if (response.Status == "Failure") {
          	alert("Failure: " + response.Msg);
          }
      },
      error: function(response) {
      	alert("Failed to update car.");
      }
    });

    return false;
}
</script>

<div class="container" style="margin:30px;">
	<div class="form-group row">
        <label for="inputMake" class="col-sm-2 col-form-label">Make</label>
        <div class="col-md-5">
			<input type="text" class="form-control" id="inputMake" disabled value="<?php getCarProp( $carObj, "Make" );?>">
        </div>
	</div>
 	<div class="form-group row">
		<label for="inputModel" class="col-sm-2 col-form-label">Model</label>
        <div class="col-md-5">
			<input type="text" class="form-control" id="inputModel" disabled value="<?php getCarProp( $carObj, "Model" );?>">
        </div>
    </div>
    <div class="form-group row">
		<label for="inputCylenders" class="col-sm-2 col-form-label">Cylenders</label>
        <div class="col-md-5">
			<input type="text" class="form-control" id="inputCylenders" value="<?php getCarProp( $carObj, "Cylenders" );?>">
        </div>
    </div>
    <div class="form-group row">
		<label for="inputMPG" class="col-sm-2 col-form-label">MPG</label>
        <div class="col-md-5">
			<input type="text" class="form-control" id="inputMPG" value="<?php echo getCarProp( $carObj, "MPG" );?>">
        </div>
    </div>
    <div class="form-group row">
		<label for="inputModelYear" class="col-sm-2 col-form-label">Model Year</label>
        <div class="col-md-5">
			<input type="text" class="form-control" id="inputModelYear" value="<?php getCarProp( $carObj, "ModelYear" );?>">
        </div>
    </div>
    <div class="form-group row">
		<label for="inputOrigin" class="col-sm-2 col-form-label">Origin</label>
        <div class="col-md-5">
			 <select class="form-control" id="inputOrigin" name="inputOrigin">
        		<option value="US">US</option>
        		<option value="Europe">Europe</option>
        		<option value="Japan">Japan</option>
      		</select>
        </div>
    </div>
    <div class="form-group row">
    	<div class="col-md-5">
			<input type="button" class="btn btn-info" id="btnUpdate" value="Update"/>
		</div>
		<div class="col-md-5">
			<input type="button" class="btn btn-info" id="btnCancel" value="Cancel"/>
		</div>
    </div>
</div>

</body>
</html>