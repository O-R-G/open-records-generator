<?
$generate_url = implode("/", $uu->urls);
$g = $host.$generate_url . $q;
			?><div id="footer-container" class="flex-min">
				<footer class="centre">
					<? if ($view != "logout"): ?>
						<a class="button" href="<? echo $admin_path; ?>info<?php echo $q; ?>">INFO</a>
						<a class="button" href="<? echo $g; ?>" target="_blank">GENERATE</a>
						<?php if ($user != 'guest'): ?>
							<a class="button" href="<? echo $admin_path; ?>settings<?php echo $q; ?>">SETTINGS</a>
						<?php endif; ?>
						<?php if ($syncName): ?>
							<a class="button" href="<? echo $admin_path; ?>sync<?php echo $q; ?>">SYNC</a>
						<?php endif; ?>
						<a class="button" href="<? echo $admin_path; ?>logout<?php echo $q; ?>" style="float: right;">LOG OUT</a>
					<? endif; ?>
				</footer>
			</div>
		</div>
	</body>
</html><?
$db-> close();
?>
