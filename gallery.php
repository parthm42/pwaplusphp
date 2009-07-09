<?PHP

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

#----------------------------------------------------------------------------
# CONFIGURATION
#----------------------------------------------------------------------------
require_once("config.php");
$back_link = "index.php";	  # Name of the file that displays all albums

#----------------------------------------------------------------------------
# Grab album data from URL
#----------------------------------------------------------------------------
$ALBUM = $_REQUEST['album'];
$LOCATION = str_replace(" ","",$ALBUM);
list($ALBUM_TITLE,$tags) = split('_',$ALBUM);

#----------------------------------------------------------------------------
# Check for required variables from config file
#----------------------------------------------------------------------------
if ( (!isset($GDATA_TOKEN)) || (!isset($PICASAWEB_USER)) || (!isset($IMGMAX)) || (!isset($THUMBSIZE)) || (!isset($USE_LIGHTBOX)) || (!isset($REQUIRE_FILTER)) || (!isset($STANDALONE_MODE)) || (!isset($IMAGES_PER_PAGE)) ) {

	echo "<h1>Error: One or more required variables is missing from config.php!</h1><h3>Please re-run the install.php configuration script.</h3>";
	exit;
}

#----------------------------------------------------------------------------
# VARIABLES FOR PAGINATION
#----------------------------------------------------------------------------
if ($IMAGES_PER_PAGE == 0) {

	$file = "http://picasaweb.google.com/data/feed/api/user/" . $PICASAWEB_USER . "/album/" . $LOCATION . "?kind=photo&thumbsize=" . $THUMBSIZE . "&imgmax=" . $IMGMAX;

} else {

	$page = $_GET['page'];
	if (!(isset($page))) {
		$page = 1;
	}
	if ($page > 1) {
		$start_image_index = (($page - 1) * $IMAGES_PER_PAGE) + 1;
	} else {
		$start_image_index = 1;
	}

	$file = "http://picasaweb.google.com/data/feed/api/user/" . $PICASAWEB_USER . "/album/" . $LOCATION . "?kind=photo&thumbsize=" . $THUMBSIZE . "&imgmax=" . $IMGMAX . "&max-results=" . $IMAGES_PER_PAGE . "&start-index=" . $start_image_index;

}

#----------------------------------------------------------------------------
# Curl code to store XML data from PWA in a variable
#----------------------------------------------------------------------------
$ch = curl_init();
$timeout = 0; // set to zero for no timeout
curl_setopt($ch, CURLOPT_URL, $file);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: AuthSub token="' . $GDATA_TOKEN . '"'
  ));
$addressData = curl_exec($ch);
curl_close($ch);

#----------------------------------------------------------------------------
# Parse the XML data into an array
#----------------------------------------------------------------------------
$p = xml_parser_create();
xml_parse_into_struct($p, $addressData, $vals, $index);
xml_parser_free($p);

#----------------------------------------------------------------------------
# Output headers if required 
#----------------------------------------------------------------------------
if ($STANDALONE_MODE == "TRUE") {

	echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n";
        echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"en\" xml:lang=\"en\">\n";
	echo "<head>" . "\n";
	echo "<title>" . $ALBUM_TITLE . "</title>" . "\n";
	echo "<link rel=\"stylesheet\" href=\"style.css\" type=\"text/css\" />";

	#----------------------------------------------------------------------------
	# Scripts and styles for lightbox, if enabled.  Assumes default install in ./
	#----------------------------------------------------------------------------
	if ($USE_LIGHTBOX == "TRUE") {
		echo "<script type=\"text/javascript\" src=\"js/prototype.js\"></script>";
		echo "<script type=\"text/javascript\" src=\"js/scriptaculous.js?load=effects,builder\"></script>";
		echo "<script type=\"text/javascript\" src=\"js/lightbox.js\"></script>";
		echo "<link rel=\"stylesheet\" href=\"css/lightbox.css\" type=\"text/css\" media=\"screen\" />";
	}

	echo "</head>" . "\n";
	echo "<body>" . "\n";

}

#----------------------------------------------------------------------------
# Iterate over the array and extract the info we want
#----------------------------------------------------------------------------
unset($thumb);
unset($title);
unset($href);
unset($path);
unset($url);
$image_count=0;
foreach ($vals as $val) {

        switch ($val["tag"]) {

                        case "MEDIA:THUMBNAIL":
                                $thumb = trim($val["attributes"]["URL"] . "\n");
                                break;
                        case "MEDIA:TITLE":
                                $title = trim($val["value"]);
                                break;
                        case "MEDIA:DESCRIPTION":
                                if ($val["attributes"]["REL"] == "alternate") {
                                        $href = trim($val["attributes"]["HREF"]);
                                }
                                break;
                        case "MEDIA:CONTENT":
                                $href = $val["attributes"]["URL"];
                                $imght = $val["attributes"]["HEIGHT"];
                                $imgwd = $val["attributes"]["WIDTH"];
                                break;
                        case "SUMMARY":
                                $text = $val["value"];
                                break;
			case "GPHOTO:NUMPHOTOS":
				$numphotos = $val["value"];
				break;
                        case "GPHOTO:ID":
                                if (!isset($STOP_FLAG)) {
                                        $gphotoid = trim($val["value"]);
                                }
                                break;
        }

        #----------------------------------------------------------------------------
        # Once we have all the pieces of info we want, dump the output
        #----------------------------------------------------------------------------
        if (isset($thumb) && isset($title) && isset($href) && isset($gphotoid)) {

                if ($STOP_FLAG != 1) {
                        $ALBUMS_PER_ROW_LESS_ONE = $ALBUMS_PER_ROW - 1;
                        echo "<div id='title'><h2>$ALBUM_TITLE</h2></div><p><a class='back_to_list' href='" . $back_link . "'>...back to album list</a></p><p>&nbsp;</p>\n";
                        $STOP_FLAG=1;
                }
                $count++;

                echo "<div class='thumbnail'>";
                if ($USE_LIGHTBOX == "TRUE") {

			$text = addslashes($text);

                        if(isset($text)) {
                                echo "<a href=\"$href\" rel=\"lightbox[this]\" title=\"$text\"><img src='$thumb' alt='image_from_picasa'></img></a>\n";
                        } else {
                                echo "<a href=\"$href\" rel=\"lightbox[this]\" title=\"$ALBUM_TITLE\"><img src='$thumb' alt='image_from_picasa'></img></a>\n";
                        }

                } else {

                        $newhref="window.open('$href', 'mywindow','scrollbars=0, width=$imgwd,height=$imght');";
                        echo "<a href='#' onclick=\"$newhref\"><img src='$thumb' alt='image_from_picasa'></img></a>\n";

                }
                echo "</div>";

                #----------------------------------
                # Reset the variables
                #----------------------------------
                unset($thumb);
                unset($title);
                unset($href);
                unset($path);
                unset($url);
		unset($text);

        }
}

#----------------------------------------------------------------------------
# Show output for pagination
#----------------------------------------------------------------------------
if ($IMAGES_PER_PAGE != 0) {

	echo "<div id='pages'>";
	$paginate = ($numphotos/$IMAGES_PER_PAGE) + 1;
	echo "Page: ";

	# List pages
	for ($i=1; $i<$paginate; $i++) {

		$link_image_index=($i - 1) * ($IMAGES_PER_PAGE + 1);
		$href = "gallery.php?album=$ALBUM&page=$i";

		# Show current page
		if ($i == $page) {
			echo "<span class='current_page'>$i </span>";
		} else {
			echo "<a class='page_link' href='$href'>$i</a> ";
		}
	}

	echo "</div>";

}

unset($title);

#----------------------------------------------------------------------------
# Output footer if required
#----------------------------------------------------------------------------
if ($STANDALONE_MODE == "TRUE") {

        echo "</body>" . "\n";
        echo "</html>" . "\n";
}
?>
