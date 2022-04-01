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

CAR::getAllCars();

$totalCount = 0;
if( CAR::$carList ) {
    $totalCount = count( CAR::$carList );
}
?>

<script type="text/javascript">

$(function() {
	
	$("#btnSignInPage").on('click', function() {
		window.location.href = "./index.php";
	});
	
});

</script>


<div class="form-row" style="margin:30px;">
    <div class="form-group col-md-4">
      <input type="button" class="form-control btn btn-link" id="btnSignInPage" style="text-align: left;" value="Go to Sign In Page"/>
    </div>
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
        </tr>
      </thead>
      <tbody>
      <?php foreach( CAR::$carList as $carObj ) {?>
        <tr>
          <th scope="row" class="d-none"><?php echo $carObj->id;?></th>
          <td><?php echo $carObj->Make;?></td>
          <td><?php echo $carObj->Model;?></td>
          <td><?php echo $carObj->Cylenders;?></td>
          <td><?php echo $carObj->MPG;?></td>
          <td><?php echo $carObj->ModelYear;?></td>
          <td><?php echo $carObj->Origin;?></td>
        </tr>
       <?php }?>
      </tbody>
    </table>
</div>

</body>
</html>