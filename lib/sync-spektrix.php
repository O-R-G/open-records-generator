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
    function generateSpektrixUserpwd($url, $key, $user){
        $gmtnow = gmdate('D, j M Y H:i:s GMT');
        $stringToSign = "GET\n" . $url . "\n" . $gmtnow;
        $signature = base64_encode( hash_hmac('sha1', $stringToSign, base64_decode($key)) );
        $output = "SpektrixAPI3 ".$user.":" . $signature;
        return $output;
    }
    function CallAPI($method, $url, $userpwd, $header = array(), $data = false)
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
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_USERPWD, $userpwd);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }
    $spektrixTable = getenv("SPEKTRIX_TABLE");
    $spektrixAPIKey = getenv("SPEKTRIX_API_KEY");
    $spektrixAPIUser = getenv("SPEKTRIX_API_USER");

    $now = date('Y-m-d', strtotime('now'));
    $api_url = array(
        'events' => getenv("SPEKTRIX_API_URL") . '/events?instanceStart_from=' . $now,
        'instances' => getenv("SPEKTRIX_API_URL") . '/instances?startFrom=' . $now
    );
    $api_userpwd = generateSpektrixUserpwd($api_url['events'], $spektrixAPIKey, $spektrixAPIUser);
    $sql = array(
        'eventExists' => "SELECT objects.id, objects.begin, objects.end FROM objects, wires WHERE objects.name2=? AND wires.toid=objects.id AND objects.active=1 AND wires.active=1",
        'insExists' => "SELECT * FROM `".$spektrixTable."` WHERE event_id=? AND event_instance_id=?",
        'updateIns' => "UPDATE `".$spektrixTable."` SET venue=?,date_time=?,duration=?,date_modified=? WHERE id=?",
        'insertIns' => "INSERT INTO `".$spektrixTable."` (event_id, event_instance_id, venue, date_time, duration, date_modified, date_created) VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    $eventAddCount = 0;
    $eventUpdateCount = 0;
    $insAddCount = 0;
    $insUpdateCount = 0;
    $eventDetails = array();
    $event_ids = array();

    $roots = array(
        "Exhibition" => 15,
        "Exhibitions" => 15,
        "Film" => 17,
        "Films" => 17,
        "Talks & Learning" => 18,
        "Live" => 16
    );

    $events = CallAPI('GET', $api_url['events'], $api_userpwd);
    $events = json_decode($events, true);
    $instances = CallAPI('GET', $api_url['instances'], $api_userpwd);
    $instances = json_decode($instances, true);

    foreach($events as $e)
    {
        $spektrix_id = intval($e['id']);
        $event_ids[] = $spektrix_id;
        $root = $roots[$e['attribute_Category']];
        $booking_url = $e['isOnSale'] ? "/book/$spektrix_id" : '';

        $stmt = $db->prepare($sql['eventExists']);
        $stmt->bind_param("s", $spektrix_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows)
        {
            $stmt->bind_result($e_id, $e_begin, $e_end) ;
            $stmt->fetch();
            $res = array(
                'id' => $e_id,
                'begin' => $e_begin,
                'end' => $e_end
            );
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
                    $new = array(
                        'url' => "'".$url."'"
                    );
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
            $e_id = intval($ins['event']['id']);
            if($e_id == $spektrix_id) return $ins; 
        });
        // echo $spektrix_id . ' >>><br>';
        $duration = addslashes($e['duration']);
        foreach($thisInstances as $ins){
            $e_id = intval($ins['event']['id']);
            $ins_id = intval($ins['id']);
            $time = addslashes(date($oo::MYSQL_DATE_FMT, strtotime($ins['start'])));
            $venue = addslashes($ins['attribute_Venue']);
            $stmt = $db->prepare($sql['insExists']);
            $stmt->bind_param("ss", $e_id, $ins_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $now = addslashes(date($oo::MYSQL_DATE_FMT, strtotime("now")));
            if($result != null && sizeof($result) > 0)
            {
                $new = array(
                    'venue'         => "'" . $venue . "'",
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
                    $new = array_values($new);
                    $stmt = $db->prepare($sql['updateIns']);
                    $stmt->bind_param("sssss", ...$new);
                    $stmt->execute();
                    $insUpdateCount++;
                    // var_dump( $eventDetails[$e_id]);
                    // echo '<br><br>';
                }
                continue;
            }
            else
            {
                $stmt = $db->prepare($sql['insertIns']);
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
    $response['status'] = 'success';
    $response['body'] .= "Synced.<br /><br />";
    $response['body'] .= "<br />Events added: $eventAddCount<br />Events updated: $eventUpdateCount<br /><br />";
    $response['body'] .= "<br />Instances added: $insAddCount<br />Instances updated: $insUpdateCount<br /><br />";
    if(count($eventDetails))
    {
        $response['body'] .= 'Detail: <br>';
        $e_counter = 1;
        $e_digits = count($eventDetails) < 100 ? 2 : 3;
        $i_pad = '&nbsp;';
        for($i = 0; $i < $e_digits; $i++)
            $i_pad .= '&nbsp;';
        foreach($eventDetails as $e)
        {
            $response['body'] .= '<div class="event-row" >' . str_pad($e_counter, $e_digits, "0", STR_PAD_LEFT) . ' ' . $e['eventName'];
            $ins_counter = 1;
            $i_digits = count($e['ins']) < 100 ? (count($e['ins']) < 10 ? 1 : 2) : 3;
            foreach($e['ins'] as $ins)
            {
                $response['body'] .= '<div class="ins-row" >'.  $i_pad . '- ' . $ins['date_time'] . '</div>';
                $ins_counter++;
            }
            $response['body'] .= '</div>';
            $e_counter++;
        }
    }
    echo json_encode($response);
}
