<?php

function renderSelf($name, $url, $id=''){
    global $display_id;
    global $q;
    $id_span = $display_id ? '<span class="record-id">['.$id.']</span> ' : '';;
    // $display_name = $display_id ? '['.$id.'] ' . $name : $name;
    $output = '<div class="self">
			'.$id_span.'<a href="'.$url.$q.'">'.$name.'</a>
		</div>';

    return $output;
}