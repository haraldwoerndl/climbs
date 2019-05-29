<?php

// global Token-Cache:
$token = false;
    
$mysqlDB = null;

require_once('strava.secret.php');
/*  contains your private app information:
$stravaOptions = [
    "client_id" => "12345",
    "grant_type" => "refresh_token",
    "client_secret" => "1234567890abcdef1234567890abcdef12345678",
    "refresh_token" => "12345678a2c4c1d5320830fec2dcb9cf8727437f"
];
*/

/*
     CREATE TABLE climb (
     id       INTEGER PRIMARY KEY,   -- gleich segment-id
     name     TEXT NOT NULL,
     sname    TEXT NOT NULL,
     beschreibung    TEXT,
     distance   FLOAT,
     grade_avg  FLOAT,
     grade_max  FLOAT,
     elev_high  FLOAT,
     elev_low   FLOAT,
     elev_gain  FLOAT,
 
     start_lat   FLOAT,
     start_lon   FLOAT,
     end_lat     FLOAT,
     end_lon     FLOAT,
 
     polyline    TEXT,
 
     haerte     INTEGER,
     schoenheit INTEGER,
     tarmac     INTEGER,
     region     TEXT,
     rules      TEXT
     );
 */

function getToken()
{
    global $token, $stravaOptions;
    if($token)
        return $token;

    $keysJson = @file_get_contents ( "db/tokens.json" );
    if($keysJson !== false)
    {
        $keys = json_decode($keysJson);
        if(isset($keys->expires_at ) && $keys->expires_at > time() + 60)
        {
            // doesn't expire in the next minute...
            return $token = $keys->access_token;
        }
        if(isset($keys->refresh_token))
        {
            $stravaOptions["refresh_token"] = $keys->refresh_token;
        }
    }
    
    $curl = curl_init("https://www.strava.com/oauth/token");
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $stravaOptions);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $erg = curl_exec($curl);
    curl_close($curl);
    if($erg === false)
    {
        die("No token");
    }
    file_put_contents ("db/tokens.json" , $erg );
    // echo "GOT NEW<br/>";

    $r = json_decode($erg);
    $at = $r->access_token;
    $rt = $r->refresh_token;
    $exp = $r->expires_at;
    // echo "Token: " . $at . "<br/>";
    // echo "R-Token: " . $rt . "<br/>";
    // echo "Expires: " . date(DATE_RSS, $exp) . "<br/>";
    return $token = $at;
}

function myDB()
{
    global $mysqlDB;
    if($mysqlDB == null)
        $mysqlDB = new SQLite3('db/climbs.sqlite3');
    return $mysqlDB;
}

    
class Climb
{
    public function set($data)
    {
        foreach ($data AS $key => $value) $this->{$key} = $value;
    }
    public function setStrava($strava) {
        // foreach ($data AS $key => $value) $this->{$key} = $value;
        $this->id = $strava->id;
        $this->name = $strava->name;    // default
        $this->sname = $strava->name;
        $this->distance = $strava->distance;
        $this->grade_avg = $strava->average_grade;
        $this->grade_max = $strava->maximum_grade;
        $this->elev_high = $strava->elevation_high;
        $this->elev_low = $strava->elevation_low;
        $this->elev_gain = $strava->total_elevation_gain;
        
        $this->start_lat = $strava->start_latitude;
        $this->start_lon = $strava->start_longitude;
        $this->end_lat = $strava->end_latitude;
        $this->end_lon = $strava->end_longitude;

        $this->polyline = $strava->map->polyline;
    }
    
    public function saveInDB() {
        $db = myDB();

        $query = "INSERT INTO climb (id,name,sname,distance,grade_avg,grade_max,".
                 "elev_high,elev_low,elev_gain,start_lat,start_lon,end_lat,end_lon,polyline)" .
                 "VALUES(" . $this->id .
                    ",'" . SQLite3::escapeString($this->name) .
                    "','" . SQLite3::escapeString($this->name) ."'," .
                    $this->distance . "," . $this->grade_avg . "," . $this->grade_max . "," .
                    $this->elev_high . "," . $this->elev_low . "," . $this->elev_gain . "," .
                    $this->start_lat . "," . $this->start_lon . "," .
                    $this->end_lat . "," . $this->end_lon . ",'" .
                    SQLite3::escapeString($this->polyline) . "');";
        // echo $query;
        $db->exec($query);

    }
}
    
function getSegmentFromStrava($nr)
{
    $auth = [ "Authorization: Bearer " . getToken() ];
    $curl = curl_init("https://www.strava.com/api/v3/segments/" . $nr);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $auth);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $erg = curl_exec($curl);
    curl_close($curl);
    $c = new Climb;
    $c->setStrava(json_decode($erg));
    return $c;
}

    
function getSegmentFromDB($nr)
{
    $db = myDB();
    $s = $db->querySingle('SELECT * FROM climb WHERE id=' . $nr, true);
    if(count($s) == 0)
        return null;
    
    $c = new Climb;
    $c->set($s);
    return $c;
}
    
function getSegment($nr)
{
    $c = getSegmentFromDB($nr);
    if($c != false) {
        // echo "DB:";
        return $c;
    }
    $c = getSegmentFromStrava($nr);
    $c->saveInDB();
    // echo "STRAVA";
    return $c;
}

function getAllSegments()
{
    $climbs = [];
    $db = myDB();
    $r = $db->query('SELECT * FROM climb');
    if($r == false)
        return null;
    while($result = $r->fetchArray(SQLITE3_ASSOC)) {
        $c = new Climb;
        $c->set($result);
        $climbs[] = $c;
    }
    if(count($climbs) == 0)
        return null;
    return $climbs;
}

    
?>
