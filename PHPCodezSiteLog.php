<?php
/**
* Plugin Name: PHPCodez Site Log
* Plugin URI: http://phpcodez.com/
* Description: A simple plugin to the sitelog (Both admin and client)
* Version: 0.1
* Author: Pramod T P
* Author URI: http://phpcodez.com/pramodtp
*/

class PHPCodezSiteLog
{
	
	function PHPCodezSiteLog(){
		register_activation_hook(__FILE__, array(&$this, 'phpcodezSiteLogInstall'));
		register_deactivation_hook(__FILE__, array(&$this, 'phpcodezSiteLogUninstall'));
		add_action('admin_menu', array(&$this, 'phpcodezSiteLogMenu'));
		add_action('plugins_loaded', array(&$this, 'phpcodezSiteLogCreate'));
		delete_option('gc_al_admin_log');
	}
	
	function phpcodezSiteLogInstall(){
		mysql_query('
			CREATE TABLE IF NOT EXISTS wp_pal_admin_log (
			log_id int(11) NOT NULL AUTO_INCREMENT,
			log_username varchar(255) NOT NULL,
			log_name varchar(255) NOT NULL,
			log_date varchar(25) NOT NULL,
			log_time varchar(25) NOT NULL,
			log_day varchar(25) NOT NULL,
			log_added_date datetime NOT NULL,
			log_page varchar(255) NOT NULL,
			log_area varchar(11) NOT NULL,
			log_ip varchar(25) NOT NULL,
			log_agent varchar(255) NOT NULL,
			PRIMARY KEY (`log_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
		');
		update_option('phpcodez_site_log_backup',  "1");
		update_option('phpcodez_site_log_install_year', date('Y'));
	}
	
	function phpcodezSiteLogUninstall(){ 
		if(!get_option("phpcodez_site_log_backup")) {
			mysql_query("DROP TABLE wp_pal_admin_log");	
			delete_option('phpcodez_site_log_backup'  );
			delete_option('phpcodez_site_log_clear');	
			delete_option('phpcodez_admin_log_csv_url');
			delete_option('phpcodez_admin_log_csv_path');
			delete_option('phpcodez_site_log_install_year');	
		}
	}
	
	function phpcodezSiteLogMenu(){
		add_menu_page('Site Log', 'Site Log', 8, 'PHPCodezSiteLogSetting', array(&$this,'PHPCodezSiteLogSetting'));
	}
	
	function PHPCodezSiteLogSetting(){
		include("PHPCodezSiteLogSetting.php");
	}
	
	function phpcodezSiteLogCreate(){
		
		$user = wp_get_current_user();
		
		if(strpos($this->getCurrentPageURL(),"wp-admin"))
			$logInfo['log_area']		=	"Admin";
		else
			$logInfo['log_area']		=	"Client";			
		
		if($user->user_login)
			$logInfo['log_username']	=	$user->user_login;	
		else
			$logInfo['log_username']	=	"Guest";	
		
		if($user->first_name or $user->last_name)	
			$logInfo['log_name']		=	$user->first_name.' '.$user->last_name;	
		
		$logInfo['log_page']		=	$this->getCurrentPageURL();	
		$logInfo['log_day']			=	date('D');
		$logInfo['log_date']		=	date('d/M/Y');
		$logInfo['log_time']		=	date('G:i:s');
		$logInfo['log_added_date']	=	"NOW()";
		$logInfo['log_ip']			=	$_SERVER['REMOTE_ADDR'];
		$logInfo['log_agent']		=	$_SERVER['HTTP_USER_AGENT'];
		$this->insertQry('wp_pal_admin_log',$logInfo);
		
	}
	
	function insertQry($table, $arFieldsValues){
		$fields	=	array_keys($arFieldsValues);
		$values	=	array_values($arFieldsValues);
		
		$formatedValues	=	array();
		foreach($values as $val){
			if(strcmp($val,"NOW()") == 0)
				$val	=	$val;
			else
				$val	=	"'".addslashes($val)."'";
		
			$formatedValues[]	=	$val;
		}
		
		$sql	=	"INSERT INTO ".$table." (";
		$sql	.=	implode(", ",$fields).") ";
		$sql	.=	"VALUES( ";
		$sql	.=	implode(", ",$formatedValues);
		$sql	.=	")";
		//echo $sql;exit;
		mysql_query($sql) or die(mysql_error());
		return mysql_insert_id(); //If the table contains autoincrement field
	}
	
	function getCurrentPageURL() {
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		
		return $pageURL;
	}

}

$pcalObj=new PHPCodezSiteLog();

?>