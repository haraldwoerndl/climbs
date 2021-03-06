<?php session_start(); ?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>A Climb in Vienna</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script src="main.js"></script>
  </head>
<body class="nirvana">
<?php
	// creates/sets global: $benutzer $isAdmin $benutzerName
	require 'auth.php';
	authenticationHTML(false);	// no login fields
?>
<button id="editButton" onclick="startEditing(this)">edit</button>
<script>
	if(!isAdmin) $('editButton').remove();
</script>


<div id="infolayout">
<div id="chead"><h1 id="i_name"></h1></div>

<div id="climbinfo">
    <table>
         <tr><th>Strava-ID:</th><td id="i_id"></td></tr>
         <tr><th>Strava-Name:</th><td id="i_sname"></td></tr>
         <tr><th>Region:</th><td id="i_region"></td></tr>
         <tr><th>Steigung:</th><td id="i_grade"></td></tr>
         <tr><th>Anstieg:</th><td id="i_hm"></td></tr>
         <tr><th>Strecke:</th><td id="i_dist"></td></tr>
         <tr><th>Härte:</th><td><form id="f_haerte">&nbsp;</form><span id="i_haerte">&nbsp;</span></td></tr>
         <tr><th>Schönheit:</th><td><form id="f_schoenheit">&nbsp;</form></td></tr>
         <tr><th>Straßenbelag:</th><td><form id="f_tarmac">&nbsp;</form></td></tr>
         <tr><th>Legalität:</th><td><div id="i_rules"></div><span id="tipphilfe">Copy: ✓🚫⛔🚶🚳</span></td></tr>
    </table>
</div>
<div id="climbdesc">
    <table>
         <tr><th>Beschreibung:</th></tr>
         <tr><td id="i_descr" colspan="2"></td></tr>
    </table>
</div>

<div id="veloviewer">
 <iframe id="veloframe" frameborder="0" scrolling="no"></iframe>
</div>
<div id="strava">
 <iframe id="stravaframe" frameborder="0" allowtransparency='true' scrolling="no"></iframe>
</div>

</div><!--infolayout-->

<script>
id = new URL(location.href).searchParams.get("id");
makeStarsForm($('f_haerte')).addEventListener('click', updateClimbStars);
makeStarsForm($('f_schoenheit')).addEventListener('click', updateClimbStars);
makeStarsForm($('f_tarmac')).addEventListener('click', updateClimbStars);

loadClimb(id);
$('veloframe').src = "https://veloviewer.com/segments/" + id + "/embed2?default2d=y";
$('stravaframe').src = "https://www.strava.com/segments/" + id + "/embed";
if(!allowEdit)
    $('editButton').remove();
</script>
</body>
</html>
