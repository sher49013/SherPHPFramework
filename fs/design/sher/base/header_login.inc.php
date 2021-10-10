<?php

header('Pragma: no-cache');
header('Cache-Control: max-age=1, s-maxage=1, no-store, no-cache, post-check=0, pre-check=0, must-revalidate, proxy-revalidate');

?>
<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html>
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
	<meta charset="utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content=""/>
	<meta name="author" content="Fintract"/>
	<meta name="theme-color" content="#F7C708"/>
	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo $GLOBALS['config']['cms']['design_path']; ?>images/fav.png?v=<?php echo $GLOBALS['config']['cms']['build_version']; ?>"/>
	<title><?php echo $GLOBALS['config']['cms']['title']; ?></title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	
	<meta http-equiv="cache-control" content="no-cache"/>
	<meta http-equiv="expires" content="0"/>
	<meta http-equiv="pragma" content="no-cache"/>
	
	<base href="<?php echo $GLOBALS['config']['cms']['site_url']; ?>">
    <link href="http://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <!------ Include the above in your HEAD tag ---------->
    <link href="<?php echo $GLOBALS['config']['cms']['design_path']; ?>css/login.css?v=<?php echo $GLOBALS['config']['cms']['build_version']; ?>"
          rel="stylesheet" type="text/css"/>

</head>
<body>

<div class="logincontainer">