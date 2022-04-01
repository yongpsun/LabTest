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

    CAR::getAllCars();
    
    $totalCount = 0;
    if( CAR::$carList ) {
        $totalCount = count( CAR::$carList );
    }
?>

<script type="text/javascript">

$(function() {

	 $(".btnUpdate").on('click', function() {
		var elemId = $(this).prop("id");
		var idPieces = elemId.split("_");
		var carId = idPieces[1];
    	//alert( "Update button found carID " +  carId);
    	
    	var url = "update.php?carId=" + carId;
    	window.location.replace(url);
    });


    $(".btnDelete").on('click', function() {
		var elemId = $(this).prop("id");
		var idPieces = elemId.split("_");
		var carId = idPieces[1];
    	// alert( "Delete button found carID " +  carId);
    	
    	var msg = "Do you really want to delete car (id: " 
    			+ carId + ")?";
  
    	var answer = confirm( msg );
  
    	if( answer ) {
    		deleteCar(carId);
    	}
    });
    
    
     $("#btnDeleteAll").on('click', function() {
    	
    	var msg = "Do you really want to delete all cars?";
    	var answer = confirm( msg );
  
    	if( answer ) {
    		deleteAllCars();
    	}
    });
    
    
	$("#btnSignOut").on('click', function() {

    	var dataToSend = {"page" : "manage.php"};
        $.ajax({
          	url: 'ajaxSignOut.php',
          	type: 'POST',
     		dataType: "JSON",
     		data: dataToSend,
     		success: function(response){

              	if(response.Status == "Success" ) {
              		//alert("Success " + response.Msg);
          			window.location.href = "./index.php";
              	}
              	else if (response.Status == "Failure") {
              		alert("Failure " + response.Msg);
              		window.location.href = "./index.php";
              	}
          	},
          	error: function(response) {
          		alert("Failed to Sign out.");
          	}
        });
    });
    
});


function deleteCar( carId )
{
	var dataToSend = {"carId" : carId};

    $.ajax({
		url: 'ajaxDelete.php',
      	type: 'POST',
 		dataType: "JSON",
      	data: dataToSend,
      	success: function(response){

          	if(response.Status == "Success" ) {
          		//alert("Success " + response.Msg);
          		window.location.href = window.location.href;
          	}
          	else if (response.Status == "Failure") {
          		alert("Failure " + response.Msg);
          	}
      	},
      	error: function(response) {
      		alert("Failed to delete car.");
      	}
    });
}


function deleteAllCars()
{
    $.ajax({
		url: 'ajaxDeleteAll.php',
      	type: 'POST',
 		dataType: "JSON",
      	success: function(response){

          	if(response.Status == "Success" ) {
          		//alert("Success " + response.Msg);
          		window.location.href = window.location.href;
          	}
          	else if (response.Status == "Failure") {
          		alert("Failure " + response.Msg);
          	}
      	},
      	error: function(response) {
      		alert("Failed to delete all cars.");
      	}
    });
}

</script>


<div class="form-row" style="margin:30px;">
    <div class="form-group col-md-4">
      <input type="button" class="form-control btn btn-link" id="btnDeleteAll" style="text-align: left;" value="Delete All CARS"/>
    </div>
    <div class="form-group col-md-4">
      <input type="button" class="form-control btn btn-link" id="btnSignOut" style="text-align: left;"value="Sign Out"/>
    </div>
</div>

<div class="container" style="margin:30px;">
    <form action="ajaxUpload.php" method="POST" accept-charset="utf-8" enctype="multipart/form-data">
		<div class="form-group col-md-6">
      		<input type="file" name="inputUpload" class="form-control form-control-file" id="inputUpload"/>
    	</div>
    	<div class="form-group col-md-3">
          <input type="submit" class="form-control btn-info" id="btnLoadCsv" value="Load CSV"/>
        </div>
    </form>
</div>

<div class="container" style="margin:30px;">
	<p id="pCars" style="color:blue;font-size:28px;text-align: left;">CARS</p>
    <p id="pCars" style="font-size:16px;text-align: left;">Total Count: <?php echo $totalCount;?></p>
    <br/>
    <table id="tblCars" class="table table-striped table-bordered table-hover">
      <thead>
        <tr>
          <th scope="col" class="d-none">Id</th>
          <th scope="col">Make</th>
          <th scope="col">Model</th>
          <th scope="col">Cylenders</th>
          <th scope="col">MPG</th>
          <th scope="col">Model Year</th>
          <th scope="col">Origin</th>
          <th scope="col"></th>
          <th scope="col"></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach( CAR::$carList as $carObj ) {?>
        <tr>
          <th scope="row" class="d-none carId"><?php echo $carObj->id;?></th>
          <td><?php echo $carObj->Make;?></td>
          <td><?php echo $carObj->Model;?></td>
          <td><?php echo $carObj->Cylenders;?></td>
          <td><?php echo $carObj->MPG;?></td>
          <td><?php echo $carObj->ModelYear;?></td>
          <td><?php echo $carObj->Origin;?></td>
          <td><input type="button" value="Update" id="btnUpdate_<?php echo $carObj->id;?>" class="btnUpdate btn btn-info btn-sm"></td>
          <td><input type="button" value="Delete" id="btnDelete_<?php echo $carObj->id;?>" class="btnDelete btn btn-info btn-sm"></td>
        </tr>
       <?php }?>
      </tbody>
    </table>
</div>

</body>
</html>