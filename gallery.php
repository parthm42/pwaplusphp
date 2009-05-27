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

#----------------------------------------------------------------------------
# VARIABLES
#----------------------------------------------------------------------------
$file = "http://picasaweb.google.com/data/feed/api/user/" . $PICASAWEB_USER . "/album/" . $LOCATION . "?kind=photo&thumbsize=" . $THUMBSIZE . "&imgmax=" . $IMGMAX;

#----------------------------------------------------------------------------
# Grab album data from URL
#----------------------------------------------------------------------------
$ALBUM = $_REQUEST['album'];
$LOCATION = str_replace(" ","",$ALBUM);
list($ALBUM_TITLE,$tags) = split('_',$ALBUM);

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
echo "<table cellpadding=0 cellspacing=0 align=center width=100%>\n";

#----------------------------------------------------------------------------
# Iterate over the array and extract the info we want
#----------------------------------------------------------------------------
unset($thumb);
unset($title);
unset($href);
unset($path);
unset($url);
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
                        echo "<tr><td colspan=$ALBUMS_PER_ROW_LESS_ONE style=\"padding-bottom: 10px;\"><h2>$ALBUM_TITLE</h2></td></tr>\n";
                        $STOP_FLAG=1;
                }
                $count++;
                if ($count == 1) {
                        echo "<tr>\n";
                }

                echo "<td align=center class=imagetd>";
                if ($USE_LIGHTBOX == "TRUE") {

                        if(isset($text)) {
                                echo "<a href='$href' rel='lightbox[this]' title='$text'><img border=0 src='$thumb'></a>\n";
                        } else {
                                echo "<a href='$href' rel='lightbox[this]' title='$ALBUM_TITLE'><img border=0 src='$thumb'></a>\n";
                        }

                } else {

                        $newhref="window.open('$href', 'mywindow','scrollbars=0, width=$imgwd,height=$imght');";
                        echo "<a href='#' onclick=\"$newhref\"><img border=0 src='$thumb'></a>\n";

                }
                echo "</td>";

                #----------------------------------
                # End the row and restart the count
                #----------------------------------
                if ($count == $ALBUMS_PER_ROW) {
                        echo "</tr>\n";
                        $count=0;
                }

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
echo "</table>\n";
unset($title);
?>
