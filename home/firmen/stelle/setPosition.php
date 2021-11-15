<?
require_once("../../../../etc/gb_config.inc.php");

if(isAjax()) {
	$sql = "INSERT INTO prakt2.firmen_standort SET firmenid = '".$_SESSION["s_firmenid"]."', lat='".mysql_real_escape_string($_POST["lat"])."', lng='".mysql_real_escape_string($_POST["lng"])."', titel='".mysql_real_escape_string($_POST["bez"])."'";
 	$hDB->query($sql, $praktdbmaster);
	echo mysql_insert_id($praktdbmaster);
	exit();
}
?>
<html>
	<head>
		<script src="http://maps.google.com/maps?file=api&v=3&language=de&key=ABQIAAAABnPBIDWSLu--59RXgZQ2WxQvmA2GV4nkX98NEKUZ1asWWzOVEhSMijjETFqPaXfAr0Q7y7n8FSwrfQ&sensor=true" type="text/javascript"></script>
		<script src="/scripts/microajax.js" type="text/javascript"></script>
		<link type="text/css" media="screen,print" rel="stylesheet" href="/styles/v2/screen.css">
		<style type="text/css">
			li {
				font-size:12px;
			}
		</style>
			<?
				$sql = "SELECT * FROM prakt2.firmen_standort WHERE firmenid = '".$_SESSION["s_firmenid"]."'";
				$result = $hDB->query($sql, $praktdbslave);
			?>		
		<script type="text/javascript">
         var map = null;
         var geo = new GClientGeocoder(); 
		 var selPos_LAT = 0;
		 var selPos_LNG = 0;
      function createMarker(point,html) {
        var marker = new GMarker(point);
        GEvent.addListener(marker, "click", function() {
          marker.openInfoWindowHtml(html);
        });
        return marker;
      }
		 
         function GetMap()
         {
		 	myOptions = {
				zoom:16,
				disableDefaultUI:true  
			};
			map = new GMap2(document.getElementById("praktMap"), myOptions);
			map.setCenter(new GLatLng(37.4419, -122.1419), 13);
			
			GEvent.addListener(map,"click", function(overlay, latlng) {     
			  if (latlng) { 
				var myHtml = "<span style='font-size:12px;'>Als Standort markieren</span>"; // The GPoint value is: " + map.fromLatLngToDivPixel(latlng) + " at zoom level " + map.getZoom();			    
			    map.openInfoWindow(latlng, myHtml);
				lat = latlng.y;
				selPos_LAT = lat;
				lng = latlng.x;
				selPos_LNG = lng;

				document.getElementById('selPos').innerHTML = lat + " - " + lng;
			  }
			});
			map.addControl(new GLargeMapControl());
			map.addControl(new GMapTypeControl());
			
			<? while($row = mysql_fetch_assoc($result)) { ?>
		      var point = new GLatLng(<?=$row["lat"].",".$row["lng"] ?>);
		      var marker = createMarker(point,'<div style="width:150px; font-size:12px;">Firmensitz<br /><b><?=htmlentities($row["titel"]) ?></b><\/div>')
		      map.addOverlay(marker);
			<? } ?>
         }   	
		 var lastOrt = 0;
		 function switchTo(lat, lng, ele) {
		 	if(lastOrt != 0) removeClass(lastOrt.parentNode, "active");
			
			map.panTo(new GLatLng(lat, lng));
		 	
			addClass(ele.parentNode, "active");
			lastOrt = ele;
			
			top.document.getElementById('lat').value = lat;
			top.document.getElementById('lng').value = lng;
			
		 }		
		 
		 function searchPoint(value) {
		      var reasons=[];
		      reasons[G_GEO_SUCCESS]            = "Success";
		      reasons[G_GEO_MISSING_ADDRESS]    = "Missing Address: The address was either missing or had no value.";
		      reasons[G_GEO_UNKNOWN_ADDRESS]    = "Unknown Address:  No corresponding geographic location could be found for the specified address.";
		      reasons[G_GEO_UNAVAILABLE_ADDRESS]= "Unavailable Address:  The geocode for the given address cannot be returned due to legal or contractual reasons.";
		      reasons[G_GEO_BAD_KEY]            = "Bad Key: The API key is either invalid or does not match the domain for which it was given";
		      reasons[G_GEO_TOO_MANY_QUERIES]   = "Too Many Queries: The daily geocoding quota for this site has been exceeded.";
		      reasons[G_GEO_SERVER_ERROR]       = "Server error: The geocoding request could not be successfully processed.";

		        geo.getLocations(value, function (result)
		          { 
		            // If that was successful
		            if (result.Status.code == G_GEO_SUCCESS) {
		              // How many resuts were found
		              // document.getElementById("message").innerHTML = "Found " +result.Placemark.length +" results";
		              // Loop through the results, placing markers
		              for (var i=0; i<result.Placemark.length; i++) {
		                var p = result.Placemark[i].Point.coordinates;
		                var marker = new GMarker(new GLatLng(p[1],p[0]));
		                // document.getElementById("message").innerHTML += "<br>"+(i+1)+": "+ result.Placemark[i].address + marker.getPoint();
		                map.addOverlay(marker);
		              }
		              // centre the map on the first result
		              var p = result.Placemark[0].Point.coordinates;
		              map.panTo(new GLatLng(p[1],p[0]));
		            }
		            // ====== Decode the error status ======
		            else {
		              var reason="Code "+result.Status.code;
		              if (reasons[result.Status.code]) {
		                reason = reasons[result.Status.code]
		              } 
		              alert('Could not find "'+value+ '" ' + reason);
		            }
		          });
		}

		 	// map.Find(null, value);
			//map.SetZoomLevel(13);
		 //}
		 function PixelClick(e) {
   	        var x = e.mapX;
            var y = e.mapY;
            pixel = new VEPixel(x, y);
            LL = map.PixelToLatLong(pixel);
			
			map.FindLocations(LL, GetResults);
		 }
        function GetResults(locations)
         {
      	    var s="";//"Results for " + LL.Latitude + ", " + LL.Longitude + ": ";
            if(locations != null)
            {
	             s += locations[0].Name;
               
            }
            else
            {
               s += "No Result found.";
            } 

            document.getElementById('selPos').innerHTML = s;
         }	 
		</script>
	</head>
	<body class="companies" onload='GetMap();'>
		<div style='float:left;height:600px;width:200px; border-right:1px solid #ccc;'>
			<fieldset style="padding:10px; border:1px solid #ccc; font-size:12px; text-align:center;">
				<legend>Suchen</legend>
				<input type='text' name='searchOrt'id='searchOrt' value='' /><br />
				<button onclick='searchPoint(document.getElementById("searchOrt").value);'><span><span><span>Suchen</span></span></span></button>
			</fieldset>
			<br />
			<ul id='einsatzListe'>
			<?
				mysql_data_seek($result, 0);				
				while($row = mysql_fetch_assoc($result)) {
					echo "<li id='einsatzort_".$row["id"]."' class='einsatzort'><a href='#' class='stdStyle' onclick='switchTo(".$row["lat"].",".$row["lng"].", this)'>".htmlentities($row["titel"])."</a></li>";
				}
			?>
			</ul>
			<br />
			<fieldset style=" font-size:12px;padding:10px; ">
				<legend>Neuer Einsatzort</legend>
				Bezeichnung: <input type='text' name='bezeichnung'id='bezeichnung' value='' /><br />
				Markierte Position:<br />
				<span id='selPos' style='font-size:12px; font-weight:bold;'>- keine -</span>
				<button onclick='savePos(document.getElementById("bezeichnung").value, selPos_LAT, selPos_LNG);'><span><span><span>Einsatzort anlegen</span></span></span></button>
			</fieldset>
		</div>
		
		<div id='praktMap' style='margin-left:200px; height:590px;overflow:hidden;position:relative; width:480px; background-color:red;'>
			
		</div>		
		<script type="text/javascript">
			function savePos(bezeichnung, lat, lng) {
				xhr('/home/firmen/stelle/setPosition.php','bez=' + bezeichnung + '&lat=' + lat + '&lng=' + lng + '&stellenid=<?=(int)$_GET["stellenid"] ?>', function(text) {
					$('einsatzListe').innerHTML = $('einsatzListe').innerHTML + "<li class='einsatzort' id='einsatzort_"+text+"'><a href='#' class='stdStyle' onclick='switchTo("+this.lat+","+this.lng+", this)'>" + this.bez + "</a></li>"; 
				}.bind({lat:lat, lng:lng, bez:bezeichnung}))
				
			}
		</script>
		
	</body>	
</html>