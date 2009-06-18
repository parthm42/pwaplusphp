<?

#==============================================================================================
# Copyright 2009 Scott McCandless (smccandl@gmail.com)
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
# http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
#==============================================================================================

function get_gdata_token() {

	$site = $_SERVER['SERVER_NAME'];
	$self  = $_SERVER['PHP_SELF'];
	$loc  = urlencode("http://" . $site . $self . "?loc=return");
	echo "<html>\n<body>\n<head>\n<title>PWA + PHP Setup: Step 1 of 3</title>\n</head>\n";
	echo "<p>Setting up PicasaWeb tokens for site: <strong>$site</strong></p>";
	echo "<p>If this is correct, <a href='https://www.google.com/accounts/AuthSubRequest?scope=http%3A%2F%2Fpicasaweb.google.com%2Fdata%2F&session=1&secure=0&next=$loc'>";
	echo "Login to your Google Account</a></p>"; 
	echo "</body>\n</html>";

}

function get_options() {

	echo "<html>\n<body>\n<head>\n<title>PWA + PHP Setup: Step 3 of 3</title>\n</head>\n";
	echo "<form action=install.php>\n";
	echo "<table width=700>\n";
	echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>Picasaweb User</strong></td><td style='padding-top: 20px;'><input type='text' name='un'></td></tr>\n";
	echo "<tr><td colspan=2><i>Enter your Picasaweb username.  This is the username you use to login to view your albums.</i></td></tr>";
	echo "<tr><td colspan=2><i>Set the number of images (albums and thumbnails) to display per row.</i></td></tr>";
	echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>Images Per Page</strong></td><td><select name='ip'>";
        for ($i=0; $i<=10; $i++) {
		$val = $i * 5;
                echo "<option value='$val'>$val</option>";
        }
        echo "</select>\n";
        echo "</td></tr>\n";
        echo "<tr><td colspan=2><i>Set the number of thumbnails to display per page. Value of 0 means don't paginate.</i></td></tr>";
	echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>Image Size (imgmax)</strong></td><td style='padding-top: 20px;'><select name='is'>";
	echo "<option value='800'>800</option>";
	echo "<option value='720'>720</option>";
	echo "<option value='640'>640</option>";
	echo "<option value='576'>576</option>";
	echo "<option value='512'>512</option>";
	echo "<option value='400'>400</option>";
	echo "<option value='320'>320</option>";	
	echo "<option value='288'>288</option>";
	echo "<option value='200'>200</option>";
	echo "</select>\n";
	echo "</td></tr>\n";
	echo "<tr><td colspan=2><i>Set the display size for full images.  These values are supported by the Picasaweb API.</i></td></tr>";
	echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>Thumbnail Size</strong></td><td style='padding-top: 20px;'><select name='ts'>";
	echo "<option value='160'>160</option>";
        echo "<option value='144'>144</option>";
        echo "<option value='72'>72</option>";
        echo "<option value='64'>64</option>";
        echo "<option value='48'>48</option>";
        echo "<option value='32'>32</option>";
	echo "</select>\n";
	echo "</td></tr>\n";
	echo "<tr><td colspan=2><i>Set the thumbnail size. These values are supported by the Picasaweb API.</i></td></tr>";
        echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>Use Lightbox</strong></td><td style='padding-top: 20px;'><select name='ul'>";
        echo "<option value='TRUE'>TRUE</option>";
        echo "<option value='FALSE'>FALSE</option>";
        echo "</select>\n";
        echo "</td></tr>\n";
	echo "<tr><td colspan=2><i>Choose whether or not to use <a href='http://www.huddletogether.com/projects/lightbox2/'>Lightbox v2</a>.  It must be installed for this to work. When set to FALSE, full size images are displayed in a pop-up window.</i></td></tr>";
	echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>Standalone Mode</strong></td><td style='padding-top: 20px;'><select name='sm'>";
        echo "<option value='TRUE' selected>TRUE</option>";
        echo "<option value='FALSE'>FALSE</option>";
        echo "</select>\n";
        echo "</td></tr>\n";
        echo "<tr><td colspan=2><i>This option allows you to specify whether this code will run within a CMS (FALSE) or whether the pages will exist outside a CMS (TRUE).  Selecting FALSE suppresses output of &lt;html&gt;, &lt;head&gt; and &lt;body&gt; tags in the source.</i></td></tr>";
	echo "<tr><td style='padding-top: 20px; width: 200px;'><strong>Require Filter</strong></td><td style='padding-top: 20px;'><select name='rf'>";
        echo "<option value='TRUE'>TRUE</option>";
        echo "<option value='FALSE' selected>FALSE</option>";
        echo "</select>\n";
        echo "</td></tr>\n";
        echo "<tr><td colspan=2><i>Set this to FALSE unless you want to *require* a search filter in the URL -- you can still filter albums with this set to FALSE.  Setting to TRUE *requires* a filter string in the URL to prevent certain users from seeing certain albums.</i></td></tr>";
	echo "</table>\n";
	echo "<input type='hidden' name='loc' value='finish'>";
	echo "<tr><td colspan=2 style='padding-top: 20px;'><input style='padding: 10px; margin-top: 20px;' type='submit' value='Finish'></td></tr>";
	echo "</form>\n";
}

function exchangeToken($single_use_token) {

        $ch = curl_init("https://www.google.com/accounts/AuthSubSessionToken");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: AuthSub token="' . $single_use_token . '"'
                ));

        $result = curl_exec($ch);  /* Execute the HTTP command. */

        curl_close($ch);

        $splitStr = split("=", $result);

        return trim($splitStr[1]);

}

function set_gdata_token() {

	global $cfg;
	$site = $_SERVER['SERVER_NAME'];
        $self  = $_SERVER['PHP_SELF'];
        $loc  = "http://" . $site . $self . "?loc=options";
	$token = $_GET['token'];
	$newToken = exchangeToken($token);

	set_config("<?PHP\n");
	set_config('$GDATA_TOKEN="' . $newToken . "\";\n");

	echo "<html>\n<body>\n<head>\n<title>PWA + PHP Setup: Step 2 of 3</title>\n</head>\n";
	echo "Done.  Token retrieved and saved in config file '$cfg'.<br />";
	echo "Continue to <a href='$loc'>Step 3</a>...\n";
	echo "</body>\n</html>";

}

function set_options() {

	$un = '$PICASAWEB_USER="'  . $_GET['un'] . "\";\n";
	$is = '$IMGMAX="'          . $_GET['is'] . "\";\t\t# Valid values are 800, 720, 640, 576, 512, 400, 320, 288, 200\n";
	$ts = '$THUMBSIZE="'       . $_GET['ts'] . "\";\t# Valid values are 160, 144, 72, 64, 48, 32\n";
	$ul = '$USE_LIGHTBOX="'    . $_GET['ul'] . "\";\n";
	$rf = '$REQUIRE_FILTER="'  . $_GET['rf'] . "\";\n";
	$sm = '$STANDALONE_MODE="' . $_GET['sm'] . "\";\n";
	$ip = '$IMAGES_PER_PAGE="' . $_GET['ip'] . "\";\n";

	set_config($un);
	set_config($is);
	set_config($ts);	
	set_config($ul);
	set_config($rf);
	set_config($sm);
	set_config($ip);
	set_config("?>\n");

	echo "<strong>Done - saved options. Installation complete. <a href='index.php'>Go to gallery!</a></strong><br /><i>You should rename install.php to prevent others from changing settings.</i>";

}

function set_config($text) {

	global $cfg;
	$fh = fopen($cfg, 'a') or die("Can't open file '$cfg' for writing.  Try 'chmod 777 pwaphp.cfg'");
	fwrite($fh, $text);
	fclose($fh);

}

$cfg = "config.php";

$loc = $_GET['loc'];
if ($loc == "return") {
	set_gdata_token();
} else if ($loc == "options") {
	get_options();	
} else if ($loc == "finish") {
        set_options();
} else {
	if (file_exists($cfg)) {
		$file = file_get_contents($cfg);
		if(strpos($file, "GDATA_TOKEN") >= 0) {
			echo "PWP+PHP is already configured.  Delete $cfg and reload this page to reconfigure.";
		} else {
			get_gdata_token();
		}
	} else {
		get_gdata_token();
	}

}

?>
