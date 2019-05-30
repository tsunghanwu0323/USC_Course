<?php
$keyword = $category = $distance = $location = $location_input = "";
$json = $geojson = $detail_json = null;
$details = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$keyword = $_POST["keyword"];
	$category = $_POST["category"];
	$distance = $_POST["distance"];
	$location = $_POST["location"];
	if (isset($_POST["location_input"])) {
		$location_input = $_POST["location_input"];
	}
	else {
		$location_input = "";
	}
	$geojson = json_decode($_POST["geojson"]);
	$json = get_place($keyword, $category, $distance, $location, $location_input, $geojson);
	exit(json_encode($json));
}

if (isset($_REQUEST["id"])) {
	$placeid = $_REQUEST["id"];
	get_details($placeid);
}

// Get searching result
function get_place($keyword, $category, $distance, $location_type, $location, $geojson) {
    // If location is user input, find geo info by using Google map API.
	if ($location_type == "user_input") {
		$url = "https://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($location)."&key=AIzaSyAbb9L5xEaZ8XZNAwHhL4Hywn0Ag2jdIhA";
		$tmpjson = json_decode(file_get_contents($url));
		$geojson = $tmpjson->results[0]->geometry->location;
	}
    $rad = $distance * 1609.344; // Turn miles to kms.
    // Google map API nearby search.
	$url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=".$geojson->lat.",".$geojson->lng."&radius=".$rad."&type=".$category."&keyword=".urlencode($keyword)."&key=YOUR_GOOGLE_API_KEY";
	$json = json_decode(file_get_contents($url));
	$json->geojson = $geojson;
	return $json;
}

// Get detail information of selected place.
function get_details($placeid) {
    // Google map API details search.
	$url = "https://maps.googleapis.com/maps/api/place/details/json?placeid=".$placeid."&key=YOUR_GOOGLE_API_KEY";
	$details = file_get_contents($url);
    $detail_json = json_decode($details);
    // Retrieve photos' url if exist.
	if (isset($detail_json->result->photos)) {
		$imgs = $detail_json->result->photos;
	}
	else {
		$imgs = null;
		exit($details);
    }
    // Make directory to save images for further retrieval.
	if (!file_exists("./imgs")) {
		mkdir("./imgs", 0777, true);
	}
    delete_all_file("./imgs");
    // Retrieve 5 photos by Google map API
	for ($i=0; $i < 5 && $i < count($imgs); $i++) { 
		$img = $imgs[$i];
		$img_url = "https://maps.googleapis.com/maps/api/place/photo?photoreference=".$img->photo_reference."&maxwidth=".$img->width."&maxheight=".$img->height."&key=AIzaSyAbb9L5xEaZ8XZNAwHhL4Hywn0Ag2jdIhA";
		file_put_contents("./imgs/".$img->photo_reference.".jpg", file_get_contents($img_url));
	}
	exit($details);
}

// Delete files.
function delete_all_file($path) {
	$handle = opendir($path);
	if ($handle) {
		while (false !== ($item = readdir($handle))) {
			if ($item != "." && $item != "..")
				unlink("$path/$item");
		}
		closedir($handle);
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Search Place</title>
	<meta charset="utf-8">
	<meta name="referrer" content="no-referrer">
	<meta author="Xiaoyi He">
	<style type="text/css">
		body {
			text-align: center;
		}
		#mapdiv {
			width: 400px;
			height: 300px;
			/*visibility: hidden;*/
			display: none;
			position: absolute;
			z-index: 0;
		}
		#mapdiv.show {
			/*visibility: visible;*/
			display: block;
		}
		#photo_table {
			display: none;
			margin: auto;
			width: 500px;
			padding-top: 20px;
			text-align: center;
		}
		#review_table {
			align-content: center;
			text-align: center;
			display: none;
			width: 600px;
			margin: auto;
			padding-top: 20px;
		}
		#review_table p {
			text-align: left;
		}
		#review_table.show {
			display: block;
		}
		#photo_table.show {
			display: block;
		}
		#details {
			text-align: center;
			margin: auto;
		}
		#review_table tbody tr td {
			width: 600px;
		}
		#photo_table tbody tr td a img {
			height: 400px;
			width: 500px;
			padding: 10px;
		}
		#photo_table tbody tr td {
			width: 500px;
		}
		ul {
			list-style-type: none;
			margin: 0;
			padding: 0;
		}
		#operations {
			position: absolute;
			left: 0;
			top: 0;
			z-index: 100;
		}
		#map {
			height: 100%;
			z-index: 1;
		}
		a {
			color: black;
			text-decoration: none;
		}
		a:active {
			color: black;
		}
		a:visited {
			color: black;
		}
		#operations ul li {
			padding: 10px;
			background-color: rgb(249, 249, 249);
		}
		#operations ul li:hover {
			background-color: rgb(195, 195, 195);
		}
		.arrow_up {
			display: block;
			height: 50px;
			width: 50px;
			margin: auto;
			background-size: 100%;
			background-image: url("https://img.icons8.com/ios/50/000000/sort-up.png");
		}
		.arrow_down {
			display: block;
			height: 50px;
			width: 50px;
			margin: auto;
			background-size: 100%;
			background-image: url("https://img.icons8.com/ios/50/000000/sort-down.png");
		}
		#formbox {
			border: 2px;
			border-color: rgb(195, 195, 195);
			height: 200px;
			width: 600px;
			margin: auto;
			padding: 10px;
			background-color: rgb(249, 249, 249);
			text-align: left;
			border-style: solid;
		}
		#search_form h2 {
			font-family: serif;
			font-style: italic;
			text-align: center;
		}
		#search_form hr {
			color: rgb(195, 195, 195);
		}
		#results {
			margin: auto;
			margin-top: 50px;
			text-align: center;
			width: 1000px;
		}
		table {
			border-collapse: collapse;
		}
		#results table {
			margin: auto;
		}
		#results table tr td {
			height: 40px;
			border: 1px solid;
			border-color: rgb(195, 195, 195);
		}
		#results table tr td img {
			height: 40px;
		}
		#no_record {
			border: 2px solid;
			border-color: rgb(195, 195, 195);
			background-color: rgb(249, 249, 249);
			width: 600px;
			text-align: center;
			margin: auto;
		}
		#location_list {
			list-style-type: none;
			margin: 0;
			padding: 0;
			display: inline;
			position: absolute;
		}
		#buttons {
			margin-top: 30px;
			margin-left: 40px;  
		}
	</style>
</head>
<body>
	<div id="formbox">
		<form id="search_form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" >
			<h2>Travel and Entertainment Search</h2>
			<hr>
			<label for="keyword"><b>Keyword</b></label>
			<input id="keyword" name="keyword" type="text" required="required" value="<?php echo $keyword ?>">
			<br>
			<label for="category"><b>Category</b></label>
			<select name="category" id="category">
				<option value="default">default</option>
				<option value="cafe">cafe</option>
				<option value="bakery">bakery</option>
				<option value="restaurant">restaurant</option>
				<option value="beauty_salon">beauty salon</option>
				<option value="casino">casino</option>
				<option value="movie_theater">movie theater</option>
				<option value="lodging">lodging</option>
				<option value="airport">airport</option>
				<option value="train_station">train station</option>
				<option value="subway_station">subway station</option>
				<option value="bus_station">bus station</option>
			</select>
			<br>
			<label for="distance"><b>Distance(miles)</b></label>
			<input id="distance" name="distance" type="text" value="10">
			<b>from</b>
			<ul id="location_list">
				<li>
					<input type="radio" id="here" name="location" value="here" checked="checked">
					<label for="here">Here</label>
				</li>
				<li>
					<input type="radio" id="location" name="location" value="user_input">
					<input type="text" id="location_input" name="location_input" disabled="disabled" placeholder="location" required="required">
				</li>
			</ul>
			<input type="hidden" name="geojson" id="geojson">
			<div id="buttons">
				<button id="search" disabled="disabled" name="search">Search</button>
				<input id="clear" name="clear" onclick="clearpage()" type="button" value="Clear">
			</div>
		</form>
	</div>

	<div id="results"></div>
	<div id="mapdiv">
		<div id="operations">
			<ul>
				<li><a href="javascript:direction('WALKING')">Walk there</a></li>
				<li><a href="javascript:direction('BICYCLING')">Bike there</a></li>
				<li><a href="javascript:direction('DRIVING')">Drive there</a></li>
			</ul>
		</div>
		<div id="map"></div>
	</div>
</body>
<script type="text/javascript">
	
	var geojson = null;
	var search = document.getElementById("search");
	var form = document.getElementById("search_form");
	var xhttp = new XMLHttpRequest();
	xhttp.open("GET", "http://ip-api.com/json", false); // Get geo info of user current location
	xhttp.send();
	var results;
	var user_location;
	var position;
    
    // Check XHTTP ready state and status.
	if (xhttp.readyState == 4 && xhttp.status == 200) {
			geojson = JSON.parse(xhttp.responseText);
			search.disabled = false;
			user_location = {
				"lat": geojson["lat"],
				"lng": geojson["lon"]
			}
			document.getElementById("geojson").value = JSON.stringify(user_location);
	}

    // Remove all child.
	function remove_all_child(nodename) {
		var node = document.getElementById(nodename);
		while (node && node.firstChild) {
			node.removeChild(node.firstChild);
		}
	}

	var location_check = document.getElementById("location");
	var location_input = document.getElementById("location_input");
	var user_location_checked = document.getElementById("here");

    // Turn on/off location input field.
	location_check.addEventListener("change", function() {
		if (this.checked) {
			location_input.disabled = false;
		}
	})
	user_location_checked.addEventListener("change", function() {
		if (this.checked) {
			location_input.disabled = true;
		}
	})
	
	var clear = document.getElementById("clear");

    // Clear page after click clear button
	function clearpage() {
			form.reset();
			location_input.disabled = true;
			remove_all_child("results");
	}

    // Submit search form
	form.addEventListener("submit", function(event) {
		event.preventDefault();
		var url = form.action;
		var params = "";
		var data = new FormData(form);
		for (const entry of data) {
			params += entry[0] + "=" + encodeURIComponent(entry[1]) + "&";
		}
		params = params.slice(0, -1);
		var xhttp = new XMLHttpRequest();
		xhttp.open("POST", url, false);
		xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhttp.send(params);
		results = JSON.parse(xhttp.responseText);
		user_location = results["geojson"];
		show_result(results);
		
	}, false);
    
    // Show searching results.
	function show_result(results) {
		if (results == null) {
			return;
		}

		result_div = document.getElementById("results");
        // Clear current result if exist.
        if (result_div.firstChild) {
			remove_all_child("results");
		}

		result_list = results["results"]
        // Show no records if no records.
		if (result_list.length == 0) {
			var node = document.createElement("div");
			node.innerHTML = "<b>No Records has been found!<b>";
			node.id = "no_record";
			result_div.appendChild(node);
			return;
		}

		var table = document.createElement("table");
		var th = table.insertRow();
		var thc1 = document.createElement("td");
		var thc2 = document.createElement("td");
		var thc3 = document.createElement("td");
		thc1.innerHTML = "<b>Category</b>";
		thc2.innerHTML = "<b>Name</b>";
		thc3.innerHTML = "<b>Address</b>";
		th.appendChild(thc1);
		th.appendChild(thc2);
		th.appendChild(thc3);
		table.appendChild(th);
		var i = 1;
        
        // Create result table.
		for (var result of result_list) {
			var tr = table.insertRow();
			tr.id = "row" + i;
			i = i + 1;
			var td = tr.insertCell();
			var img = document.createElement("img");
			img.src = result["icon"];
			td.appendChild(img);
			var td = tr.insertCell();
			var name = document.createElement("a");
			name.innerHTML = result["name"];
			name.href = "javascript:detail('" + result["place_id"] + "')";
			td.appendChild(name);
			var td = tr.insertCell();
			var place = document.createElement("a");
			place.innerHTML = result["vicinity"]
			position = result["geometry"]["location"];
			place.href = "javascript:pop_map("+JSON.stringify(position)+",'"+tr.id+"')";
			td.appendChild(place);
		}
		result_div.appendChild(table);
	}

    // Request detail info of selected place.
	function detail(placeid) {
		remove_all_child("results");    // Remove results.
		var xmlhttp = new XMLHttpRequest();
		var details;

		xmlhttp.onreadystatechange = function () {
			if (this.readyState == 4 && this.status == 200) {
				details = JSON.parse(xmlhttp.responseText);
				show_detail(details["result"]);
			}
		}
		xmlhttp.open("GET", "place.php?id=" + placeid, true);
		xmlhttp.send();
	}

    // Show reviews of selected place.
	function show_reviews() {
		var arrow_review = document.getElementById("arrow_review");
		var arrow_photo = document.getElementById("arrow_photo");
		var review_table = document.getElementById("review_table");
		var photo_table = document.getElementById("photo_table");
		arrow_review.classList.toggle("arrow_up");
		arrow_review.classList.toggle("arrow_down");
		review_table.classList.toggle("show");
		if (photo_table.classList.contains("show")) {
			photo_table.classList.toggle("show");
			arrow_photo.classList.toggle("arrow_up");
			arrow_photo.classList.toggle("arrow_down");
		}
	}

    // Show photos of selected place.
	function show_photos() {
		var arrow = document.getElementById("arrow_review");
		var arrow = document.getElementById("arrow_photo");
		var review_table = document.getElementById("review_table");
		var photo_table = document.getElementById("photo_table");
		arrow_photo.classList.toggle("arrow_up");
		arrow_photo.classList.toggle("arrow_down");
		photo_table.classList.toggle("show");
		if (review_table.classList.contains("show")) {
			review_table.classList.toggle("show");
			arrow_review.classList.toggle("arrow_up");
			arrow_review.classList.toggle("arrow_down");
		}
	}

    // Show detail info of selected place.
	function show_detail(details) {
		var detail_div = document.getElementById("results");
		if (detail_div.firstChild) {
			return;
		}
		var arrow_review = document.createElement("div");
		arrow_review.id = "arrow_review";
		arrow_review.className = "arrow_down";
		
		var arrow_photo = document.createElement("div");
		arrow_photo.id = "arrow_photo";
		arrow_photo.className = "arrow_down";
		var name = document.createElement("p");
		name.innerHTML = "<b>"+details["name"]+"</b>";
		detail_div.appendChild(name);
		var text = document.createElement("p");
		text.innerHTML = "click to show reviews";
		detail_div.appendChild(text);
		var review_table = document.createElement("table");
		review_table.id = "review_table";
		var reviews = details["reviews"];
        // Show no review if no revied found.
		if (reviews == undefined || reviews.length == 0) {
			var tr = review_table.insertRow();
			var td = tr.insertCell();
			td.innerHTML = "<b>No Reviews Found</b>";
		}
		else {
			for (var i = 0; i < 5 && i < reviews.length; i++) {
                var tr = review_table.insertRow();
                var td = tr.insertCell();
                var avatar = document.createElement("img");
                avatar.src = reviews[i]["profile_photo_url"];
                var author = document.createElement("b");
                author.innerHTML = reviews[i]["author_name"];
                
                // Show avatar if avatar exists.
                if (avatar.src != undefined) {
                    td.appendChild(avatar);
                }
                td.appendChild(author);
                var tr = review_table.insertRow();
                var td = tr.insertCell();
                td.innerHTML = reviews[i]["text"];
			}
		}
		
		review_table.visibility = "hidden";
		detail_div.appendChild(review_table);
		arrow_review.onclick = show_reviews;
		detail_div.insertBefore(arrow_review, detail_div.lastChild);
		var text = document.createElement("p");
		text.innerHTML = "click to show photos";
		detail_div.appendChild(text);
		detail_div.appendChild(arrow_photo)
		var photo_table = document.createElement("table");
		photo_table.id = "photo_table";
		var photos = details["photos"];
        // Show no photos if no phote found.
		if (photos == undefined || photos.length == 0) {
			var tr = photo_table.insertRow();
			var td = tr.insertCell();
			td.innerHTML = "<b>No Photos Found</b>";
		}
		else {
            // Show five photos.
			for (var i = 0; i < 5 && i < photos.length; i++) {
                var tr = photo_table.insertRow();
                var td = tr.insertCell();
                var img = document.createElement("img");
                var a = document.createElement("a")
                img.src =  "./imgs/" + photos[i]["photo_reference"] + ".jpg";
                img.alt =  "image" + i;
                a.href = img.src;
                a.target = "_blank";
                a.appendChild(img);
                td.appendChild(a);
			}
		}
		photo_table.visibility = "hidden";
		detail_div.appendChild(photo_table);
		arrow_photo.onclick = show_photos;
		detail_div.insertBefore(arrow_photo, detail_div.lastChild);
	}

	var map;
	var marker;
	var direction_display;
	var direction_service;

    // Initiate map.
	function initMap() {
		var location = user_location;
		map = new google.maps.Map(document.getElementById('map'), {
          zoom: 13,
          center: user_location,
          mapTypeControl: false,
        });
        
        // Add marker at user current location.
		add_marker(user_location);
		direction_service = new google.maps.DirectionsService();
		direction_display = new google.maps.DirectionsRenderer();
	}

    // Show direction according to different mode.
	function direction(mode) {
		remove_marker();    // Remove marker before show route.
		direction_display.setMap(map);
		var start = user_location;
		var end = position;
		var request = {
			origin: start,
			destination: end,
			travelMode: mode
		}
		direction_service.route(request, function(res, status){
			if (status == "OK") {
				direction_display.setDirections(res);
			}
		});
	}

    // Add marker.
	function add_marker(pos) {
		marker = new google.maps.Marker({
          position: pos,
          map: map
        });
	}

    // Remove marker.
	function remove_marker() {
		marker.setMap(null);
	}

    // Show map.
	function pop_map(pos, row) {
		remove_marker();
		direction_display.setMap(null);
		position = pos;
		map.setCenter(pos);
		add_marker(pos);
		var mapdiv = document.getElementById('mapdiv');
		var row = document.getElementById(row).lastChild;
		var rect = row.getBoundingClientRect()
		mapdiv.style.top = (rect.top + window.pageYOffset + 50) + "px";
		mapdiv.style.left = rect.left + "px";
		mapdiv.classList.toggle("show");
	}
    
	show_result(results);   
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?&key=YOUR_GOOGLE_API_KEY&callback=initMap">
</script>
</html>
