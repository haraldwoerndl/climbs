/*
 Globals:
 climbs: array with all climbs.
 map: the map object
 */
function $(id) { return document.getElementById(id); }

//rating = { 1: "⭐", 2: "⭐⭐", 3: "⭐⭐⭐", 4: "⭐⭐⭐⭐", 5: "⭐⭐⭐⭐⭐" };
rating = { 1: "★☆☆☆☆", 2: "★★☆☆☆", 3: "★★★☆☆", 4: "★★★★☆", 5: "★★★★★" };
/*rating = [ 1: "&#x2b50;", 2: "&#x2b50;&#x2b50;", 3: "&#x2b50;&#x2b50;&#x2b50;",
    4: "&#x2b50;&#x2b50;&#x2b50;&#x2b50;", 5: "&#x2b50;&#x2b50;&#x2b50;&#x2b50;&#x2b50;" ];
*/
/*
    ⛔🚫🚳🚶  🚴  ☹☺✓
*/

// some globals:
var map;
var climbs = false;

function initLeafletMap() {
    map = L.map('mapid');

    map.fitBounds([
                   [48.239495, 16.297339],
                   [48.339495, 16.197339]
                   ]);
    L.tileLayer(
                'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                }).addTo(map);
}


function centerMap()
{
    latMin = false;
    latMax = false;
    lonMin = false;
    lonMax = false;
    for(let climb of climbs) {
        if(!climb.visible)
            continue;
        if(!latMin || climb.end_lat < latMin)   latMin = climb.end_lat;
        if(climb.start_lat < latMin)            latMin = climb.start_lat;
        if(!latMax || climb.end_lat > latMax)   latMax = climb.end_lat;
        if(climb.start_lat > latMax)            latMax = climb.start_lat;
        
        if(!lonMin || climb.end_lon < lonMin)   lonMin = climb.end_lon;
        if(climb.start_lon < lonMin)            lonMin = climb.start_lon;
        if(!lonMax || climb.end_lon > lonMax)   lonMax = climb.end_lon;
        if(climb.start_lon > lonMax)            lonMax = climb.start_lon
    }
    if(latMin)
        map.fitBounds([[latMin, lonMin], [latMax, lonMax]], { padding: [50, 50] });
}
/*********** SORT List: ***********/

function rulesToInt(rule) {
    if(rule == null || rule == "")
        return 0;
    if(rule == "🚫") return 1;
    if(rule == "🚳") return 2;
    if(rule == "⛔") return 3;
    return 4;
}

// Last Sort Direction:
direction = -1;
function sortTable(e)
{
    col = e.target.sortKey;
    
    table = $("liste");
    rows = table.rows;
    tbody = table.childNodes[0];
    titleRow = rows[0];
    
    direction *= -1;
    
    r = [];        // convert to Array-Object
    for (i = 0; i < rows.length; i++)
        r[i] = rows[i];

    r.sort(function(a, b) {
        if(a == titleRow) return -1;    // Keep Title on Top
        if(b == titleRow) return +1;

        if(col == 1 || col == 2) {  // Text order
            x = a.childNodes[col].innerHTML.toLowerCase();
            y = b.childNodes[col].innerHTML.toLowerCase();
        } else if(col == 6) {   // Stars Härte
            x = a.climb.haerte + a.climb.haertepunkte / 1000;   // for equal stars, the haertepunkte decide
            y = b.climb.haerte + b.climb.haertepunkte / 1000;
        } else if(col == 7) {   // Stars Schönheit
            x = a.climb.schoenheit;
            y = b.climb.schoenheit;
        } else if(col == 8) {   // Stars Asphalt
            x = a.climb.tarmac;
            y = b.climb.tarmac;
        } else if(col == 9) {   // Legal
            x = rulesToInt(a.climb.rules);
            y = rulesToInt(b.climb.rules);
        } else { // Numbers
            x = Number(a.childNodes[col].innerHTML);
            y = Number(b.childNodes[col].innerHTML);
        }
        if (x > y) return 1 * direction;
        if (x < y) return -1 * direction;
        return 0;
    });
    for (i = 0; i < rows.length; i++) {
        tbody.appendChild(r[i]);
    }
}

/*********** FILTER List: ***********/

function doFilter()
{
    f = $("f_text").value.trim().toLowerCase();
    fr = $("f_region").value.trim().toLowerCase();

    min_hm = Number($("f_minhm").value);
    max_hm = Number($("f_maxhm").value);
    min_g = Number($("f_mingrad").value);
    max_g = Number($("f_maxgrad").value);
    min_h = Number(document.forms.f_hmin.elements.stars.value); // Haerte
    max_h = Number(document.forms.f_hmax.elements.stars.value);
    min_s = Number(document.forms.f_smin.elements.stars.value); // Beaty
    max_s = Number(document.forms.f_smax.elements.stars.value);
    min_a = Number(document.forms.f_amin.elements.stars.value); // Asphalt
    max_a = Number(document.forms.f_amax.elements.stars.value);
    legal = Number(document.forms.f_zufahrt.elements.legal.value);  // 1=legal, 2=illegal, 3=beide

    var table, rows, name, beschr, wot, display;
    table = $("liste");
    rows = table.rows;
    changed = false;
    for (i = 1; i < rows.length; i++) {
        climb = rows[i].climb;

        try {
            if(f != "") {
                kombi = climb.name + "|" + climb.sname;
                if(kombi.toLowerCase().search(f) == -1)
                    throw 0;
            }
            if((fr != "") && (!climb.region || climb.region.toLowerCase().search(fr) == -1))
                throw 0;
            
            hm = climb.elev_high - climb.elev_low;
            if((min_hm > 0 && hm < min_hm) || (max_hm > 0 && hm > max_hm))
                throw 0;
            if((min_g > 0 && climb.grade_avg < min_g) || (max_g > 0 && climb.grade_avg > max_g))
                throw 0;

            if((climb.haerte) && (climb.haerte < min_h || climb.haerte > max_h))
                    throw 0;
            if((climb.schoenheit) && (climb.schoenheit < min_s || climb.schoenheit > max_s))
                    throw 0;
            if((climb.tarmac) && (climb.tarmac < min_a || climb.tarmac > max_a))
                    throw 0;
            if((legal == 2) && (!climb.rules || climb.rules == ""))   // nur illegal
                    throw 0;
            if((legal == 1) && (climb.rules && climb.rules != ""))    // nur legal
                    throw 0;
        } catch(err) {
            rows[i].style.display = "none";
            if(climb.visible) { // changes to invisible
                climb.visible = false;
                climb.pl.remove();
                climb.m.remove();
                changed = true;
            }
            continue;
        }

        rows[i].style.display = "table-row";
        if(!climb.visible) {
            climb.visible = true;
            climb.pl.addTo(map);
            climb.m.addTo(map);
            changed = true;
        }
    }
    if(changed)
        centerMap();
}


/*********** CREATE List: ***********/
var selectedPL = false;
function clickOnClimb(climb, openPopup=false) // in map or in list
{
    // console.log("Click: " + climb.name);
    
    if(selectedPL) {
        selectedPL.setStyle({color: 'blue'});
        selectedPL.climb.tablerow.classList.remove('selected');
        selectedPL.climb.m.closePopup();
    }
    selectedPL = climb.pl;
    selectedPL.setStyle({color: 'red'});
    if(openPopup)
        climb.m.openPopup();
    climb.tablerow.classList.add('selected');
    climb.tablerow.scrollIntoViewIfNeeded({behavior: "smooth"});
}

function loadClimbs() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {

            t = $("liste");
            row = t.insertRow(-1);
            row.innerHTML =
               '<th>Nr.</th><th>Name</th><th>Region</th><th>&#216;&nbsp;Steigung\
                </th><th>Höhenmeter</th><th>Strecke\
                </th><th>Härte</th><th>Schönheit</th><th>Belag</th><th>Legal</th>';
            for(i=0; i<10; i++) {
                row.childNodes[i].addEventListener('click', sortTable);
                row.childNodes[i].sortKey=i;
            }
            
            var finishIcon = L.icon({
                                    iconUrl: 'icons8-finish-flag-48.png',
                                    iconSize:     [24, 24],
                                    iconAnchor:   [8, 23],
                                    popupAnchor:  [-3, -24]
                                    });

            climbs = JSON.parse(this.responseText);
            for(let climb of climbs)
            {
                // Polyline:
                var coordinates = L.Polyline.fromEncoded(climb.polyline).getLatLngs();
                pl = L.polyline(coordinates, { color: 'blue',
                                               weight: 5,
                                               opacity: .7,
                                               lineJoin: 'round',
                                               bubblingMouseEvents: false }).addTo(map);
                pl.climb = climb;
                pl.on('click', function(me) { clickOnClimb(climb, true); });

                // Marker:
                m = L.marker([climb.end_lat, climb.end_lon], {icon: finishIcon}).addTo(map);
                m.bindPopup("<a href=\"nirvana.html?id=" + climb.id + "\" target=\"climb\">" + climb.name + "</a> "+ climb.id);
                m.climb = climb;
                
                // Table-Row:
                row = t.insertRow(-1);
                row.insertCell(-1).innerHTML = climb.id;
                row.insertCell(-1).innerHTML = "<a href=\"nirvana.html?id=" + climb.id + "\" target=\"climb\">" + climb.name + "</a>";
                row.insertCell(-1).innerHTML = climb.region ? climb.region : "-";
                row.insertCell(-1).innerHTML = climb.grade_avg;
                row.insertCell(-1).innerHTML = Math.round(climb.elev_high - climb.elev_low); // climb.elev_gain; ist oft leer
                row.insertCell(-1).innerHTML = Math.round(climb.distance);
                
                hp = Math.round(climb.distance * climb.grade_avg * climb.grade_avg / 1000);
                climb.haertepunkte = hp;
                row.insertCell(-1).innerHTML = (climb.haerte ? rating[climb.haerte] : "") + " (" + hp + ")";
                row.insertCell(-1).innerHTML = climb.schoenheit ? rating[climb.schoenheit] : "";
                row.insertCell(-1).innerHTML = climb.tarmac ? rating[climb.tarmac] : "";
                row.insertCell(-1).innerHTML = (climb.rules && climb.rules != "") ? climb.rules : "✓";
                row.climb = climb;
                
                row.addEventListener('click', function(e) { clickOnClimb(climb); });
                
                // create cross references:
                climb.pl = pl;
                climb.m = m;
                climb.tablerow = row;
                
                climb.visible = true;
            }
            
            centerMap();
        }
    };
    xhttp.open("GET", "climbs.json.php", true);
    xhttp.send();
}

/*** Fill out a given form-Object (with ID): ***/
function makeStarsForm(form)
{
    var id = form.id;
    pattern = '<input type="radio" id="s5_NN" name="stars" value="5"/> \
               <label for="s5_NN" ></label> \
               <input type="radio" id="s4_NN" name="stars" value="4"/> \
               <label for="s4_NN" ></label> \
               <input type="radio" id="s3_NN" name="stars" value="3"/> \
               <label for="s3_NN"></label> \
               <input type="radio" id="s2_NN" name="stars" value="2"/> \
               <label for="s2_NN" ></label> \
               <input type="radio" id="s1_NN" name="stars" value="1"/> \
               <label for="s1_NN" ></label>';
    form.innerHTML = pattern.replace(/NN/g, id);
    form.classList.add('rating');
    return form;
}

function makeMyForms() {
    makeStarsForm($('f_hmin')).addEventListener('click', doFilter); // Härte
    makeStarsForm($('f_hmax')).addEventListener('click', doFilter);
    makeStarsForm($('f_smin')).addEventListener('click', doFilter); // Schönheit
    makeStarsForm($('f_smax')).addEventListener('click', doFilter);
    makeStarsForm($('f_amin')).addEventListener('click', doFilter); // Belag
    makeStarsForm($('f_amax')).addEventListener('click', doFilter);

    document.forms.f_hmin.elements.stars.value = 1;
    document.forms.f_hmax.elements.stars.value = 5;
    document.forms.f_smin.elements.stars.value = 1;
    document.forms.f_smax.elements.stars.value = 5;
    document.forms.f_amin.elements.stars.value = 1;
    document.forms.f_amax.elements.stars.value = 5;
}


/*** used by nirvana.html: ***/


var oneClimb = null;    // the climb which was loaded

function loadClimb(id) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            console.log(this.responseText);
            climb = JSON.parse(this.responseText);

            // Table-Row:
            $('i_id').innerHTML = climb.id;
            $('i_name').innerHTML = climb.name;
            $('i_sname').innerHTML = climb.sname;
            $('i_region').innerHTML = climb.region;
            $('i_grade').innerHTML = climb.grade_avg + "%, max. " + climb.grade_max + "%";
            $('i_hm').innerHTML = Math.round(climb.elev_high - climb.elev_low) + " hm";
            $('i_dist').innerHTML = Math.round(climb.distance)  + " m";
            
            hp = Math.round(climb.distance * climb.grade_avg * climb.grade_avg / 1000);
            $('i_haerte').innerHTML = " (" + hp + ")";
            $('i_rules').innerHTML = (climb.rules && climb.rules != "") ? climb.rules : "✓";
            $('i_descr').innerHTML = climb.beschreibung;
            
            oneClimb = climb;
            updateClimbStars();
        }
    };
    xhttp.open("GET", "climbs.json.php?id=" + id, true);
    xhttp.send();
}

function makeEditable(element, editable=true)
{
    element.contentEditable = editable;
    if(editable)
        element.classList.add('editmode');
    else
        element.classList.remove('editmode');
}

function saveChanges(button, climb)
{
    button.innerHTML = "saving..."
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            button.innerHTML = "edit";
            button.disabled = false;

            console.log(this.responseText);
        }
    };
    xhttp.open("POST", "edit_climb.php", true);
    xhttp.setRequestHeader('Content-Type', 'application/json')
    xhttp.send(JSON.stringify(oneClimb));
}

function updateClimbStars() {
    if(!$('editButton').editmode) {
        document.forms.f_haerte.elements.stars.value = oneClimb.haerte;
        document.forms.f_schoenheit.elements.stars.value = oneClimb.schoenheit;
        document.forms.f_tarmac.elements.stars.value = oneClimb.tarmac;
    } else {
        oneClimb.haerte = document.forms.f_haerte.elements.stars.value;
        oneClimb.schoenheit = document.forms.f_schoenheit.elements.stars.value;
        oneClimb.tarmac = document.forms.f_tarmac.elements.stars.value;
    }
}

function startEditing(button) {
    if(button.editmode) // Finish Editing
    {
        makeEditable($('i_name'), false);
        makeEditable($('i_descr'), false);
        makeEditable($('i_rules'), false);
        makeEditable($('i_region'), false);
        $('f_haerte').parentNode.classList.remove('editmode');
        $('f_schoenheit').parentNode.classList.remove('editmode');
        $('f_tarmac').parentNode.classList.remove('editmode');

        oneClimb.name = $('i_name').innerHTML;
        oneClimb.beschreibung = $('i_descr').innerHTML;
        oneClimb.rules = $('i_rules').innerHTML;
        oneClimb.region = $('i_region').innerHTML;

        button.disabled = true;
        button.editmode = false;
        saveChanges(button, oneClimb);
    }
    else
    {
        makeEditable($('i_name'));
        makeEditable($('i_descr'));
        makeEditable($('i_rules'));
        makeEditable($('i_region'));
        $('f_haerte').parentNode.classList.add('editmode');
        $('f_schoenheit').parentNode.classList.add('editmode');
        $('f_tarmac').parentNode.classList.add('editmode');
        button.innerHTML = "save"
        button.editmode = true;
    }
}
