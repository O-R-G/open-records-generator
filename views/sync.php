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
			<form action="<? echo $admin_path; ?>sync" method="post">
				<span>Sync with Patronbase </span>
				<input name='action' type='hidden' value='sync'>
				<input name='submit' type='submit' value='Sync'>
			</form><?
		}
		else
		{
      $endpoint = 'https://uk.patronbase.com/_ICA/API/v1/Productions/Feed';
      $headers = 'X-PatronBase-Api-Key:d73e6ac69f9ad58a2bced324d0c7e8f56d85e0b8';

			$performanceAddCount = 0;
      $performanceUpdateCount = 0;

			$productionAddCount = 0;
			$productionUpdateCount = 0;

      // get json from patronbase
      $json = getJSON($endpoint, $headers, false);

      // iterate through json productions
      foreach ($json->productions as $production) {
        // make production
        $productionObject = array(
          "name" => $production->name,
					"category" => $production->category->name,
					"department" => $production->department,
          "pb_id" => $production->id,
					"booking_url" => $production->urls->bookonline,
          "prod_group" => $production->project,
          "begin_date" => $production->dates->from,
          "end_date" => $production->dates->to,
					"price_range" => $production->pricing->formatted
        );

        $production_added = add_or_update_production($productionObject);

				if ($production_added)
					$productionAddCount++;
				else
					$productionUpdateCount++;

        // iterate through a production's performance
        foreach ($production->performances->details as $performance) {
          // make performance
          $performanceObject = array(
            "production_id" => $production->id,
            "performance_id" => $performance->id,
            "date" => $performance->date,
            "duration" => $production->duration,
            "booking_url" => $performance->bookonlineurl,
            "venue" => $performance->venue,
            "venue_id" => $performance->venue_id,
            "status_code" => $performance->statuscode,
            "seats_count" => $performance->seatcount,
            "seats_left" => $performance->seatsleft
          );

          $performance_added = add_or_update_performance($performanceObject);

          if ($performance_added)
            $performanceAddCount++;
          else
            $performanceUpdateCount++;
        }
      }

      echo "Synced.<br /><br />Productions Added: $productionAddCount<br /><br />Performances Added: $performanceAddCount<br />Performances Updated: $performanceUpdateCount";
		}
		?></div>
	</div>
</div>

<?php

// gets json from patronbase endpoint
function getJSON($endpoint, $headers='', $convertToArray = false) {
    $arrContextOptions=array(
        "http"=>array(
            "header"=>$headers
        )
    );
    $data = @file_get_contents($endpoint, false, stream_context_create($arrContextOptions));
    $json = json_decode($data, $convertToArray);
    return $json;
}

// returns true if added or false ir updated
function add_or_update_production($production) {
	global $db;
  global $oo;
	global $ww;

	// TODO: Find out exactly how this gets mapped...
	if ($production["project"] == "ICA Live" || $production["project"] == "Art Night" || $production["project"] == "ICA Music") {
		$root = "Live";
	} else if ($production["category"] == "Films") {
		$root = "Films";
	} else {
		$root = "Talks & Learning";
	}

	$roots = array(
		"Films" => 17,
		"Talks & Learning" => 18,
		"Live" => 16
	);

	$pb_id = $production["pb_id"];
	$booking_url = "https://ica.web.patronbase.co.uk/tickets?ProdID=$pb_id";

	// does this exist?
	$sql = "SELECT name2 FROM objects WHERE name2='$pb_id' AND active=1";
	$res = $db->query($sql)->fetch_assoc();

	if (sizeof($res) > 0) {
		// update
		return false;
	} else {
		// create
		$processed = array(
			"name1" => '.' . $production["name"], // title - text
			"name2" => $pb_id, // ticketing id
			"country" => $booking_url, // booking url
			"phone" => $production["price_range"],  // price range
			"head" => $production["prod_group"], // production group
			"begin" => date($oo::MYSQL_DATE_FMT, strtotime($production['begin_date'])), // begin date
			"end" => date($oo::MYSQL_DATE_FMT, strtotime($production['end_date'])), // end date
			"url" => slug($production["name"]), // slug!
		);

		foreach($processed as $key => $value)
		{
			if($value)
				$processed[$key] = "'".addslashes($value)."'";
			else
				$processed[$key] = "null";
		}

		$event_id = $oo->insert($processed);

		if ($event_id != 0)
			$ww->create_wire($roots[$root], $event_id);

		return true;
	}
}

// returns true if added or false ir updated
function add_or_update_performance($performance) {
  global $db;
  global $oo;

  $productionID = $performance["production_id"];
  $performanceID = $performance["performance_id"];

	// use this because json feed uses older booking url
	$booking_url = "https://ica.web.patronbase.co.uk/tickets?ProdID=$productionID&PerfID=$performanceID";

  $sql = "SELECT production_id, performance_id FROM patronbase WHERE production_id='$productionID' AND performance_id='$performanceID'";

  $res = $db->query($sql)->fetch_assoc();
  if (sizeof($res) > 0) {
    // update
    $sql = "UPDATE patronbase SET ";
    $sql .= "venue='" . addslashes($performance["venue"]) . "', ";
    $sql .= "booking_url='" . addslashes($booking_url) . "', ";
    $sql .= "date_time='" . addslashes(date($oo::MYSQL_DATE_FMT, strtotime($performance["date"]))) . "', ";
    $sql .= "duration='" . addslashes($performance["duration"]) . "', ";
    $sql .= "status_code='" . addslashes($performance["status_code"]) . "', ";
    $sql .= "date_modified='" . addslashes(date($oo::MYSQL_DATE_FMT, strtotime("now"))) . "' ";
    $sql .= "WHERE production_id='$productionID' AND performance_id='$performanceID'";

    $update = $db->query($sql);
    return false;
  } else {
    // insert
    $sql = "INSERT INTO patronbase (production_id, performance_id, venue, booking_url, date_time, duration, status_code, date_modified, date_created) VALUES ";
    $sql .= "('" . addslashes($productionID) . "', ";
    $sql .= "'" . addslashes($performanceID) . "', ";
    $sql .= "'" . addslashes($performance["venue"]) . "', ";
    $sql .= "'" . addslashes($booking_url) . "', ";
    $sql .= "'" . addslashes(date($oo::MYSQL_DATE_FMT, strtotime($performance["date"]))) . "', ";
    $sql .= "'" . addslashes($performance["duration"]) . "', ";
    $sql .= "'" . addslashes($performance["status_code"]) . "', ";
    $sql .= "'" . addslashes(date($oo::MYSQL_DATE_FMT, strtotime("now"))) . "', ";
    $sql .= "'" . addslashes(date($oo::MYSQL_DATE_FMT, strtotime("now"))) . "') ";

    $insert = $db->query($sql);
    return true;
  }
}

?>
