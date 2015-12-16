<?
$dbUptime = floor(((time() - $dbStart) / 3600) * 100) / 100;
/*
$sql = "SELECT DISTINCT ip FROM log";
$result = MYSQL_QUERY($sql);
$dbRequests = MYSQL_NUM_ROWS($result);
echo "UNIQUE ". $dbRequests. ". ";
*/
?>
			</td>
		</tr>
		<tr style="background-color: #CCCCCC;">
			<td style="padding: 16px 16px 16px 16px;">&nbsp;</td>
		</tr>
		<tr class="foot">
			<td>ACTIVITY 
				<img 
					src="<?php echo $dbAdmin; ?>MEDIA/org_activity.gif" 
					width="64" heigh="16" alt="ORG Activity" 
					style="background-color: #<?php echo $dbColor2;?>;" />
				&nbsp;UPTIME <? echo $dbUptime; ?>H. 
				<a href="<? echo $dbAdmin; ?>info.php">INFO</a>
				<a href="<? echo $dbAdmin; ?>settings.php">SETTINGS</a>
				<a href="<? echo $dbHost; ?>info.php" target="_blank">GENERATE &gt;</a>
			</td>
		</tr>
	</table>
		<!--
	myNameIs  =  Stewart Smith
	findMeAt  =  stewdio.org
	iRunWith  =  Command+N

	shoutOuts {

		abbiesmith.com
		august-yn.com
		andypressman.com
		apirat.net
		confess.cc
		daveidesign.com
		dobi.nu
		greypixel.com
		jpchirdon.net
		juliettecezzar.com
		listentotitles.com
		madeintheusb.com
		o-r-g.com
		projectprojects.com
		saidsew.com
		salierno.com
		skellaby.com
		tweedmag.com
	}
		-->
</body>
</html>