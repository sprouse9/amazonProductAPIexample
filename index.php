<?php

	// This is the Amazon response test
	// input an ASIN to fetch the data
	// display the data in a div section
	// click to import the item into your inventory

	


	if(!isset($shopify_inventory)) {
		$shopify_inventory = array();
		array_push($shopify_inventory, "098172723");
		array_push($shopify_inventory, "098172724");
		array_push($shopify_inventory, "098172111");
	}



	if(isset($_GET['itemId'])) {	 // AJAX call was made

		$itemId = $_GET['itemId'];
		$amazonURL = getAmazonURL( $itemId );
		echo $amazonURL;








		die(0);
	}

	function getAmazonURL($itemId) {


		//if(isset($_GET['asin'])) {

		// Your Access Key ID, as taken from the Your Account page
		$access_key_id = "AKIAJLTWCOOXWWCJSUOA";

		// Your Secret Key corresponding to the above ID, as taken from the Your Account page
		$secret_key = "mSAWN6bLP922IHQ5BCODxIMDW2+HdV7hMgkuHNXy";

		// The region you are interested in
		$endpoint = "webservices.amazon.com";

		$uri = "/onca/xml";

		$params = array(
		    "Service" => "AWSECommerceService",
		    "Operation" => "ItemLookup",
		    "AWSAccessKeyId" => "AKIAJLTWCOOXWWCJSUOA",
		    "AssociateTag" => "sprouse9-20",
		    "ItemId" => $itemId,
		    "IdType" => "ASIN",
		    "ResponseGroup" => "Images,ItemAttributes"
		);

		// Set current timestamp if not set
		if (!isset($params["Timestamp"])) {
		    $params["Timestamp"] = gmdate('Y-m-d\TH:i:s\Z');
		}

		// Sort the parameters by key
		ksort($params);

		$pairs = array();

		foreach ($params as $key => $value) {
		    array_push($pairs, rawurlencode($key)."=".rawurlencode($value));
		}

		// Generate the canonical query
		$canonical_query_string = join("&", $pairs);

		// Generate the string to be signed
		$string_to_sign = "GET\n".$endpoint."\n".$uri."\n".$canonical_query_string;

		// Generate the signature required by the Product Advertising API
		$signature = base64_encode(hash_hmac("sha256", $string_to_sign, $secret_key, true));

		// Generate the signed URL
		//request_url = 'http://'.$endpoint.$uri.'?'.$canonical_query_string.'&Signature='.rawurlencode($signature);
		
		$amazonURL = 
			'http://'.$endpoint.$uri.'?'.$canonical_query_string.'&Signature='.rawurlencode($signature);

		$results = http_get($amazonURL, array("timeout"=>1), $info);
		return $results;


		//return 'http://'.$endpoint.$uri.'?'.$canonical_query_string.'&Signature='.rawurlencode($signature);

		//echo "\"".$request_url."\"";
		//}
	}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Amazon to Shopify item import test</title>
	</head>


	<style>

		div {
			border: 1px solid;
			border-collapse: collapse;
			border-width: 1px;
			display: inline-block;
			width: 40%;
			min-height: 300pt;

		}
	</style>




<body>

	<pre>
		<?php

			// if(isset($_GET))
			// 	print_r($_GET); 
		?>
	</pre>



	<p>
		<?php 
			if(isset($amazonURL))
				echo "Signed URL: \"".$request_url."\"";
			?>
	</p>





	<form>
		Item ASIN:<br>
		<input type="text" name="asin" id="asinTextBox" value="0976925524"><br>
		<input type="button" name="fetchASIN" value="fetch ASIN" onclick="getAmazonASIN()">
	</form>




<div id="amazonResultsDiv">
	Amazon Result:

</div>
<div>Shopify Inventory:
	<?php
		// output all of the shopify inventory
		foreach($shopify_inventory as $item) {
			echo "<br>" . $item;
		}

	?>
	
</div>


<script>

	var searchResultsDiv = document.getElementById("amazonResultsDiv");
	var search 			 = document.getElementById("asinTextBox");
	var url;
	var xhr;
	var amazonURL;

	function getAmazonASIN(){
		// here we do our Ajax Call


		url = "index.php?itemId=" + asinTextBox.value;

		xhr = new XMLHttpRequest();
		xhr.open('GET', url, true);
		xhr.onreadystatechange = function() {

			if(xhr.readyState == 4 && xhr.status == 200) {
				// place the results into the amazon div
				//amazonURL = xhr.responseText;
				searchResultsDiv.innerHTML += xhr.responseText;
			}
		}

		xhr.send();	// gets the Amazon URL



		// Update 01-28-18: We cannot use Ajax to make a call to another server
		// now do another AJAX call, this time to the Amazon server
		//amazonXHR = new XMLHttpRequest();
		// amazonXHR.open('GET', amazonURL, true);
		// amazonXHR.onreadystatechange = function() {
		// 	if(amazonXHR.readyState == 4 && amazonXHR.status == 200) {
		// 		// place the results into the amazon div
		// 		searchResultsDiv.innerHTML += amazonXHR.responseText;
		// 	}	
		//}
		//amazonXHR.send();

	}

</script>



</body>
</html>