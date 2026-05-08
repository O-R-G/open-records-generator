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
    $new_action = [...$action_template, ...$params];
    array_unshift($schedule, $new_action);
    var_dump($schedule);
    return $schedule;
}
function updateAction($schedule, $id_to_update, $params){
    foreach($schedule as $key => &$s) {
        if($s['id'] == $id_to_update) {
            $s = [...$s, ...$params];
            break;
        }
    }
    unset($s);
    return array_values($schedule);
}