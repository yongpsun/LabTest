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

?>


<script type="text/javascript">

$(function() {
	
	$("#btnViewPage").on("click", function() {
		window.location.href = "./view.php";
	});

	$("#btnClear").on("click", function() {
		//$("#inputUserName").val("");
		//$("#inputPassword").val("");
		
		window.location.href = "./index.php";
	});
});

</script>

<div class="form-row" style="margin:30px;">
    <div class="form-group col-md-2">
      <input type="button" class="form-control btn btn-link" id="btnViewPage" style="text-align: left;" value="View Cars"/>
    </div>
</div>


<div class="container" style="margin:30px;">
    <form action="ajaxSignIn.php" method="POST">

        <h4>Sign In</h4>
		<br/>
        <?php if (isset($_GET['Error'])) { ?>
            <p id="pError" style="color: red;"><?php echo $_GET['Error']; ?></p>
        <?php 
            }
            else {
          ?>
          	<p id="pError" style="color: red;"></p>
         <?php 
            }
         ?>

		<div class="form-group row">
        	<label for="inputUserName" class="col-sm-2 col-form-label">User Name</label>
        	<div class="col-md-5">
				<input type="text" class="form-control" id="inputUserName" name="inputUserName">
			</div>
		</div>
     	<div class="form-group row">
    		<label for="inputPassword" class="col-sm-2 col-form-label">Password</label>
            <div class="col-md-5">
    			<input type="password" class="form-control" id="inputPassword" name="inputPassword">
            </div>
        </div>

		<div class="form-group row">
        	<div class="col-md-5">
    			<input type="Submit" class="btn btn-info" id="btnSignIn" value="Sign In"/>
    		</div>
    		<div class="col-md-5">
    			<input type="button" class="btn btn-info" id="btnClear" value="Clear"/>
    		</div>
        </div>
     </form>
</div>

</body>
</html>