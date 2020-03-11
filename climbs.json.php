<?php
	session_start();
	require 'auth.php';
	authenticationCheck(false, false); // if user, fetch new segments (see below)
    require_once('strava.php');

    if(isset($_GET["id"]))
    {
        $id = intval($_GET["id"]);
        // if logged in: create on demand, otherwise just do.
        $s = isset($benutzer) ? getSegment($id) : getSegmentFromDB($id);
        echo json_encode($s);
    }
    else
    {
        echo json_encode(getAllSegments());
    }
?>
