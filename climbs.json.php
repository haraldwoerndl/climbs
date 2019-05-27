<?php
    require_once('strava.php');

    if(isset($_GET["id"]))
    {
        $id = intval($_GET["id"]);
        echo json_encode(getSegment($id));
    }
    else
    {
        echo json_encode(getAllSegments());
    }
?>
