## Introduction ##

If you want, you can modify the code so that it will only display public albums and then you can specify any PicasaWeb username to display that user's public albums on your page.  The procedure below explains how to accomplish that.

## Procedure ##

### For v1.5+ ###

Set the variable $PUBLIC\_ONLY=TRUE in config.php and specify the user whose albums you wish to embed using the $PICASAWEB\_USER variable.

### For v1.4 ###
Change the $PICASAWEB\_USER variable in config.php and comment out the following lines in index.php:

```
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: AuthSub token="' . $GDATA_TOKEN . '"'
 )); 
```

At this point, you should see the public albums of whichever user you specified in the variable above.