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

      // get json from patronbase
      $json = getJSON($endpoint, $headers, false);

      // iterate through json productions
      foreach ($json->productions as $production) {
        // make production
        $productionObject = array(
          "name" => $production->name,
          "pb_id" => $production->id,
          "prod_group" => $production->project,
          "begin_date" => $production->dates->from,
          "end_date" => $production->dates->to
        );

        add_or_update_production($productionObject);

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

          $added = add_or_update_performance($performanceObject);

          if ($added)
            $performanceAddCount++;
          else
            $performanceUpdateCount++;
        }
      }

      echo "Synced.<br /><br />Performances Added: $performanceAddCount<br />Performances Updated: $performanceUpdateCount";
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


function add_or_update_production($production) {
  // do nothing for now..

}

// returns true if added or false ir updated
function add_or_update_performance($performance) {
  global $db;
  global $oo;

  $productionID = $performance["production_id"];
  $performanceID = $performance["performance_id"];
  $sql = "SELECT production_id, performance_id FROM patronbase WHERE production_id='$productionID' AND performance_id='$performanceID'";

  $res = $db->query($sql)->fetch_assoc();
  if (sizeof($res) > 0) {
    // update
    $sql = "UPDATE patronbase SET ";
    $sql .= "venue='" . addslashes($performance["venue"]) . "', ";
    $sql .= "booking_url='" . addslashes($performance["booking_url"]) . "', ";
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
    $sql .= "'" . addslashes($performance["booking_url"]) . "', ";
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
