<table>
	<tr>
		<td><h2>PHPCodez Admin Log </h2></td>
	</tr>
</table>		
<?php
	extract($_POST);
	
	if (isset($_POST['cp_save'])) {
		update_option('phpcodez_site_log_backup',  $_POST['phpcodez_site_log_backup']);
		update_option('phpcodez_site_log_clear', $_POST['phpcodez_site_log_clear']);
	}	
	
	if(isset($clear)) mysql_query("TRUNCATE TABLE  wp_pal_admin_log");	
	
	if($month_date)	$logCondition	.="AND log_date LIKE '%$month_date/%'";
	if($log_month)
		$logCondition	.="AND log_date LIKE '%/$log_month/%'";
	else
		$logCondition	.="AND log_date LIKE '%/".date('M')."/%'";
		
	
	if($log_year)	
		$logCondition	.="AND log_date LIKE '%$log_year%'";
	else
		$logCondition	.="AND log_date LIKE '%".date('Y')."%'";
			
	if($log_area_field)	$logCondition	.="AND log_area LIKE '%$log_area_field%'";
	
	$totalLog=0;
	$csvQry	=	"SELECT * FROM wp_pal_admin_log WHERE 1 $logCondition";
	$logQry	=	mysql_query("SELECT * FROM wp_pal_admin_log WHERE 1 $logCondition");
	while($logData=mysql_fetch_assoc($logQry)){ $totalLog++;
		extract($logData);
		$adminLog	="(".$log_username.",".$log_name.") On ".$log_day.' '.$log_date.' @ '.$log_time." ==>".$log_page." "."\n From( ".$log_ip." )Using (".$log_agent.") \n\n" . $adminLog;

	}
	
	$adminLog	= empty($adminLog)?"Log is empty":$adminLog;
	
	if(isset($csv)){
		if(!$log_area_field)	$log_area_field="Site"; 
		$newFileName	=	str_replace(" ","-",get_bloginfo('site_name'))."-$log_area_field-log-".date('-Y-M-d-D-H-i-s.').".csv";
		
		$filename = ABSPATH."/wp-content/plugins/phpcodez-site-log/".$newFileName;
		
		if(file_exists(get_option('phpcodez_admin_log_csv_path'))) unlink(get_option('phpcodez_admin_log_csv_path')) or die("Could not delete");
		
		$fileURL = get_bloginfo('url')."/wp-content/plugins/phpcodez-site-log/".$newFileName;
		
		$fp = fopen($filename, 'w') or die ('file cant be opened . Make sure that the plugin folder has proper permission ');
		
		$fieldsQry = mysql_query("SHOW COLUMNS FROM wp_pal_admin_log");
		
		while ($fields = mysql_fetch_assoc($fieldsQry)) {
			$fieldNames[] = $fields['Field'];
		}
		
		fputcsv($fp, $fieldNames);
		
		$csvQry = mysql_query($csvQry);
		while($csvInfo=mysql_fetch_assoc($csvQry)){
			fputcsv($fp, $csvInfo);$i++;
		}
		system("chmod 777 $filename");
		fclose($fp);
		update_option("phpcodez_admin_log_csv_url",$fileURL);
		update_option("phpcodez_admin_log_csv_path",$filename);
	}
?>

<form action="" method="post">
	<table width="910">
		<tr>
			<td width="205"><strong>Total Log :	<?php echo $totalLog; ?></strong></td>
			<td width="217" align="right">
				<select name="month_date" onChange="this.form.submit()">
					<option value="">Date </option>
					<?php for($day=1;$day<=31;$day++) { ?>
						<option value="<?php echo strlen($day)==1?"0".$day:$day; ?>"  <?php if($month_date==$day) echo 'selected="selected"' ?> ><?php echo strlen($day)==1?"0".$day:$day; ?></option>
					<?php } ?>	
				</select>
				<select name="log_month" onChange="this.form.submit()">
					<option value="">Month</option>
					<option value="Jan"  <?php if($log_month=='Jan') echo 'selected="selected"' ?> >January</option>
					<option value="Feb"  <?php if($log_month=='Feb') echo 'selected="selected"' ?> >February</option>
					<option value="Mar"  <?php if($log_month=='Mar') echo 'selected="selected"' ?> >March</option>
					<option value="Apr"  <?php if($log_month=='Apr') echo 'selected="selected"' ?> >April</option>
					<option value="May"  <?php if($log_month=='May') echo 'selected="selected"' ?> >May</option>
					<option value="Jun"  <?php if($log_month=='Jun') echo 'selected="selected"' ?> >June</option>
					<option value="Jul"  <?php if($log_month=='Jul') echo 'selected="selected"' ?> >July</option>
					<option value="Aug"  <?php if($log_month=='Aug') echo 'selected="selected"' ?> >August</option>
					<option value="Sep"  <?php if($log_month=='Sep') echo 'selected="selected"' ?> >September</option>
					<option value="Oct"  <?php if($log_month=='Oct') echo 'selected="selected"' ?> >October</option>
					<option value="Nov"  <?php if($log_month=='Nov') echo 'selected="selected"' ?> >November</option>
					<option value="Dec"  <?php if($log_month=='Dec') echo 'selected="selected"' ?> >December</option>
				</select>
				<select name="log_year" onChange="this.form.submit()">
					<option value="">Year</option>
					<?php for($limit=$year=date('Y');$year>=get_option('phpcodez_site_log_install_year');$year--) { ?>
						<option value="<?php echo $year ?>"  <?php if($log_year==$year) echo 'selected="selected"' ?> ><?php echo $year ?></option>
					<?php } ?>	
				</select>
		  </td>
	    <td width="62" align="right">
				<select name="log_area_field" onChange="this.form.submit()">
					<option value="">Area</option>
					<option value="Admin"  <?php if($log_area_field=='Admin') echo 'selected="selected"' ?> >Admin</option>
					<option value="Client" <?php if($log_area_field=='Client') echo 'selected="selected"' ?> >Client</option>
				</select>
		  </td>
	    <td width="406" align="right">
				<input type="submit" value="Generate CSV" name="csv">
				<?php if(file_exists(get_option("phpcodez_admin_log_csv_path"))){ ?>
					<input type="button" onclick="confirm('Make Sure that You Have Generated the Latest CSV');window.location='<?php echo get_option("phpcodez_admin_log_csv_url"); ?>'" value="Download CSV" name="csv">
				<?php } ?>
		  		<?php if(get_option("phpcodez_site_log_clear")) { ?>
					<input onclick="javascript:confirm('Do you want to delete the log?');" type="submit" value="Clear Admin Log" name="clear">
				<?php } ?>	
		  </td>
		</tr>
  </table>	
</form>	

<table width="90%">
	<tr>
		<td width="573" colspan="2">
			<textarea name="PHPCodezAdminLog" style="width:90%; height:auto; min-height:500px;" type='textarea' readonly><?php echo $adminLog; ?></textarea>
	  </td>
	</tr>
</table>
<table>
	<tr>
		<td><h2>Settings</h2></td>
	</tr>
</table>

<form method="post" action="">
<table>
	<tr>
		<td width="176">Keep Backup</td>
		<td width="786">
			<input name="phpcodez_site_log_backup"  type="checkbox" value="1"  <?php if(get_option("phpcodez_site_log_backup")) echo 'checked="checked"'; ?> /><em>(If this option is enabled , the log details will not get deleted when you deactivate the plugin)</em>
	  </td>	
	</tr>
	<tr>
		<td width="176">Enable Clear Log Option</td>
		<td width="786">
			<input name="phpcodez_site_log_clear"  type="checkbox" value="1"  <?php if(get_option("phpcodez_site_log_clear")) echo 'checked="checked"'; ?> />
			<em>(If this option is enabled , clear option will be available so that admin can delete the data)</em>
	  </td>	
	</tr>
</table>
	<div style="float:left;padding-top:15px;">
		Are you done? Then<input type="submit" value="Save Changes &raquo;" name="cp_save" class="dochanges" />
	</div>	
</form>	

