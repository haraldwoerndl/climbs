<?php
    // No security YET!
    if(!isset($_GET["id"]))
        die("No id set");

    $id = intval($_GET["id"]);
    $db = new SQLite3('db/climbs.sqlite3');

    // UPDATE
    $query = "DELETE FROM climb WHERE id=" . $id . ";";
    echo $query;
    echo "\n";
    $db->exec($query);
?>
