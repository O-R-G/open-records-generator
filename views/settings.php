<div id="body-container">
	<div id="body">
		<div id="self-container"><?
		if($rr->action != "update")
		{
			$default_editor_mode_options = array(
				array(
					'name' => 'Rich text editor',
					'value'   => 'rich text'
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
				<? if(isset($default_editor_mode_options)){ ?>
					<br>
					<label for = 'default_editor_mode'>default editor mode: </label>
					<select id = 'default_editor_mode' name="default_editor_mode"><?
					foreach($default_editor_mode_options as $option)
					{
						$selected = $default_editor_mode == $option['value'] ? 'selected' : '';
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
			$default_editor_mode = $rr->default_editor_mode;
			if(!$settings)
				$settings = new ORG_Settings();
			$settings->num_uploads = $uploads;
			$settings->default_editor_mode = $default_editor_mode;
			$f = serialize($settings);
			file_put_contents($settings_file, $f);
			?><span>maximum number of uploads: <? echo $uploads; ?></span><br><span>default rich text fields mode: <? echo $default_editor_mode; ?></span><br><?
		}
		?></div>
	</div>
</div>