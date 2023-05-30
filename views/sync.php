<?php
$now = date('Y-m-d', strtotime('now'));
$db_name = 'spektrix';
$db_user = 'root';
$db_pass = 'f3f4p4ax';

$api_name = 'Spektrix';
$api_type = "json";
$key = "qWoTkvGxAgQmvw/a+t9tT41M5HpgSgEFU0jj8DL3ttSreT3UtWxC9nuKM6haspDzNTko5GTLSWL25e1kBnSOP9qftXqSvQjAZb7k09tJkzbwEbUr1zUAIcAAxdC8KYNHjdH+5+nbnqwFb3sqh//DwW1LxY7xR7FMbXnJmU4PXCDgXLja/wXpX1W3g82RIavqCeySEvIEsfUnYw89EhAsUzKn1Y8Pya1O5e570Y0AJUYBragQjzF3AjDIsEdMywue347owtc+5dKzsm4RwieiNkI7sa99IIf1tJMrSJXNV16NHKcOJXl+f1PAo1axO9tvlMqTC9e/CX4ZVTO+sIUmXg==";
$req_url = 'https://buy.ica.art/ica/api/v3/events?instanceStart_from=' . $now;
$api_user = 'ica_sync';

// $api_name = 'Shopify';
// $api_type = "graphql";
// $key = "0df4a2d60f5c99276aaba8f4265b06e4";
// $req_url = 'https://new-york-consolidated-2.myshopify.com';

function handleResponse($existing_ids = array(), $callback = null){
    // spektrix
    if($callback) $callback();
}
function generateSpektrixUserpwd($key, $user){
    $stringToSign = "GET\n" . $url . "\n" . $gmtnow;
    $signature = base64_encode( hash_hmac('sha1', $stringToSign, base64_decode($key)) );
    $output = "SpektrixAPI3 ".$user.":" . $signature;
    return $output;
}
$api_userpwd = generateSpektrixUserpwd($key, $api_user);
if(!isset($_POST['action']) || $_POST['action'] != "sync")
{
?>
    <form action="" method="post" enctype="multipart/form-data">
        <span>Sync<?php echo isset($api_name) && $api_name ? ' with ' . $api_name : ''; ?></span>
        <input name='action' type='hidden' value='sync'>
        <input name='submit' type='submit' value='Sync'>
    </form><?
}
else
{
    $req_instances_url = 'https://buy.ica.art/ica/api/v3/instances';
    $req_instances_url .= '?startFrom=' . $now;
    $eventAddCount = 0;
    $eventUpdateCount = 0;
    $insAddCount = 0;
    $insUpdateCount = 0;
    $eventDetails = array();
    $event_ids = array();

    $sql_insExists = "SELECT * FROM `spektrix` WHERE event_id=? AND event_instance_id=?";
    $sql_updateIns = "UPDATE `spektrix` SET venue=?,date_time=?,duration=?,date_modified=? WHERE id=?";
    $sql_insertIns = "INSERT INTO `spektrix` (event_id, event_instance_id, venue, date_time, duration, date_modified, date_created) VALUES (?, ?, ?, ?, ?, ?, ?)";

    function CallAPI($method, $url, $userpwd, $header = array(), $data = false)
    {
        global $key;
        $curl = curl_init();
        $gmtnow = gmdate('D, j M Y H:i:s GMT');
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
        
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_USERPWD, $userpwd);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }
    $events = CallAPI('GET', $req_url, $api_userpwd);

    $instances = CallAPI('GET', $req_instances_url, $api_userpwd);
    $instances = json_decode($instances, true);
    $events = json_decode($events, true);
    $roots = array(
        "Exhibition" => 15,
        "Exhibitions" => 15,
        "Film" => 17,
        "Films" => 17,
        "Talks & Learning" => 18,
        "Live" => 16
    );

    foreach($events as $e)
    {
        $spektrix_id = intval($e['id']);
        $event_ids[] = $spektrix_id;
        $root = $roots[$e['attribute_Category']];
        $booking_url = $e['isOnSale'] ? "/book/$spektrix_id" : '';

        $sql = "SELECT objects.* FROM objects, wires WHERE objects.name2='$spektrix_id' AND wires.toid=objects.id AND objects.active=1 AND wires.active=1";
        $res = $db->query($sql)->fetch_assoc();
        if ($res != null && sizeof($res) > 0)
        {
            
            $processed = array(
                "begin" => date($oo::MYSQL_DATE_FMT, strtotime( $e['firstInstanceDateTime'] )), // begin date
                "end" => date($oo::MYSQL_DATE_FMT, strtotime( $e['lastInstanceDateTime'] )) // end date
            );
            if($res['begin'] !== $processed['begin'] || $res['end'] !== $processed['end'])
            {
                foreach($processed as $key => $value)
                {
                    if($value)
                        $processed[$key] = "'".addslashes($value)."'";
                    else
                        $processed[$key] = "null";
                }
                $oo->update($res['id'], $processed);
                $eventUpdateCount++;
                $eventDetails[$spektrix_id] = array(
                    'eventName' => $e['name'],
                    'ins' => array()
                );
            }
        }
        else
        {
            // check url part 1
            $siblings = $oo->children($root);
            $s_urls = array();
            foreach($siblings as $s)
                $s_urls[] = $s['url'];
            $url = slug($e['name']);
            
            $processed = array(
                "name1" => '.' . $e['name'], // title - text
                "name2" => $spektrix_id, // ticketing id
                "country" => $booking_url, // booking url
                "begin" => date($oo::MYSQL_DATE_FMT, strtotime($e['firstInstanceDateTime'])), // begin date
                "end" => date($oo::MYSQL_DATE_FMT, strtotime($e['lastInstanceDateTime'])), // end date
                "url" => $url // slug!
            );

            foreach($processed as $key => $value)
            {
                if($value)
                    $processed[$key] = "'".addslashes($value)."'";
                else
                    $processed[$key] = "null";
            }

            $event_id = $oo->insert($processed);

            if ($event_id != 0){
                $ww->create_wire($root, $event_id);

                // check url part 2
                $urlIsValid = validate_url($url, $s_urls);
                if( !$urlIsValid )
                {
                    $url = valid_url($url, strval($event_id), $s_urls);
                    $new['url'] = "'".$url."'";
                    $oo->update($event_id, $new);
                }
            }
            $eventAddCount++;
            $eventDetails[$spektrix_id] = array(
                'eventName' => $e['name'],
                'ins' => array()
            );
        }
        $thisInstances = array_filter($instances, function($ins){ 
            global $spektrix_id;
            $e_id =intval($ins['event']['id']);
            if($e_id == $spektrix_id) return $ins; 
        });
        $duration = addslashes($e['duration']);
        foreach($thisInstances as $ins){
            $e_id = intval($ins['event']['id']);
            $ins_id = intval($ins['id']);
            $time = addslashes(date($oo::MYSQL_DATE_FMT, strtotime($ins['start'])));
            $venue = addslashes($ins['attribute_Venue']);
            $stmt = $db->prepare($sql_insExists);
            $stmt->bind_param("ss", $e_id, $ins_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $now = addslashes(date($oo::MYSQL_DATE_FMT, strtotime("now")));
            if($result != null && sizeof($result) > 0)
            {
                $new = array(
                    'venue'         => $venue,
                    'date_time'     => $time,
                    'duration'      => $duration
                );
                $old = array(
                    'venue'         => $result['venue'],
                    'date_time'     => $result['date_time'],
                    'duration'      => $result['duration']
                );

                if(!empty(array_diff($old, $new)))
                {
                    $new['date_modified'] = $now;
                    $new[] = $result['id'];
                    $stmt = $db->prepare($sql_updateIns);
                    $stmt->bind_param("sssss", ...$new);
                    $stmt->execute();
                    $insUpdateCount++;
                    $eventDetails[$e_id]['ins'][] = array(
                        'date_time' => $result['date_time'],
                        
                    );
                }
                continue;
            }
            else
            {
                // $e_id = $e_id;
                // $ins_id = $ins_id;
                $stmt = $db->prepare($sql_insertIns);
                $stmt->bind_param("sssssss", $e_id, $ins_id, $venue, $time, $duration, $now, $now);
                $stmt->execute();
                $insAddCount++;
                $eventDetails[$e_id]['ins'][] = array(
                    'date_time' => $time,
                    
                );
                continue;
            }
        }
    }
    echo "<br />Synced.<br /><br />";
    echo "<br />Events added: $eventAddCount<br />Events updated: $eventUpdateCount<br /><br />";
    echo "<br />Instances added: $insAddCount<br />Instances updated: $insUpdateCount<br /><br />";
    if(count($eventDetails))
    {
        echo 'Detail: <br>';
        $e_counter = 1;
        $e_digits = count($eventDetails) < 100 ? 2 : 3;
        $i_pad = '&nbsp;';
        for($i = 0; $i < $e_digits; $i++)
            $i_pad .= '&nbsp;';
        foreach($eventDetails as $e)
        {
            
            echo '<div class="event-row" >' . str_pad($e_counter, $e_digits, "0", STR_PAD_LEFT) . ' ' . $e['eventName'];
            $ins_counter = 1;
            $i_digits = count($e['ins']) < 100 ? (count($e['ins']) < 10 ? 1 : 2) : 3;
            foreach($e['ins'] as $ins)
            {
                echo '<div class="ins-row" >'.  $i_pad . '- ' . $ins['date_time'] . '</div>';
                $ins_counter++;
            }
            echo '</div>';
            $e_counter++;
        }
        echo '<br><br>';
    }
    
}