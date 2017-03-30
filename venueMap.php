<!DOCTYPE html>
<html>
  <head>
    <title>Simple Map</title>
    <meta name="viewport" content="initial-scale=1.0">
    <meta charset="utf-8">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
	<link href="./polaris/polaris.css" rel="stylesheet">
	<script src="icheck.js"></script>
	<script>
	$(document).ready(function(){
	  $('input').iCheck({
	    checkboxClass: 'icheckbox_polaris',
	    radioClass: 'iradio_polaris',
	    increaseArea: '-10%' // optional
	  });
	});
	</script>
    <link rel="stylesheet" href="style3.css" type="text/css">
    <script>
	<?php
		$submit = 0;
		$center = "44.977276,-93.232266";
		$limit = 25;
		$radius = 1500;
		$defaultCats = "4d4b7104d754a06370d81259,4d4b7105d754a06374d81259,4d4b7105d754a06376d81259,4d4b7105d754a06377d81259,4d4b7105d754a06378d81259,4d4b7105d754a06379d81259,4d4b7105d754a06372d81259,4d4b7105d754a06375d81259,4e67e38e036454776db1fb3a";
		if (isset($_POST["submit"])) {
			$submit = 1;
		   $category = $_POST["category"];
		   $categoryIDs = "";
		   if(empty($category)){
		   	$categoryIDs = $defaultCats;
		   }
		   else{
			   for($i = 0; $i < count($category) - 1; $i++){
				   		$categoryIDs = $categoryIDs . $category[$i] . ",";
				}
				$categoryIDs = $categoryIDs . $category[count($category) - 1];
			}
		   $limit = $_POST["limit"];
		   $radius = $_POST["radius"];
		   $center = $_POST["location"];
		  	$json = file_get_contents("https://api.foursquare.com/v2/venues/search?ll=$center&oauth_token=1P2BMLQAPWYJM3OTQ3JWDAGTEPNPVZ5ZNFOY1WHE1ELXJYXK&radius=$radius&limit=$limit&categoryId=$categoryIDs&v=20151114");
			$json_data = json_decode($json, true);
			$venues = $json_data[response][venues];
			$N = count($venues);
			for($i = 0; $i < $N; $i++){
				$name[$i] = $venues[$i][name];
				$address[$i] = $venues[$i][location][address];
				$lat[$i] = $venues[$i][location][lat];
				$lng[$i] = $venues[$i][location][lng]; 
				$prefix[$i] = $venues[$i][categories][0][icon][prefix];
				$suffix[$i] = $venues[$i][categories][0][icon][suffix];
			}
		}
	?> 
		var submit = <?php echo $submit ?>;
		var center = <?php echo json_encode($center) ?>;
		var centerPair = center.split(",");
		var limit = <?php echo $limit ?>;
		var radius = <?php echo $radius ?>;
		//document.getElementById("test").value = "haha";
		function initMap() {
			map = new google.maps.Map(document.getElementById('map'), {
			    center: {lat: parseFloat(centerPair[0]), lng: parseFloat(centerPair[1])},
			    zoom: 15
			});
			if(submit == 1){
				var category = <?php echo json_encode($category) ?>;
				if(category){
					for(var i = 0; i < category.length; i++){
						document.getElementById(category[i]).checked = true;
					}
				}
				var map;
				var marker;
				var N = <?php echo json_encode($N) ?>;
				var lat = <?php echo json_encode($lat) ?>;
				var lng = <?php echo json_encode($lng) ?>;
				var venueName = <?php echo json_encode($name) ?>;
				var prefix = <?php echo json_encode($prefix) ?>;
				var suffix = <?php echo json_encode($suffix) ?>;
				var buildingMarkers = new Array(N);
				var address = <?php echo json_encode($address) ?>;

				for(var i = 0; i < N; i++){
				  	buildingMarkers[i] = new google.maps.Marker({
				    position: {lat: lat[i], lng: lng[i]},
				    title: venueName[i],
				    map: map,
				    icon: prefix[i] + 'bg_32' + suffix[i],
					});
				}
				var marker = new google.maps.Marker({
					position: {lat: parseFloat(centerPair[0]), lng: parseFloat(centerPair[1])},
					map: map 
				});
			}
			var infowindows = new Array(N);
			var contentStrings = new Array(N);
			for(var i = 0; i < N; i++){
					contentStrings[i] = '<h2>'+venueName[i]+'</h2>'
					+'<p>'+address[i]+'</p>'+'<p>'+lat[i]+','+'<br>'+lng[i]+'<p>';
				    infowindows[i] = new google.maps.InfoWindow({content: contentStrings[i], maxWidth: 150});
			}			
			for(var i = 0; i < N; i++){
				buildingMarkers[i].addListener('click', (function(marker, i) {
				  return function() {
				    infowindows[i].open(map, buildingMarkers[i]);
				  }
				})(buildingMarkers[i], i));
			}
			map.addListener('click', function(event){
				addMarker(event.latLng);
				document.getElementById('location').value = event.latLng.toUrlValue();
				document.getElementById('submit').disabled = false;
			});				
			function addMarker(latLng) {
				if(marker != null){
					marker.setMap(null); 
				}
				marker = new google.maps.Marker({
				    position: latLng,
				    map: map
				});
				infoHello = new google.maps.InfoWindow({content: "Hello"});
				marker.addListener('click', (function(marker) {
					return function() {
						infoHello.open(map, marker);
					}
				})(marker));
			}
		}
		// document.getElementById("limitRange").value = limit;
		// document.getElementById("limitNumber").value = limit;
		// document.getElementById("radiusRange").value = radius;
		// document.getElementById("radiusNumber").value = radius;
		function limitChange(){
			var limit = document.getElementById("limitRange").value;
			document.getElementById("limitNumber").value = limit;
		}
		function radiusChange(){
			var radius = document.getElementById("radiusRange").value;
			document.getElementById("radiusNumber").value = radius;
		}
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCIM3PIi-5eQVKcK0FxpPKtujJC2lBzgAQ&callback=initMap" async defer></script>
  </head>
  <body>
  	<input type="hidden" name="test" id="test" value="">
  	<div id="floating-panel">
  	<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST" enctype="multipart/form-data">
  	<input type="hidden" name="location" id="location" value="">
  	
  	<input type="checkbox" name="category[]" id="4d4b7104d754a06370d81259" value="4d4b7104d754a06370d81259"> <label>Arts & Entertainment</label><br>
  	<input type="checkbox" name="category[]" id="4d4b7105d754a06374d81259" value="4d4b7105d754a06374d81259"> <label>Food</label><br>
  	<input type="checkbox" name="category[]" id="4d4b7105d754a06376d81259" value="4d4b7105d754a06376d81259"> <label>Nightlife Spot</label><br>
  	<input type="checkbox" name="category[]" id="4d4b7105d754a06377d81259" value="4d4b7105d754a06377d81259"> <label>Outdoors & Recreation</label><br>
  	<input type="checkbox" name="category[]" id="4d4b7105d754a06378d81259" value="4d4b7105d754a06378d81259"> <label>Shop & Service</label><br>
  	<input type="checkbox" name="category[]" id="4d4b7105d754a06379d81259" value="4d4b7105d754a06379d81259"> <label>Travel & Transport</label><br>
  	<input type="checkbox" name="category[]" id="4d4b7105d754a06372d81259" value="4d4b7105d754a06372d81259"> <label>College & Universities</label><br>
  	<input type="checkbox" name="category[]" id="4d4b7105d754a06375d81259" value="4d4b7105d754a06375d81259"> <label>Professional & Others places</label><br>
  	<input type="checkbox" name="category[]" id="4e67e38e036454776db1fb3a" value="4e67e38e036454776db1fb3a"> <label>Residence</label><br>
  	
  	<center><label>Limit (K):</label><br>
  	<input type="range" name="limit" id="limitRange" min="0" max="50" value="25" onchange="limitChange()"><br>
  	<input type="text" id="limitNumber" value="25" disabled><br>
  	<label>Radius (M):</label><br>
  	<input type="range" name="radius" id="radiusRange" min="0" max="3000" step="100" onchange="radiusChange()"><br>
  	<input type="text" id="radiusNumber" value="1500" disabled><br>
  	<br>
  	<input type="submit" name="submit" id="submit" value="Submit" disabled></center>
  	</form>
  	</div>
    <div id="map"></div>   
  </body>
</html>