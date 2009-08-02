<?PHP

include("config.php");

function check_for_updates() {

	$version_file = "update_exists.txt";
	$version = file_get_contents('http://www.smccandl.net/pwaplusphp_version.html');
	if ($version !== false) {
		$version=trim($version);
		if ($version > $THIS_VERSION) {
			echo "<a href='http://pwaplusphp.googlecode.com/files/pwa+php_v$version.tar'>Get v$version!</a>";
			$fh = fopen($version_file, 'w') or die("can't open file");
			fwrite($fh, $version);
			fclose($fh);
		}
	} else {
		# We had an error, fake a high version number so no message is printed.
		$version = "99";
	}

}

check_for_updates();
?>
