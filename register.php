<?php include_once("includes/basic_includes.php");?>
<?php include_once("functions.php"); ?>
<?php 
// Call register() and capture any feedback message
$message = register() ?: ['type' => 'error', 'text' => 'Registration failed.'];
?>
<!DOCTYPE HTML>
<html>
<head>
<title>Find Your Perfect Partner - Matrimony | Register :: Matrimony</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<script type="application/x-javascript"> 
    addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); 
    function hideURLbar(){ window.scrollTo(0,1); } 
</script>

<link href="css/bootstrap-3.1.1.min.css" rel='stylesheet' type='text/css' />
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<link href="css/style.css" rel='stylesheet' type='text/css' />
<link href='//fonts.googleapis.com/css?family=Oswald:300,400,700' rel='stylesheet' type='text/css'>
<link href='//fonts.googleapis.com/css?family=Ubuntu:300,400,500,700' rel='stylesheet' type='text/css'>
<link href="css/font-awesome.css" rel="stylesheet">

<!-- Custom style for background -->
<style>
.grid_3 {
    background-image: url('images/pic12.png'); /* Ensure this path is correct */
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center center;
    padding: 50px 0;
}

/* Style for feedback messages */
.message {
    text-align: center;
    margin-bottom: 20px;
    padding: 10px;
    border-radius: 5px;
}

.message.success {
    color: green;
    background-color: #e0ffe0;
}

.message.error {
    color: red;
    background-color: #ffe0e0;
}
</style>

<script>
$(document).ready(function(){
    $(".dropdown").hover(            
        function() {
            $('.dropdown-menu', this).stop(true, true).slideDown("fast");
            $(this).toggleClass('open');        
        },
        function() {
            $('.dropdown-menu', this).stop(true, true).slideUp("fast");
            $(this).toggleClass('open');       
        }
    );
});
</script>
</head>
<body>

<!-- ============================  Navigation Start =========================== -->
<?php include_once("includes/navigation.php");?>
<!-- ============================  Navigation End ============================ -->

<div class="grid_3">
  <div class="container">
   <div class="breadcrumb1">
     <ul>
        <a href="index.php"><i class="fa fa-home home_1"></i></a>
        <span class="divider"> | </span>
        <li class="current-page">Register</li>
     </ul>
   </div>
   <!-- Display feedback message if exists -->
   <?php if (!empty($message)): ?>
       <div class="message <?php echo $message['type']; ?>">
           <?php echo $message['text']; ?>
       </div>
   <?php endif; ?>
   <div class="services">
   	  <div class="col-sm-6 login_left">
	     <form action="" method="POST">
		     <div class="form-group">
                <label for="edit-name">Username <span class="form-required" title="This field is required.">*</span></label>
                <input type="text" id="edit-name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" size="60" maxlength="60" class="form-text red-box">
            </div>

		    <div class="form-group">
		      <label for="edit-pass">Password <span class="form-required" title="This field is required.">*</span></label>
		      <input type="text" id="edit-pass" name="pass" size="60" maxlength="128" class="form-text red-box">
		    </div>
		    <div class="form-group">
		      <label for="edit-pass-confirm">Confirm Password <span class="form-required" title="This field is required.">*</span></label>
		      <input type="text" id="edit-pass-confirm" name="pass_confirm" size="60" maxlength="128" class="form-text red-box">
		    </div>
		    <div class="form-group">
		      <label for="edit-email">Email <span class="form-required" title="This field is required.">*</span></label>
		      <input type="email" id="edit-email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" size="60" maxlength="255" class="form-text red-box">
		    </div>
        <div class="form-group">
    <label for="edit-religion">Religion <span class="form-required" title="This field is required.">*</span></label>
    <select name="religion" id="edit-religion" class="form-text red-box" required>
        <option value="">Select Religion</option>
        <?php
        $religions = ['Hindu', 'Muslim', 'Christian', 'Sikh', 'Jain', 'Buddhist', 'Parsi', 'Jewish', 'Other'];
        foreach ($religions as $religionOption) {
            $selected = (isset($_POST['religion']) && $_POST['religion'] === $religionOption) ? 'selected' : '';
            echo "<option value=\"$religionOption\" $selected>$religionOption</option>";
        }
        ?>
    </select>
</div>

		    <div class="age_select">
		      <label for="edit-pass">Date of Birth <span class="form-required" title="This field is required.">*</span></label>
		        <div class="age_grid">
		         <div class="col-sm-4 form_box">
                  <div class="select-block1">
                    <select name="day">
	                    <option value="">Date</option>
	                    <?php for ($i=1; $i<=31; $i++) echo "<option value=\"$i\" " . (isset($_POST['day']) && $_POST['day'] == $i ? 'selected' : '') . ">$i</option>"; ?>
                    </select>
                  </div>
                </div>
                <div class="col-sm-4 form_box2">
                   <div class="select-block1">
                    <select name="month">
	                    <option value="">Month</option>
	                    <?php 
	                    $months = array(
	                      "01" => "January", "02" => "February", "03" => "March", "04" => "April",
	                      "05" => "May", "06" => "June", "07" => "July", "08" => "August",
	                      "09" => "September", "10" => "October", "11" => "November", "12" => "December"
	                    );
	                    foreach ($months as $num => $name) echo "<option value=\"$num\" " . (isset($_POST['month']) && $_POST['month'] == $num ? 'selected' : '') . ">$name</option>";
	                    ?>
                    </select>
                  </div>
                 </div>
                 <div class="col-sm-4 form_box1">
                   <div class="select-block1">
                    <select name="year">
	                    <option value="">Year</option>
	                    <?php 
	                    for ($y = 1980; $y <= 2006; $y++) echo "<option value=\"$y\" " . (isset($_POST['year']) && $_POST['year'] == $y ? 'selected' : '') . ">$y</option>"; 
	                    ?>
                    </select>
                   </div>
                  </div>
                  <div class="clearfix"> </div>
                 </div>
              </div>
              <div class="form-group form-group1">
                <label class="col-sm-7 control-lable" for="sex">Sex : </label>
                <div class="col-sm-5">
                    <div class="radios">
				        <label for="radio-01" class="label_radio">
				            <input type="radio" name="gender" value="male" <?php echo (!isset($_POST['gender']) || $_POST['gender'] == 'male') ? 'checked' : ''; ?>> Male
				        </label>
				        <label for="radio-02" class="label_radio">
				            <input type="radio" name="gender" value="female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'female') ? 'checked' : ''; ?>> Female
				        </label>
	                </div>
                </div>
                <div class="clearfix"> </div>
             </div>
			  
			  <div class="form-actions">
			    <input type="submit" id="edit-submit" name="op" value="Submit" class="btn_1 submit">
			  </div>
		 </form>
	  </div>
	  <div class="col-sm-6">
	     <ul class="sharing">
			<li><a href="#" class="facebook" title="Facebook"><i class="fa fa-boxed fa-fw fa-facebook"></i> Share on Facebook</a></li>
		  	<li><a href="#" class="twitter" title="Twitter"><i class="fa fa-boxed fa-fw fa-twitter"></i> Tweet</a></li>
		  	<li><a href="#" class="google" title="Google"><i class="fa fa-boxed fa-fw fa-google-plus"></i> Share on Google+</a></li>
		  	<li><a href="#" class="linkedin" title="Linkedin"><i class="fa fa-boxed fa-fw fa-linkedin"></i> Share on LinkedIn</a></li>
		  	<li><a href="#" class="mail" title="Email"><i class="fa fa-boxed fa-fw fa-envelope-o"></i> E-mail</a></li>
		 </ul>
	  </div>
	  <div class="clearfix"> </div>
   </div>
  </div>
</div>

<?php include_once("footer.php");?>


