<?
// have to use root file path because of something called
// fopen wrappers? 
// see 'notes' on this page: 
// http://php.net/manual/en/function.file-get-contents.php
$license_file = $admin_root."static/gnu.txt";
$license = file_get_contents($license_file);
?><div id="body-container">
	<div id="body">
		<div class="self-container">
			<p>
				Open Records Generator<br>
				Version 2.9.5<br>
				7 August 2014<br>
				<a href="http://www.o-r-g.com/" target="_blank">O R G inc.</a>
			</p>
			<div class="license-container">
				<textarea class="large"><? echo $license; ?></textarea>
			</div>
			<div>
				<a href="<? echo $js_back; ?>">&lt; RETURN</a>
			</div>
		</div>
	</div>
</div>