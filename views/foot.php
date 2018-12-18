<?
$generate_url = implode("/", $uu->urls);
$g = $host.$generate_url;
			?><div id="footer-container" class="flex-min">
				<footer class="centre">
						<? if ($view != 'logout'): ?>
							<a class="button" href="<? echo $admin_path; ?>info">INFO</a>
							<a class="button" href="<? echo $g; ?>" target="_blank">GENERATE</a>
							<a class="button" href="<? echo $admin_path; ?>settings">SETTINGS</a>
							<a class="button" href="<? echo $admin_path; ?>logout" style="float: right;">LOG OUT</a>
						<? endif; ?>
				</footer>
			</div>
		</div>
	</body>
</html><?
$db-> close();
?>
