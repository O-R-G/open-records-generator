<div id="body-container">
	<div id="body">
		<div id="self-container"><?
		if($rr->action != "update")
		{
			$rich_text_mode_options = array(
				array(
					'name' => 'Regular editor',
					'value'   => 'regular'
				),
				array(
					'name' => 'HTML editor',
					'value'   => 'html'
				)
			);
		?>
			<form action="<? echo $admin_path; ?>settings" method="post">
				<span>maximum # of uploads: </span>
				<select name="uploads"><?
					for($i = 5; $i <= 50; $i+= 5)
					{
						if($i == $max_uploads)
						{
						?><option value="<? echo $i; ?>" selected><? echo $i; ?></option><?
						}
						else
						{
						?><option value="<? echo $i; ?>"><? echo $i; ?></option><?
						}
					}
				?></select>
				<? if(isset($rich_text_mode_options)){ ?>
					<br>
					<label for = 'default_rich_text_field_mode'>default rich text fields mode: </label>
					<select id = 'default_rich_text_field_mode' name="default_rich_text_field_mode"><?
					foreach($rich_text_mode_options as $option)
					{
						$selected = $default_rich_text_field_mode == $option['value'] ? 'selected' : '';
						?><option value="<?= $option['value']; ?>" <?= $selected; ?>><?= $option['name']; ?></option><?
					}
					?></select><?
				} ?>
				<input name='action' type='hidden' value='update'>
				<br><br>
				<input name='submit' type='submit' value='update settings'>
			</form><?
		}
		else
		{
			$uploads = $rr->uploads;
			$default_rich_text_field_mode = $rr->default_rich_text_field_mode;
			if(!$settings)
				$settings = new ORG_Settings();
			$settings->num_uploads = $uploads;
			$settings->default_rich_text_field_mode = $default_rich_text_field_mode;
			$f = serialize($settings);
			file_put_contents($settings_file, $f);
			?><span>maximum number of uploads: <? echo $uploads; ?></span><br><span>default rich text fields mode: <? echo $default_rich_text_field_mode; ?></span><br><?
		}
		?></div>
	</div>
</div>