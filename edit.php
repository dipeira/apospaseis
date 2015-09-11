<?php

  header('Content-type: text/html; charset=iso8859-7'); 
  require_once "config.php";
  require_once 'functions.php';
  session_start();

  include("class.login.php");
  $log = new logmein();
  if($log->logincheck($_SESSION['loggedin']) == false)
  {   
      header("Location: login.php");
  }
  else
      $loggedin = 1;
?>
<html>
  <head>
	<title>�������� ����������</title>
	<h2>�������� ����������</h2>
	<h4>���.: ������� �� �������...</h4>
  </head>
  <body>

<?php
	if (!$loggedin)
		die("Error...");

    // check if admin 
    if ($_SESSION['user']!=$av_admin)
        die("Authentication error");
		
	// configuration
	$file = 'config.php';

	// check if form has been submitted
	if (isset($_POST['text']))
	{
		// save the text contents
		file_put_contents($file, $_POST['text']);

		// redirect to admin page again
		header("Location: admin.php");
		exit();
	}

	// read the textfile
	$text = file_get_contents($file);

	?>
	<!-- HTML form -->
	<form action="" method="post">
	<textarea name="text" style="width: 700px; height: 740px;"><?php echo $text; ?></textarea>
	<br>
	<input type="submit" value="��������" />
	<br>
	<a href="admin.php">���������</a>
	</form>

  </body>
</html>