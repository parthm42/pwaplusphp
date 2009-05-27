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

<?PHP

#----------------------------------------------------------------------------
# CONFIGURATION
#----------------------------------------------------------------------------
require_once("config.php");


#----------------------------------------------------------------------------
# VARIABLES
#----------------------------------------------------------------------------
$FILTER = $_REQUEST['filter'];
if ($REQUIRE_FILTER != "FALSE") {
	if ((!isset($FILTER)) || ($FILTER == "")) {
		die("Permission Denied.  Filter is required.");
	}
}

$ALL = $_REQUEST['all'];
if ($ALL == "") { $ALL = 0; }

$file = "http://picasaweb.google.com/data/feed/api/user/" . $PICASAWEB_USER . "?kind=album";

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
# Start the output table
#----------------------------------------------------------------------------
echo "<html>\n<head>\n";
echo "<title>" . $PICASAWEB_USER . "'s Picasa Galleries</title>\n";
echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"style.css\" />\n";
echo "</head>\n";
echo "<table cellpadding=0 cellspacing=0 align=center>\n";

#----------------------------------------------------------------------------
# Iterate over the array and extract the info we want
#----------------------------------------------------------------------------
unset($thumb);
unset($title);
unset($href);
unset($num);
unset($description);
foreach ($vals as $val) {

	switch ($val["tag"]) {

			case "MEDIA:THUMBNAIL":
				$thumb = trim($val["attributes"]["URL"] . "\n");
				break;	
                        case "MEDIA:TITLE":
                                $title = trim($val["value"]);
                                break;
                        case "LINK":
				if ($val["attributes"]["REL"] == "alternate") {
                                	$href = trim($val["attributes"]["HREF"]);
				}
                                break;
                        case "GPHOTO:NUMPHOTOS":
                                $num = trim($val["value"]);
                                break;
			case "PUBLISHED":
                                $published = trim($val["value"]);
				$published = substr($published,0,10);	
                                break;
        }

	#----------------------------------------------------------------------------
	# Once we have all the pieces of info we want, dump the output
	#----------------------------------------------------------------------------
	
	if (isset($thumb) && isset($title) && isset($href) && isset($num) && isset($published)) {
		
		if ($ALL == 1) { 
			$pos = 0;
		} else { 
			if ($FILTER != "") {
				$pos = strlen(strpos($title,$FILTER));
				if ($pos > 0) { $pos = 0; }
				else { $pos = 1; }
			} else {
				$pos = strlen(strpos($title,"_hide"));
			}
		}
		
		if ($pos == 0) {
			$count++;
			if ($count == 1) {
				echo "<tr>\n";
			}
			list($disp_name,$tags) = split('_',$title);
			echo "<td><table cellpadding=0 cellspacing=0>\n";
			echo "<tr><td class=imagetd>\n";
			echo "<a href='gallery.php?album=$title'><img border=0 src='$thumb'></a>";
			echo "</td></tr><tr><td valign=top class=titletd>";
			echo "<a href='gallery.php?album=$title'>$disp_name</a><BR><i><font size=-1>$published, $num images</font></i>\n";
			echo "</td></tr></table></td>\n";

			#----------------------------------
			# End the row and restart the count
			#----------------------------------
			if ($count == $ALBUMS_PER_ROW) {
				echo "</tr>\n";
				$count=0;
			}
		}
			#----------------------------------
			# Reset the variables
			#----------------------------------
			unset($thumb);
			unset($title);
			unset($href);
			unset($num);
			unset($description);
	
	}
}
echo "</table></body></html>\n";
unset($title);
?>
