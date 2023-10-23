<?php

	    if (file_exists ( 'color-extractor')) {
	       header('Location: /color-extractor/index.php');
	    }

	include_once('v2/Classes/AdminCheck.php');

//	if (\v2\Classes\AdminCheck::checkForAdmin()) {
//		error_reporting(E_ALL);
//		ini_set('display_errors', TRUE);
//		ini_set('display_startup_errors', TRUE);
//    } else {
//		echo "Under Construction";
//		die();
//    }
	require_once("./v2/Manager.php");

    $index = new \v2\Manager();
    die();
