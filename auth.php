<?php
/*
	global: $benutzer
	global: $isAdmin

	$_SESSION:
	- $_SESSION["user"] (dann ist sie gültig)	-> $benutzer	lowercase!
	- $_SESSION["name"]							-> $benutzerName
	- $_SESSION["isAdmin"]						-> $isAdmin
	
	CREATE TABLE user (
   		user TEXT NOT NULL PRIMARY KEY,
   		passhash  TEXT NOT NULL,
   		name TEXT NOT NULL,
  		isAdmin INTEGER DEFAULT 0
   	);
   	
   	// admin:admin
   	INSERT INTO user (user, passhash, name, isAdmin)
   	VALUES("admin", "$2y$10$.SZaP77H0X29xZzz5fMC1e4ybGZOQZ3PgOfqjKhJVbrH2.CqLYU/2",
   	"Administrator", 1);
   	
   	INSERT INTO user (user, passhash, name, isAdmin)
    VALUES("harald", "$2y$10$p31CZdQwFdwLaO.08e14DO2WlDoh1QFf4AZJTjQl0WAp8wN87wJUK", "Harald", 1);
*/
global $benutzer;
global $benutzerName;
global $isAdmin;

function getCredentials($user, $pwd)
{
	$hash = password_hash($pwd, PASSWORD_DEFAULT);
	$escuser = SQLite3::escapeString($user);

	$db = new SQLite3('db/climbs.sqlite3');
			
	$query = 'SELECT passhash, user, name, isAdmin FROM user'.
			 ' WHERE user = "'.$escuser.'"';
	
	$result = $db->querySingle($query, TRUE);	// TRUE: entire first row
	
	if(count($result) > 0 && password_verify($pwd, $result['passhash']))
	{
		// Build Credentials from db:
		
		$cred['user'] = $result['user'];
		$cred['name'] = $result['name'];
		$cred['isAdmin'] = ($result['isAdmin'] == 1);
		$db->close();
		return $cred;
	}
	return null;
}

function authenticationCheck($needUser=false, $needAdmin=false)
{
	global $benutzer;
	global $benutzerName;
	global $isAdmin;

	if(!isSet($_SESSION["user"]))
	{
		if($needUser) die("No valid Session");
		return;
	}
	$benutzer = $_SESSION['user'];
	$benutzerName = $_SESSION['name'];
	$isAdmin = $_SESSION['isAdmin'];
	if($needAdmin && !$isAdmin)
		die("User " . $benutzer . " is not an admin");
}

function authenticationHTML($showLogin)
{
	global $benutzer;
	global $benutzerName;
	global $isAdmin;

	if(isSet($_SESSION["user"]))
	{
		$benutzer = $_SESSION['user'];
		$benutzerName = $_SESSION['name'];
		$isAdmin = $_SESSION['isAdmin'];
	}
	else if($showLogin)
	{
		// LOGIN:
		if(isSet($_POST["username"]) && isSet($_POST["password"]))
		{
			// check creds.
			$u = strtolower($_POST["username"]);
			$p = $_POST["password"];
		
			$cred = getCredentials($u, $p);
			if($cred != null)
			{
				$_SESSION = $cred;
				$benutzer = $cred['user'];
				$benutzerName = $cred['name'];
				$isAdmin = $cred['isAdmin'];
			}
			else
			{
				echo '<div class="error">Benutzer + Kennwort ungültig</div><br/>';
			}
		}
	
		if(!isSet($benutzer))
		{
			// LOGIN-FORM:
			$isAdmin = false;
			?>
			<div id="lockicon" onclick="clickLock()">🔒</div>
			<div id="loginbox">
			<form action="" method="post">
		    <fieldset><legend>Benutzer:</legend>
			<table>
			<tr><th>Benutzername:</th><td>
					<input type="text" name="username" size="15" autofocus="autofocus"></td></tr>
			<tr><th>Kennwort:</th><td><input type="password" name="password" size="15"></td></tr>
			<tr><th></th><td><input type="submit" value="Einloggen"></td></tr>
			</table>
			</fieldset>
			</form>
			</div>
			<?php
		}
	}


	if($showLogin && isSet($benutzer))
	{
		?>
		<div id="lockicon" onclick="clickLock()">🔒</div>
		<div id="loginbox">
		<?php
		$aText = ($isAdmin) ? '<b>Admin</b> ' : '';
	
		echo '<div id="userinfo">' . $aText . $benutzerName . ' (' . $benutzer . ')' ;
		echo ' <a href="?logout=yes">Logout</a></div></div>';
			
	}
	?>
	<script>
		// JavaScript ENVIRONMENTAL VARIABLES:
		isAdmin = <?php echo $isAdmin ? 'true':'false'; ?>;
		benutzer =  "<?php echo $benutzer; ?>";
		benutzerName = "<?php echo $benutzerName; ?>";
	</script>
	<?php
	// Funktioniert, falls das Session-Cookie akzeptiert wurde
}
?>
