<?php

	// This is the Amazon response test
	// input an ASIN to fetch the data
	// display the data in a div section
	// click to import the item into your inventory

	if(!isset($shopify_inventory)) {
		// $shopify_inventory = array();
		// array_push($shopify_inventory, "098172723");
		// array_push($shopify_inventory, "098172724");
		// array_push($shopify_inventory, "098172111");

		// attempt to fetch Shopify inventory and display it
		// GET https://testsprouse9.myshopify.com/admin/products.json
		// https://91e4cea4698f6b28550dfe85eb55fced:6bea82a24d8feda4a520701ecda77c8d@testsprouse9.myshopify.com/admin/products.json
		// https://91e4cea4698f6b28550dfe85eb55fced:6bea82a24d8feda4a520701ecda77c8d@testsprouse9.myshopify.com/admin/orders.json

		$shopfiy_GET = "https://91e4cea4698f6b28550dfe85eb55fced:6bea82a24d8feda4a520701ecda77c8d@testsprouse9.myshopify.com/admin/products.json";

		$cSession = curl_init();

		// step 2
		curl_setopt($cSession, CURLOPT_URL, $shopfiy_GET);
		curl_setopt($cSession, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cSession, CURLOPT_HEADER, false);

		// step 3
		$temp = curl_exec($cSession);
		$shopify_inventory = json_decode($temp);

		// step 4
		curl_close($cSession);
	}

	if(isset($_GET['itemId'])) {	 // AJAX call was made

		$itemId = $_GET['itemId'];
		$amazonURL = getAmazonURL( $itemId );
		//echo "<div>" . $amazonURL . "</div>";

		// step 1
		$cSession = curl_init();

		// step 2
		curl_setopt($cSession, CURLOPT_URL, $amazonURL);
		curl_setopt($cSession, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cSession, CURLOPT_HEADER, false);

		// step 3
		$result = curl_exec($cSession);

		// step 4
		curl_close($cSession);

		//echo htmlentities($result);

		$oXML = new SimpleXMLElement($result);

		//echo $oXML->Items->Item->CustomerReviews->IFrameURL;

		echo "<img src=\"" . $oXML->Items->Item->MediumImage->URL . "\">";

		$OfferSummary = $oXML->Items->Item->OfferSummary;

		echo "<table>";
		echo "<tr>" . 
				"<td>LowestNewPrice: " . $OfferSummary->LowestNewPrice->FormattedPrice . "</td>" .
				"<td>Available: " . $OfferSummary->TotalNew . "</td></tr>";
		echo "<tr>" . 
				"<td>LowestUsedPrice: " . $OfferSummary->LowestUsedPrice->FormattedPrice . "</td>" .
				"<td>Available: " . $OfferSummary->TotalUsed . "</td></tr>";
		echo "<tr>" .
				"<td><input type=\"button\" name=\"addToInventory\" value=\"Add to inventory\" id=\"addToInventory-button\" onclick=\"addAmazonItemToShopify()\"</td>";
		echo "</table>";

		// echo "<div><pre>";
		// print_r($oXML);
		// echo "</pre></div>";

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
		    "ResponseGroup" => "Images,OfferSummary"
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
		return 'http://'.$endpoint.$uri.'?'.$canonical_query_string.'&Signature='.rawurlencode($signature);
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
			width: 100%;
			min-height: 200pt;
			vertical-align: top;
		}


		#fetchASIN-button {
			background: lightblue;
			height: 20px;
		}


		#fetchASIN-button:disabled {
		    background: white;
		    color: #555;
		}

		#shopfiyinventory {
			border: 1px;
		}


#shopifytable {
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    width: 100%;
    font-size: 14px;
}

#shopifytable td, #shopifytable th {
    border: 1px solid #ddd;
    padding: 8px;
}

#shopifytable tr:nth-child(even){background-color: #f2f2f2;}

#shopifytable tr:hover {background-color: #ddd;}

#shopifytable th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #4CAF50;
    color: white;
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
		<input type="text" name="asin" id="asinTextBox" value="B000P297IO"><br>
		<input type="button" name="fetchASIN" value="fetch ASIN" id="fetchASIN-button" onclick="getAmazonASIN()">
	</form>


	<p>

<div id="amazonResultsDiv">
	Amazon Result:

</div>
<div>Shopify Inventory:

	<table id="shopifytable">
		<tr>
			<th>id</th><th>title</th>
		</tr>

		<?php

			foreach ($shopify_inventory->products as $product) {
				echo "<tr><td>" . $product->id . "</td><td>" . $product->title . "</td></tr>";
				//echo "<tr><td>" . $product->id . "</td><td>" . $product->title . "</td><td>" . $product->price . "</td></tr>";
			}





		?>



	</table>

	<?php
		// output all of the shopify inventory
		// foreach($shopify_inventory as $item) {
		// 	echo "<br>" . $item;
		//}

		//echo "<p>" . $shopify_inventory->products[0]->title;


		// echo "<table>";
		// echo "<tr>" . 
		// 		"<td>LowestNewPrice: " . $OfferSummary->LowestNewPrice->FormattedPrice . "</td>" .
		// 		"<td>Available: " . $OfferSummary->TotalNew . "</td></tr>";
		// echo "<tr>" . 
		// 		"<td>LowestUsedPrice: " . $OfferSummary->LowestUsedPrice->FormattedPrice . "</td>" .
		// 		"<td>Available: " . $OfferSummary->TotalUsed . "</td></tr>";

		// echo "<tr>" .
		// 		"<td><input type=\"button\" name=\"addToInventory\" value=\"Add to inventory\"</td>";

		// echo "</table>";


//		foreach($shopify_inventory->products)



		//var_dump($shopify_inventory);

	?>
	
</div>


<script>

	var searchResultsDiv = document.getElementById("amazonResultsDiv");
	var search 			 = document.getElementById("asinTextBox");
	var button  	     = document.getElementById("fetchASIN-button");

	var url;
	var xhr;
	var amazonURL;

	function getAmazonASIN(){
		// here we do our Ajax Call

		url = "amazoniframe.php?itemId=" + asinTextBox.value;

		xhr = new XMLHttpRequest();
		xhr.open('GET', url, true);
		xhr.onreadystatechange = function() {

			if(xhr.readyState == 4 && xhr.status == 200) {
				// place the results into the amazon div
				amazonURL = xhr.responseText;
				searchResultsDiv.innerHTML = amazonURL;

				button.disabled = 'disabled';
			}
		}

		xhr.send();	// gets the Amazon info

		}

	function addAmazonItemToShopify() {

		// There should be an Amazon item already pulled up otherwise the button would not show
		// The Amazon item info is in the PHP object $oXML


		/*
		$oXML->OperationRequest


                            [2] => SimpleXMLElement Object
                                (
                                    [@attributes] => Array
                                        (
                                            [Name] => IdType
                                            [Value] => ASIN
                                        )

                                )

                            [3] => SimpleXMLElement Object
                                (
                                    [@attributes] => Array
                                        (
                                            [Name] => ItemId
                                            [Value] => B000P297IO
                                        )

                                )

*/

		//rl = "amazoniframe.php?addItem=" + 




	}



</script>


</body>
</html>