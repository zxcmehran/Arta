<?php 
#########################################
# Arta is created by Mehran Ahadi www.artaproject.com 
# config.php 
# This file contains settings for Arta {$version}
# Last Modified : {$time_formatted}
#########################################
if(!defined('ARTA_VALID')){die('No access');}
class ArtaConfig {
	var $site_name = '{$site_name}';
	var $homepage_title = '{$homepage_title}';
	var $description = '{$description}';
	var $keywords = '{$keywords}';
	var $gzip_output = '1';
	var $list_limit = '20';
	var $time_format = 'F d, Y H:i  ';
	var $time_offset = '{$time_offset}';
	var $cal_type = '{$cal_type}';
	var $debug = '0';
	var $debug_mode = 'echo';
	var $cache = '1';
	var $cache_lifetime = '1800';
	var $admin_alert = '1';
	var $admin_alert_lifetime = '604800';
	var $cron_log_lifetime = '604800';
	var $offline = '0';
	var $offline_msg = 'This website is temporary unavailable. Please try again later.';
	var $offline_pass = '';
	var $cookie_path = '/';
	var $cookie_domain = '';
	var $session_lifetime = '1800';
	var $session_type = 'db';
	var $db_host = '{$db_host}';
	var $db_user = '{$db_user}';
	var $db_pass = '{$db_pass}';
	var $db_name = '{$db_name}';
	var $db_prefix = '{$db_prefix}';
	var $db_type = '{$db_type}';
	var $secure_site = '0';
	var $secure_admin = '0';
	var $sef = '{$sef}';
	var $sef_rewrite = '{$sef_rewrite}';
	var $main_domain = '';
	var $mail_admin = '{$v_email}';
	var $mail_type = 'php';
	var $mail_smtp_host = '';
	var $mail_username = '';
	var $mail_password = '';
	var $mail_pop3_auth_host = '';
	var $ftp_enabled = '0';
	var $ftp_host = '';
	var $ftp_port = '21';
	var $ftp_user = '';
	var $ftp_pass = '';
	var $ftp_path = '';
	var $secret = '{$secret}';
	var $install_time = '{$time}';

	function __construct(){
		$this->time_offset=(string)$this->time_offset;
		$this->time_offset= substr($this->time_offset, 0, 1)=='-' ? $this->time_offset : '+'.$this->time_offset;
	}
}
?>