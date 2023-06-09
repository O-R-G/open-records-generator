<?php
$response = array(
    'status' => '',
    'body' => ''
);

if(!isset($_POST['action']) || $_POST['action'] != "sync")
{
    $response['status'] = 'error';
    $response['body'] = 'nothing here';
    echo json_encode($response);
    exit;
}
else
{
	require_once(__DIR__ . '/../config/config.php');
    require_once(__DIR__ . '/../models/model.php');
    require_once(__DIR__ . '/../models/objects.php');
    require_once(__DIR__ . '/../models/wires.php');
    $db = db_connect('admin');
    $oo = new Objects();
    $ww = new Wires();

	$isTest = true;
	$api_url = $isTest ? getenv("SHOPIFY_API_URL_SANDBOX") :  getenv("SHOPIFY_API_URL_LIVE");
	$api_url .= '/api/graphql';
	$accessToken = $isTest ? getenv("SHOPIFY_ACCESS_TOKEN_SANDBOX") :  getenv("SHOPIFY_ACCESS_TOKEN_LIVE");

	$query = array(
		'all' => `query FirstProduct {
			products(first:200) {
				edges {
					node {
						id
						title
						description
						descriptionHtml
					}
				}
			}   
		}`
	);

	
	// $data = file_get_contents( "php://input" );
	// $data = json_decode($data, true);
	// $s_urls = $data['s_urls'];

	function CallAPI($method, $url, $userpwd = '', $header = array(), $data = false)
    {
        $curl = curl_init();        
        if(!empty($header)) curl_setopt ($curl, CURLOPT_HTTPHEADER, $header);
        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // Optional Authentication:
        if($userpwd) {
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			curl_setopt($curl, CURLOPT_USERPWD, $userpwd);
		}
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }
	$products = CallAPI('POST', $api_url, '', array(
		"Content-Type" => "application/graphql",
		"X-Shopify-Storefront-Access-Token" => $accessToken
	), $query['all']);

	var_dump($products);
	die();

	try{
		$temp = $oo->urls_to_ids(array('buy'));
		$buy_id = end($temp);
		$buy_children = $oo->children($buy_id);
		$existing_product_ids = array();
		$existing_product_id_to_item = array();
		$s_urls = array();
		foreach($buy_children as $child)
		{
			$this_id = base64_encode( 'gid://shopify/Product/' . get_single_tag($child['deck']) );
			$existing_product_ids[] = $this_id;
			$existing_product_id_to_item[$this_id] = $child;
			$s_urls[] = $child['url'];
		}
		try{
			$temp = $oo->urls_to_ids(array('donate'));
			$donate_id = end($temp);
			$donate_item = $oo->get($donate_id);
			$donate_product_id = get_single_tag($donate_item['deck']);
			$hasDonate = true;
		}
		catch(Exception $err)
		{
			try{
				$temp = $oo->urls_to_ids(array('donation'));
				$donate_id = end($temp);
				$donate_item = $oo->get($donate_id);
				$donate_product_id = get_single_tag($donate_item['deck']);
				$hasDonate = true;
			}
			catch(Exception $err)
			{
				$hasDonate = false;
				$donate_product_id = -1;
			}
		}
		
		?>
		<p>Synced.</p><br>
		<div id="products-added">Products added: <span class="counter" count = "0">0</span><br>
			<p></p>
		</div>
		<div id="products-updated">Products updated: <span class="counter" count = "0">0</span><br>
			<p></p>
		</div>
		<script>
			// for update_msg();
		var update_msg_elements = [];
		update_msg_elements[0] = {};
		update_msg_elements[0]['block'] = document.getElementById('products-added');
		update_msg_elements[0]['counter'] = update_msg_elements[0]['block'].querySelector('.counter');
		update_msg_elements[0]['p'] = update_msg_elements[0]['block'].querySelector('p');
		update_msg_elements[1] = {};
		update_msg_elements[1]['block'] = document.getElementById('products-updated');
		update_msg_elements[1]['counter'] = update_msg_elements[1]['block'].querySelector('.counter');
		update_msg_elements[1]['p'] = update_msg_elements[1]['block'].querySelector('p');

		var existing_product_ids = <?= json_encode($existing_product_ids); ?>;
		var existing_product_id_to_item = <?= json_encode($existing_product_id_to_item); ?>;
		var donate_product_id = window.btoa('gid://shopify/Product/<?= $donate_product_id; ?>');
		var s_urls = <?= json_encode($s_urls); ?>;
		var hasDonate = <?= json_encode($hasDonate); ?>;
		var isTest = false;
		var product_updated_counter = 0;
		var product_added_counter = 0;
		
		const query_all = `query FirstProduct {
			products(first:200) {
				edges {
					node {
						id
						title
						description
						descriptionHtml
					}
				}
			}   
		}`;
		const fetchQuery_all = () => {
				// Define options for first query with no variables and body is string and not a json object
				const optionsQuery_all = {
					method: "post",
					headers: {
						"Content-Type": "application/graphql",
						"X-Shopify-Storefront-Access-Token": accessToken
					},
					body: query_all
				};

				// Fetch data and remember product id
				fetch(api_url + `/api/graphql`, optionsQuery_all)
					.then(res => res.json())
					.then(response => {
						console.log("<< Fetching products...");
						var edges = response.data.products.edges;
						edges.forEach(function(el, i){
							var this_id = el.node.id;
							var this_title = el.node.title;
							var this_obj = {};	
							if(this_id != donate_product_id)
							{
								if( this_title.toLowerCase() == 'donation' || 
										this_title.toLowerCase() == 'donate'
								)
								{
									if(hasDonate){
										// if a product's name is similar to donate with a different ID, and donate record exists...
										console.log('['+i+'] '+el.node.title+' ('+this_id + ') conflicts with the current '+el.node.title+' record.');
										return;
									}
									else
									{
										// for some reason donate record is rebuilt.
										console.log('['+i+'] '+el.node.title+' ('+this_id + ') is a new donatation record. Adding it to database...');
										var this_product_id = window.atob(el.node.id);
										this_product_id = this_product_id.replace('gid://shopify/Product/', '');
										this_obj['name1'] = el.node.title;
										this_obj['body'] = el.node.descriptionHtml;
										this_obj['deck'] = '[' + this_product_id + ']';
										this_obj['action'] = 'insert';
										this_obj['s_urls'] = s_urls;
										this_obj['parent_id'] = 0;
										send_ajax('insert', this_obj, i);
									}
								}
								if( existing_product_ids.includes(this_id) )
								{
									var this_item = existing_product_id_to_item[this_id];
									var toBeUpdated = false;
									var this_description_html = el.node.descriptionHtml;
									if(this_description_html != this_item['body']){
										this_obj['body'] = this_description_html;

										if(toBeUpdated === false)
											toBeUpdated = '';
										else
											toBeUpdated += ',';
										toBeUpdated += ' description';
									}
									if( this_title != this_item['name1'] && 
											'.' + this_title != this_item['name1'] // keep the flexibility of hidding records with period.
										){

										this_obj['name1'] = this_title;

										if(toBeUpdated === false)
											toBeUpdated = '';
										else
											toBeUpdated += ',';
										toBeUpdated += ' name';
									}
									if(this_title == '1996' && toBeUpdated !== false){
										console.log('['+i+'] needs update:' + toBeUpdated);
										this_obj['id'] = this_item['id'];
										this_obj['action'] = 'update';
										this_obj['s_urls'] = s_urls;
										send_ajax('update', this_obj, i);
									}
									else
									{
										console.log('['+i+'] nothing to update.');
									}
								}
								else
								{
									console.log('['+i+'] '+el.node.title+' ('+this_id + ') is a new product. Adding it to database...');
									var this_product_id = window.atob(el.node.id);
									this_product_id = this_product_id.replace('gid://shopify/Product/', '');
									console.log(this_product_id);
									this_obj['name1'] = el.node.title;
									this_obj['body'] = el.node.descriptionHtml;
									this_obj['deck'] = '[' + this_product_id + ']';
									this_obj['action'] = 'insert';
									this_obj['s_urls'] = s_urls;
									this_obj['parent_id'] = <?= $buy_id; ?>;
									send_ajax('insert', this_obj, i);
								}
							}		            	
						});
					});
				}
				fetchQuery_all();
		</script>
	<?
	}
	catch(Exception $e)
	{
		echo 'Please add a record with the url "buy" as the parent of products.' . "\n";
	}
	
	
?>
<script>
	function send_ajax(type, data, idx)
	{
		var xmlhttp;
		var requestUrl = '/open-records-generator/views/sync-shopify-handler.php';
		if(window.XMLHttpRequest)
		{
			// code for IE7+, Firefox, Chrome, Opera, Safari
			xmlhttp = new XMLHttpRequest();
		}
		else
		{
			// code for IE6, IE5
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
		
		xmlhttp.onreadystatechange = function() 
		{
			if(xmlhttp.readyState == 4 && xmlhttp.status == 200) 
			{
				if(xmlhttp.responseText)
				{
					var response = JSON.parse(xmlhttp.responseText);
					if(response.length != 0 && response != '0')
					{
						if(type == 'update'){
							console.log('['+idx+'] updated successfully.');
							update_msg(type, data, update_msg_elements);
						}
						else if(type == 'insert'){
							console.log('['+idx+'] added successfully.');
							update_msg(type, data, update_msg_elements);
						}
					}
					else if(response == '0')
					{
						if(type == 'update')
							console.log('['+idx+'] fail updating the product.');
						else if(type == 'insert')
							console.log('['+idx+'] fail adding the product.');
					}
					else
						console.log('['+idx+'] no response');
				}
				else
					console.log('['+idx+'] no responseText');
			}
		}
		xmlhttp.open("POST", requestUrl, true);
		xmlhttp.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
		data = JSON.stringify(data)
		xmlhttp.send(data);
	}

	function update_msg(type, data, elements_arr)
	{
		if(type == 'insert')
			var elements = elements_arr[0];
		else if(type == 'update')
			var elements = elements_arr[1];
		var this_count = parseInt(elements['counter'].getAttribute('count'));
		this_count ++;
		elements['counter'].innerText = this_count;
		elements['counter'].setAttribute('count', this_count);
		// elements['p'].innerHTML += data['name1'] + '<br>';
	}
</script>
<?php }