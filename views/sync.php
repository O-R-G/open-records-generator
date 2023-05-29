<?php
    $key = "qWoTkvGxAgQmvw/a+t9tT41M5HpgSgEFU0jj8DL3ttSreT3UtWxC9nuKM6haspDzNTko5GTLSWL25e1kBnSOP9qftXqSvQjAZb7k09tJkzbwEbUr1zUAIcAAxdC8KYNHjdH+5+nbnqwFb3sqh//DwW1LxY7xR7FMbXnJmU4PXCDgXLja/wXpX1W3g82RIavqCeySEvIEsfUnYw89EhAsUzKn1Y8Pya1O5e570Y0AJUYBragQjzF3AjDIsEdMywue347owtc+5dKzsm4RwieiNkI7sa99IIf1tJMrSJXNV16NHKcOJXl+f1PAo1axO9tvlMqTC9e/CX4ZVTO+sIUmXg==";
    $now = date('Y-m-d',strtotime('now'));
    $req_url = 'https://buy.ica.art/ica/api/v3/events';
    $req_url .= '?instanceStart_from=' . $now;
    
    function CallAPI($method, $url, $data = false)
    {
        global $key;
        $curl = curl_init();

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
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, "SpektrixAPI3 icasync:yIZot86wLbldDR1oYhVZC4sG63M=");

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }
    $result = CallAPI('GET', $req_url);
    var_dump(base64_decode($key));
    var_dump($result);
?>
<script>
    let now = '<?= $now; ?>';
    let req_url = 'https://system.spektrix.com/ica/api/v3/events';
    req_url += '?instanceStart_from=' + now;
    console.log(req_url);
    let request = new XMLHttpRequest();
    request.onreadystatechange = function(){
        if(request.readystate == 4 && reqeust.status == 200)
        {
            console.log(request.responseText);
        }
    };
    request.open('GET', req_url);
    request.send();

</script>