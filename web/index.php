<?php // Copyright (c) Settings (https://github.com/Mihapro/Settings)
//error_reporting(0);
session_start();

// Need this for a check in footer.
$main_page = true;

include 'include/config.php';
include 'include/functions.php';
include 'include/mysql.php';

include 'header.php';

sql_connect();

$game = (isset($_GET['g']) ? $_GET['g'] : null);
$build = (isset($_GET['v']) ? $_GET['v'] : null);

if(is_null($game) || !isset($games[$game])) {
	echo '<div class="navigation"><b>Main Page</b></div>';
	echo '<div style="padding:3px;">
			<form name="name" method="get">
				Game:&nbsp;<select name="g">';
	foreach($games as $key => $value) { 
		echo '<option value="'.$key.'">'.$value[0].'</option>';
	} 
	echo '</select><input type="submit" value="Submit" /></form></div>';
	echo '<table cellpadding=2>';
	foreach($games as $key => $value) {
		$query = "SELECT build, date, accessed FROM versions WHERE game='$key' ORDER BY id DESC LIMIT 1";
		$result = mysql_query($query);		
		while ($row = mysql_fetch_assoc($result)) {
			echo '<tr>
					<td><img src="'.$images[$key][1].'" height=20 alt="'.$value[0].'" title="'.$value[0].'" /></td>
					<td><a href="index.php?g='.$key.'&v='.$row['build'].'">'.$row['build'].' </a></td>
					<td><div class="info">Accessed '.$row['accessed'].' '.($row['accessed'] == 1 ? 'time' : 'times').' since '.getTimeAgoString(strtotime($row['date'])).'.</div></td>
				</tr>';
		}
	}
	echo '</table>';
} elseif(is_null($build)) {
	echo '<div class="navigation"><a href="?">Main Page</a> > <b>'.colored($game,$games,$colors,$colors_enabled).'</b></div>';
	// Game Logo
	if ($images_enabled) echo '<div><img src="'.$images[$game][0].'" height="110" /></div>';
	// Input form
	echo '<form name="build" method="get">
			<input type="hidden" name="g" value="'.$game.'">
			<center><table>
			<tr><td align=right>Game:</td><td><b>'.colored($game,$games,$colors,$colors_enabled).'</b> (<a href="index.php">change</a>)</td></tr>
			<tr><td align=right>Build:</td><td><input type="text" name="v" size="11" /></td></tr>
			</table></center>
			<input type="submit" value="Submit" />
		</form>';
	// Listing known build numbers
	$result = mysql_query("SELECT build, date, accessed FROM versions WHERE game='$game' ORDER BY id DESC LIMIT 5");
	echo '<center><table cellpadding=2>';
	while ($row = mysql_fetch_assoc($result)) {
		echo '<tr>
				<td><a href="index.php?g='.$game.'&v='.$row['build'].'">'.$row['build'].' </a></td>
				<td><div class="info">Accessed '.$row['accessed'].' '.($row['accessed'] == 1 ? 'time' : 'times').' since '.getTimeAgoString(strtotime($row['date'])).'.</div></td>
			</tr>';
	}
	echo '</table></center>';
} else { // both $game and $build are set
	$check = $urls[$game].$build.'/'.$files[$game][0];
	if($game == 3) {
		$f = file_get_contents($check, NULL, NULL, 0, 2);
		if($f != 'x�') {
			header('Location: index.php');
			return;
		}
	} else {
		$xml = simplexml_load_file($check);
		if (!$xml || $xml->getName() == 'Error') {
			header('Location: index.php');
			return;
		}
	}
	echo '<div class="navigation"><a href="?">Main Page</a> > <a href="?&game='.$game.'">'.colored($game,$games,$colors,$colors_enabled).'</a> > <b>Build '.$build.'</b></div>';
	$result = mysql_query("SELECT * FROM versions WHERE game='$game' AND build='$build'");
	if (mysql_num_rows($result) == 0)
		mysql_query("INSERT INTO versions (game, build, date, accessed) VALUES ('$game','$build', NOW(), '1')");
	else
		mysql_query("UPDATE versions SET accessed = accessed + 1 WHERE game='$game' AND build='$build'");
	
	$result = mysql_query("SELECT * FROM versions WHERE game='$game' AND build='$build'");
	$buildinfo = mysql_fetch_assoc($result);
	
	// Game Logo
	if ($images_enabled) echo '<div><img src="'.$images[$game][0].'" height="110" /></div>';
	echo '<center><table>
			<tr><td align=right>Game:</td><td><b>'.colored($game,$games,$colors,$colors_enabled).'</b> (<a class="gray" href="index.php">change</a>)</td></tr>
			<tr><td align=right>Build:</td><td><b>'.$build.'</b> (<a class="gray" href="index.php?g='.$game.'">change</a>)</td></tr>
		</table></center><center><table>
			<tr><td><div class="info"><b>First time accessed:</b> '.$buildinfo['date'].' ('.getTimeAgoString(strtotime($buildinfo['date'])).')</div></td></tr>
		</table></center>';
		
	if (isset($game_messages[$game])) echo '<center><div class="notice">'.$game_messages[$game].'</div></center>';
	echo '<center><table cellpadding=2><tr><td><center>';
	foreach ($files[$game] as $i => $file) {
		echo '<a href="'.assemble_link($game,$build,$file,$urls).'">'.$file.'</a>';
		if ($i < count($files[$game])-1) echo ', ';
	}
	echo '</center></td></tr>';
	echo '</table></center>';
	echo '<center><table>
			<tr><th>MPRO Image Downloader (<a href="http://www.box.com/shared/cyqtv5klon">download</a>)</th></tr>
			<tr><td>'.(isset($hash_unavailable[$game]) ? '<font color=red>Not available: </font>'.($hash_unavailable[$game] ? $hash_unavailable[$game] : '<no message given>') : '<a href="hash.php?g='.$game.'&v='.$build.'">'.$games[$game][1].'.txt</a> (save target as, rename to <i>'.$games[$game][1].'.txt</i>)').'</td></tr>
		</table></center>';
}
include 'footer.php';
?>