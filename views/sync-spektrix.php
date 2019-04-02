<?php
/*
  Schema for an "patronbase" performances table.

  Name:
  patronbase

  Structure:
  id – INT
  production_id – TEXT
  performance_id – TEXT
  venue – TEXT
  booking_url – TEXT
  date_time – DATETIME
  duration – INT
  status_code – TEXT
  date_modified – DATETIME
  date_created – DATETIME

*/
?>
<div id="body-container">
	<div id="body">
		<div id="self-container"><?
		if($rr->action != "sync")
		{
		?>
			<form action="<? echo $admin_path; ?>sync-spektrix" method="post">
				<span>Sync with Spektrix </span>
				<input name='action' type='hidden' value='sync'>
				<input name='submit' type='submit' value='Sync'>
			</form><?
		}
		else
		{
			$spektrix_site_name = 'ica';
      $endpoint = 'https://system.spektrix.com/' . $spektrix_site_name . '/api/v1/eventsrestful.svc';

			$eventAddCount = 0;
      $eventUpdateCount = 0;

			$instanceUpdateCount = 0;

      // get spektrix list of all future events from now
			$getFrom = '/allattributes/from?date=' . date('Y-m-d\TH:i:s',strtotime('now'));
			$spektrixData = getXML($endpoint, $getFrom);

			// delete existing instances where instance time > now
			$sql_del = "DELETE FROM spektrix WHERE date_time > NOW()";
			$res_del = $db->query($sql_del);

      // iterate through events
      foreach ($spektrixData->Event as $event) {
				// print_r($event);

        // make event
        $eventObject = array(
          "name" => (string)$event->Name,
					"category" => (string)getCategory($event),
          "spektrix_id" => (string)$event->Id,
          "begin_date" => (string)$event->FirstInstance,
          "end_date" => (string)$event->LastInstance,
					"on_sale_on_web" => (string)$event->OnSaleOnWeb
        );

        $eventAdded = addOrUpdateEvent($eventObject);

				if ($eventAdded)
					$eventAddCount++;
				else
					$eventUpdateCount++;

        // iterate through instances

				foreach ($event->Times->EventTime as $instance) {
					// print_r($instance);

					// make instance
          $instanceObject = array(
            "spektrix_id" => (string)$event->Id,
            "instance_id" => (string)$instance->EventInstanceId,
            "date" => (string)$instance->Time,
            "duration" => (string)$event->Duration,
            "venue" => (string)getVenue($instance)
          );

					// print_r($instanceObject);

          $instanceAdded = addOrUpdateInstance($instanceObject);
					$instanceUpdateCount++;
        }
      }

			echo "Synced.<br /><br />Events Added: $eventAddCount<br />Events Updated: $eventUpdateCount<br /><br />Instances Updated: $instanceUpdateCount";
		}
		?></div>
	</div>
</div>

<?php

// get xml from path and return
function getXML($endpoint, $path) {
	$url = $endpoint . $path;
	$xml = simplexml_load_file($url) or die("Error: Cannot create object");
	return $xml;
}

// gets the category for event
function getCategory($event) {
	$attributes = $event->Attributes->EventAttribute;
	foreach ($attributes as $attribute) {
		if ((string)$attribute->Name == "Category") {
			return $attribute->Value;
		}
	}
}

// gets the venue for an instance
function getVenue($instance) {
	$attributes = $instance->Attributes->EventAttribute;
	foreach ($attributes as $attribute) {
		if ((string)$attribute->Name == "Venue") {
			return $attribute->Value;
		}
	}
}

// adds spektrix event or updatees if already in
function addOrUpdateEvent($event) {
	global $db;
  global $oo;
	global $ww;

	$roots = array(
		"Exhibition" => 15,
		"Exhibitions" => 15,
		"Film" => 17,
		"Films" => 17,
		"Talks & Learning" => 18,
		"Live" => 16
	);

	$root = $roots[$event["category"]];
	$spektrix_id = $event["spektrix_id"];
	$booking_url = "/book/$spektrix_id";

	if (!$event["on_sale_on_web"]) {
		$booking_url = '';
	}

	// does this exist?
	$sql = "SELECT objects.* FROM objects, wires WHERE objects.name2='$spektrix_id' AND wires.toid=objects.id AND objects.active=1 AND wires.active=1";
	$res = $db->query($sql)->fetch_assoc();

	if (sizeof($res) > 0) {
		// update dates
		$processed = array(
			"begin" => date($oo::MYSQL_DATE_FMT, strtotime($event['begin_date'])), // begin date
			"end" => date($oo::MYSQL_DATE_FMT, strtotime($event['end_date'])) // end date
		);

		foreach($processed as $key => $value)
		{
			if($value)
				$processed[$key] = "'".addslashes($value)."'";
			else
				$processed[$key] = "null";
		}

		$oo->update($res['id'], $processed);

		return false;
	} else {
		// create
		$processed = array(
			"name1" => '.' . $event["name"], // title - text
			"name2" => $spektrix_id, // ticketing id
			"country" => $booking_url, // booking url
			"begin" => date($oo::MYSQL_DATE_FMT, strtotime($event['begin_date'])), // begin date
			"end" => date($oo::MYSQL_DATE_FMT, strtotime($event['end_date'])), // end date
			"url" => slug($event["name"]) // slug!
		);

		// print_r($processed);

		foreach($processed as $key => $value)
		{
			if($value)
				$processed[$key] = "'".addslashes($value)."'";
			else
				$processed[$key] = "null";
		}

		$event_id = $oo->insert($processed);

		if ($event_id != 0)
			$ww->create_wire($root, $event_id);

		return true;
	}
}

// returns true if added or false ir updated
function addOrUpdateInstance($instance) {
	global $db;
	global $oo;

	$eventId = $instance["spektrix_id"];
	$instanceId = $instance["instance_id"];

	$sql = "SELECT event_id, event_instance_id FROM spektrix WHERE event_id='$eventId' AND event_instance_id='$instanceId'";

	$res = $db->query($sql)->fetch_assoc();
  if (sizeof($res) > 0) {
    // update
    $sql = "UPDATE spektrix SET ";
    $sql .= "venue='" . addslashes($instance["venue"]) . "', ";
    $sql .= "date_time='" . addslashes(date($oo::MYSQL_DATE_FMT, strtotime($instance["date"]))) . "', ";
    $sql .= "duration='" . addslashes($instance["duration"]) . "', ";
    $sql .= "date_modified='" . addslashes(date($oo::MYSQL_DATE_FMT, strtotime("now"))) . "' ";
    $sql .= "WHERE event_id='$eventId' AND event_instance_id='$instanceId'";

    $update = $db->query($sql);
    return false;
  } else {
    // insert
    $sql = "INSERT INTO spektrix (event_id, event_instance_id, venue, date_time, duration, date_modified, date_created) VALUES ";
    $sql .= "('" . addslashes($eventId) . "', ";
    $sql .= "'" . addslashes($instanceId) . "', ";
    $sql .= "'" . addslashes($instance["venue"]) . "', ";
    $sql .= "'" . addslashes(date($oo::MYSQL_DATE_FMT, strtotime($instance["date"]))) . "', ";
    $sql .= "'" . addslashes($instance["duration"]) . "', ";
    $sql .= "'" . addslashes(date($oo::MYSQL_DATE_FMT, strtotime("now"))) . "', ";
    $sql .= "'" . addslashes(date($oo::MYSQL_DATE_FMT, strtotime("now"))) . "') ";

    $insert = $db->query($sql);
    return true;
  }
}
?>
