<div id="body-container">
	<div id="body">
		<div id="self-container"><?php
		if($rr->action != "update")
		{
		?>
			<form action="<?php echo $admin_path; ?>settings" method="post">
				<?php
				foreach($org_settings as $setting_item) {
					$name = $setting_item['name'];
					$html = '<div class="sync-row"><label for="'.$name.'">' . $setting_item['display'] . ': </label>';
					if($setting_item['input_type'] == 'select')
					{
						$html .= '<select id="'.$name.'" name="'.$name.'">';
						foreach($setting_item['options'] as $key => $option) {
							$html .= $settings[$setting_item['name']] == $option['value'] ?  '<option value="' . $option['value'] . '" selected>' : '<option value="' . $option['value'] . '">';
							$html .= $option['display'] . '</option>';
						}
						$html .= '</select>';
					}
					$html .= '</div>';
					echo $html;
				}
				?>
				<br>
				<input name='action' type='hidden' value='update'>
				<input name='submit' type='submit' value='update settings'>
			</form><?php
		}
		else
		{	
			$rr_arr = get_object_vars($rr);
			foreach($org_settings as $org_setting)
			{
				
				$name = $org_setting['name'];
				$settings[$name] = $rr_arr[$name];
 			}
			$f = serialize($settings);
			file_put_contents($settings_file, $f);
			foreach($org_settings as $key => $org_setting)
			{
				$name = $org_setting['name'];
				$html = '<div class="sync-row"><span>' . $org_setting['display'] . ': '.$settings[$name].'</span><br></div>';
				echo $html;
			}
		}
		?></div>
	</div>
</div>