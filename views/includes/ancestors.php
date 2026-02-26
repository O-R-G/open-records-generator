<?php 

function renderAncestors($ids){
    global $q;
    global $admin_path;
    global $display_id;
    global $oo;
    $output = '';
    $a_url = $admin_path."browse";
	for($i = 0; $i < count($ids)-1; $i++)
	{
		$a = $ids[$i];
		$ancestor = $oo->get($a);
		$a_url.= "/".$ancestor["url"];
        $id_span = $display_id ? '<span class="record-id">['.$a.']</span> ' : '';
        $output .= '<div class="ancestor">'.$id_span.'<a href="'.$a_url . $q.'">'.$ancestor["name1"].'</a></div>';
	}
    return $output;
}