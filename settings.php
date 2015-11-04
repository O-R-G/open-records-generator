<?
require_once("GLOBAL/head.php");
// require_once("GLOBAL/settings.inc");
// 
// $settings_file = "GLOBAL/settings.store";
// if(file_exists($settings_file))
// {
// 	$f = file_get_contents($settings_file);
// 	$settings = unserialize($f);
// 	$u = $settings->num_uploads;
// }

if($action != "update")
{
?>
<div>
	<form action="settings.php" method="post">
		<input name='action' type='hidden' value='update'>
		<span>maximum # of uploads: </span>
		<select name="uploads"><?
			for($i = 5; $i <= 50; $i+= 5)
			{
				if($i == $uploadsMax)
				{
				?><option value="<? echo $i; ?>" selected><? echo $i; ?></option><?
				}
				else
				{
				?><option value="<? echo $i; ?>"><? echo $i; ?></option><?
				}
			}
		?></select>
		<input name='submit' type='submit' value='update settings'>
</div>
<?
}
else
{
	$uploads = $_REQUEST['uploads'];
	if(!$settings)
		$settings = new ORG_Settings();
	$settings->num_uploads = $uploads;
	$f = serialize($settings);
	file_put_contents($settings_file, $f);
	?><span>maximum number of uploads: <? echo $uploads; ?></span><?
}
require_once("GLOBAL/foot.php");
?>