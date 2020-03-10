<?php
session_start();
if(isSet($_GET["logout"]))
{
	session_unset();
	session_destroy();
	header("Location: index.php");
	exit();
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Climbs in Vienna</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.5.1/dist/leaflet.css"
        integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ=="
        crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.5.1/dist/leaflet.js"
        integrity="sha512-GffPMF3RvMeYyc1LWMHtK8EbPv0iNZ8/oTtHPx9/cc2ILxQ+u905qIwdpULaqDkyBKgOaB57QTMg7ztg8Jm2Og=="
        crossorigin=""></script>
    <!-- Decoder: -->
    <script type="text/javascript" src="https://rawgit.com/jieter/Leaflet.encoded/master/Polyline.encoded.js"></script>
    
    <script src="main.js"></script>
  </head>
<body>
<!-- My private climb page (c) 2019 by Harald Wörndl-Aichriedler -->
<?php
	// creates/sets global: $benutzer $isAdmin $benutzerName
	require 'auth.php';
	authenticationHTML(true);
?>

<div id="layout">
<div id="head">
    <h1>Climbs in Vienna</h1>
</div>

<div id="filter">
        <div><span class="sTit">Name:</span><input type="text" id="f_text" onkeyup="doFilter()"></div>
        <div><span class="sTit">Region:</span><input type="text" id="f_region" onkeyup="doFilter()"></div>
        <div><span class="sTit">Höhenmeter:</span>
            <input type="number" id="f_minhm" value="" min="0" onkeyup="doFilter()" onchange="doFilter()">m –
            <input type="number" id="f_maxhm" value="" min="0" onkeyup="doFilter()" onchange="doFilter()">m
        </div>
        <div><span class="sTit">Steigung:</span>
            <input type="number" id="f_mingrad" value="" min="0" onkeyup="doFilter()" onchange="doFilter()">%&nbsp; –
            <input type="number" id="f_maxgrad" value="" min="0" onkeyup="doFilter()" onchange="doFilter()">%
        </div>
        <div><span class="sTit">Härte:</span><form id="f_hmin">&nbsp;</form> — <form id="f_hmax">&nbsp;</form></div>
        <div><span class="sTit">Schönheit:</span><form id="f_smin">&nbsp;</form> — <form id="f_smax">&nbsp;</form></div>
        <div><span class="sTit">Belag:</span><form id="f_amin">&nbsp;</form> — <form id="f_amax">&nbsp;</form></div>
        <div><span class="sTit">Zufahrt:</span> &nbsp; <form id="f_zufahrt" onclick="doFilter()">
                <input type="radio" id="l_1" name="legal" value="1"/>
                <label for="l_1" >Legal</label>
                <input type="radio" id="l_2" name="legal" value="2"/>
                <label for="l_2" >Illegal</label>
                <input type="radio" id="l_3" name="legal" value="3" checked="checked"/>
                <label for="l_3">Egal</label>
            </form></div>
        
</div>

<div id="mapid"></div>

<div id="listediv">
    <table id="liste">
        <thead id="listenhead">
            <tr><th>Nr.</th><th>Name</th><th>Region</th><th>&#216;&nbsp;Steigung
                </th><th>Höhenmeter</th><th>Strecke
                </th><th>Härte</th><th>Schönheit</th><th>Belag</th><th>Legal</th></tr>
        </thead>
        <tbody id="listenbody">
        </tbody>
    </table>
</div>
</div><!-- layout -->

<script>
    initLeafletMap();
    loadClimbs();
    makeMyForms();
</script>

<!--
<script>
function allowDrop(ev) {
    ev.preventDefault();
    ev.target.style.color = "red";
}

function drop(ev) {
    ev.preventDefault();
    
    for(let file of ev.dataTransfer.files)
    {
        if(file.size < 1000) {
            var reader = new FileReader();
            reader.onload = function(evt) {
                console.log(evt.target.result);
                nr = parseInt(evt.target.result.replace(/^.*segments\//, ''));
                if(nr > 0)
                {
                    console.log("Import Event: " + nr);
                    ev.target.innerHTML = "Import Event: " + nr;
                }
            };
            reader.readAsText(file);
        }
    }
}
</script>
<div id="dropper" style="width: 100vw; height: 5vh;" ondrop="drop(event)" ondragover="allowDrop(event)">
Drop a link here!</div>
-->
</body>
</html>
