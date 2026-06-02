<?php
function findExistingAction($schedule, $id_to_find){
    $existing_action = array_filter($schedule, function($action) use ($id_to_find) {
        return $action['processed'] === false && $action['record-id'] == $id_to_find;
    });
    return count($existing_action) ? $existing_action[0] : null;
}
function removeAction($schedule, $id_to_remove){
    foreach($schedule as $key => &$s) {
        if($s['id'] == $id_to_remove) {
            unset($schedule[$key]);
            break;
        }
    }
    unset($s);
    return array_values($schedule);
}
function addAction($schedule, $record_id, $params){
    $action_template = array(
        'id' => count($schedule) ? intval( $schedule[0]['id'] ) + 1 : 0,
        'record-id' => intval($record_id),
        'action' => '',
        'datetime' => '',
        'record-to-replace' => '',
        'processed' => false
    );
    // $new_action = [...$action_template, ...$params];
    $new_action = array_merge($action_template, $params);
    array_unshift($schedule, $new_action);
    return $schedule;
}
function updateAction($schedule, $id_to_update, $params){
    foreach($schedule as $key => &$s) {
        if($s['id'] == $id_to_update) {
            // $s = [...$s, ...$params];
            $s = array_merge($s, $params);
            break;
        }
    }
    unset($s);
    return array_values($schedule);
}
function writeSchedule($schedule, $path_to_schedule){
    file_put_contents($path_to_schedule, "<?php \n \$schedule = " . var_export($schedule, true) . ';' );
    if (function_exists('opcache_invalidate')) {
        opcache_invalidate($path_to_schedule, true);
    }
}
function publishRecord($id){
    $db = db_connect('admin');
    $sql = "UPDATE objects SET name1 = (
        CASE
            WHEN SUBSTRING(name1, 1, 1) = '.' THEN SUBSTRING(name1, 2)
            ELSE name1
        END
    ) WHERE id = $id";
    $db->query($sql);
}
function swapRecords($id, $id_to_replace){
    global $oo;
    $db = db_connect('admin');

    /* swap url and name1 of the records */
    $sql_get_record_to_replace = "SELECT `name1`, `url` FROM objects WHERE id = $id_to_replace";
    $record_to_replace = $db->query($sql_get_record_to_replace)->fetch_assoc();
    $sql_update_new_record = "UPDATE `objects` SET `url` = '$record_to_replace[url]', `name1` = '$record_to_replace[name1]' WHERE id = $id";
    $db->query($sql_update_new_record);

    $siblings = $oo->siblings($id_to_replace);
    $s_urls = array();
    foreach($siblings as $s_id)
        $s_urls[] = $oo->get($s_id)['url'];

    $new_url = $record_to_replace['url'] . "-keep";
    $urlIsValid = validate_url($new_url, $s_urls);
    if( !$urlIsValid )
        $new_url = valid_url($new_url, intval($id_to_replace), $s_urls);

    $sql_update_record_to_replace = "UPDATE `objects` SET `name1` = '.$record_to_replace[name1] (keep)', `url` = '$new_url' WHERE id = $id_to_replace";
    $db->query($sql_update_record_to_replace);

    /* 
        create wires rows to link the children and parents of the original record to the new one 
        also exclude any child/parent is already linked to the new record. 
    */
    $inserts = [];
    $sql_get_children = "SELECT w.toid 
        FROM wires w
        WHERE 
            w.active = 1 AND 
            w.fromid = $id_to_replace AND 
            NOT EXISTS(SELECT 1 FROM wires w2 WHERE w2.fromid = $id AND w2.toid = w.toid AND w2.active = 1)";
    $result_get_children = $db->query($sql_get_children);

    while($obj = $result_get_children->fetch_assoc()) {
        $inserts[] = "($id, $obj[toid])";
    }
    $sql_get_parents = "SELECT w.fromid 
        FROM wires w 
        WHERE 
            w.active = 1 AND 
            w.toid = $id_to_replace AND
            NOT EXISTS(SELECT 1 FROM wires w2 WHERE w2.toid = $id AND w2.fromid = w.fromid AND w2.active = 1)";
    $result_get_parents = $db->query($sql_get_parents);
    while($obj = $result_get_parents->fetch_assoc()) {
        $inserts[] = "($obj[fromid], $id)";
    }
    if(empty($inserts)) return;
    $inserts_str = implode(',', $inserts);
    $sql_insert_wires = "INSERT INTO wires (`fromid`, `toid`) VALUES $inserts_str";

    $db->query($sql_insert_wires);
}
function handleSchedule($path_to_schedule){
    require_once($path_to_schedule);
    $now = time();
    $updated_schedule = $schedule;
    $scheduleUpdated = false;
    foreach($updated_schedule as &$action) {
        if(!$action['processed'] && strtotime($action['datetime']) <= $now) {
            if($action['action'] === 'schedule') {
                publishRecord($action['record-id']);
            } else if($action['action'] === 'schedule-and-replace') {
                publishRecord($action['record-id']);
                swapRecords($action['record-id'], $action['record-to-replace']);
            }
            $action['processed'] = true;
            if(!$scheduleUpdated) $scheduleUpdated = true;
        }
    }
    unset($action);
    if($scheduleUpdated)
        writeSchedule($updated_schedule, $path_to_schedule);
}