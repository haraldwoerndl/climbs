<?php
session_start();
// creates/sets global: $benutzer $isAdmin $benutzerName
require 'auth.php';
authenticationCheck(true, false);   // user:yes, admin:no

// get Post-Data directly:
$c = json_decode(file_get_contents('php://input'));

// vid, veranstaltung.name, veranstaltung.uid, user.name AS owner,
// beschreibung, kategorie, maxTN, minTN, tage, kosten, gesperrt

// print_r($c);

$db = new SQLite3('db/climbs.sqlite3');

// UPDATE
$query = "UPDATE climb SET ".
         "name='".SQLite3::escapeString($c->name)."',".
         "region='".SQLite3::escapeString($c->region)."',".
         "beschreibung='".SQLite3::escapeString($c->beschreibung)."',".
         "rules='".SQLite3::escapeString($c->rules)."',".
         "haerte=" . intval($c->haerte) . ",schoenheit=" . intval($c->schoenheit) . ",tarmac=" . intval($c->tarmac).
         " WHERE id=".intval($c->id).";";
echo $query;
echo "\n";
$db->exec($query);

?>
