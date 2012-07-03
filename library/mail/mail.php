<?php
/**
 * Mail and SMTP Class
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
 
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaMail Class
 * You can use it to send easy and unicode mails.
 */
class ArtaMail{

	/**
	 * Mailing type
	 * 
	 * @var	string
	 */
	var $type = 'smtp';
	
	/**
	 * Constructor.
	 * Sets {@link	ArtaMail::$type} according {@link	ArtaConfig} mail settings.
	 */
	function __construct(){
		$config = ArtaLoader::Config();
		$this->type = $config->mail_type;
	}

	/**
	 * Sends mail(s).
	 * 
	 * @param	array	$to	You can pass string too. It will be converted to array.
	 * @param	string	$subject	Mail Subject
	 * @param	string	$message	Message in HTML Format
	 * @param	array	$from	From name in key 0 and from e-mail in key 1
	 * @return	bool
	 */
	function mail($to, $subject, $message, $from="")
	{
	/*	if(strpos('<',$message)===false || strpos('>',$message)===false){
			$message=nl2br($message);
		}*/
		
		if(is_array($to) == false){
			$to = array($to);
		}
		if(strlen($subject)==0){$subject='-';}
		switch($this->type){
			case 'smtp':
				return $this->smtpMail($to, $subject, $message, $from);
			break;
			case 'php':
				return $this->phpMail($to, $subject, $message, $from);
			break;
		}
	}

	/**
	 * Sends mail(s) using SMTP.
	 * 
	 * @param	array	$to	You can pass string too. It will be converted to array.
	 * @param	string	$subject	Mail Subject
	 * @param	string	$message	Message in HTML Format
	 * @param	array	$from	From name in key 0 and from e-mail in key 1
	 * @return	bool
	 */
	function smtpMail($to, $subject, $message, $from=""){
		$config= ArtaLoader::Config();
		ArtaLoader::Import('mail->smtp');
		$smtp=new ArtaMail_SMTP;
		$smtp->host_name=$config->mail_smtp_host;
		$smtp->host_port=25;
		$smtp->ssl=0;
		$smtp->localhost="localhost";
		$smtp->direct_delivery=0;
		$smtp->timeout=10;
		$smtp->data_timeout=5;
		$smtp->debug=0;
		$smtp->html_debug=0;
		$smtp->pop3_auth_host=$config->mail_pop3_auth_host;
		$smtp->user=$config->mail_username;
		$smtp->realm="";
		$smtp->password=$config->mail_password;
		$smtp->workstation="";
		$smtp->authentication_mechanism="";

		if($from == null){
			$from =array($config->mail_admin, $config->site_name);
		}
		if(!is_array($from)){
			$from=array($from, $config->site_name);
		}
		$charset = trans('_LANG_CHARSET');
		$dir = trans('_LANG_DIRECTION');
		if($dir=='ltr'){
			$align='left';
		}else{
			$align='right';
		}
		$message="<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">
<HTML>
<HEAD>
<TITLE>".htmlspecialchars($subject)."</TITLE>
<META http-equiv=\"Content-Type\" content=\"text/html; charset={$charset}\" />
<META content=\"Arta Content Management Framework\" name=\"GENERATOR\" />
<BASE href=\"".ArtaURL::getSiteURL()."\" />
</HEAD>
<BODY style=\"direction: {$dir}; text-align: {$align};\">
{$message}
</BODY>
</HTML>";
		$from_h = "=?UTF-8?B?".base64_encode($from[1]).'?= <'.$from[0].'>';
		$http_host = ArtaURL::getDomain();
		$subject = "=?UTF-8?B?".base64_encode($subject).'?=';
		$charset=strtoupper($charset);
		if($smtp->SendMessage(
		$from[0], $to, array(
			"From: $from_h",
			"Subject: $subject",
			"To: ".implode(';', $to),
			"Date: ".strftime("%a, %d %b %Y %H:%M:%S %Z"),
			"Message-ID: <". md5(uniqid(time()))."@$http_host>",
			"MIME-Version: 1.0",
			"Content-Type: text/html; charset=$charset",
			"Content-Transfer-Encoding: 8bit",
			"X-Priority: 3",
			"X-MSMail-Priority: Normal",
			"X-Mailer: Arta Content Management Framework",
		),	$message)){
			return true;
		}else{
			ArtaError::addAdminAlert('Mail Class', 'Sending E-Mail', 'Email not sent. To test that your Mail function works correctly, head to Website Diagnostics. You are already using SMTP Mode. This error maybe because invalid SMTP Connection parameters. Please correct them at System Configuration.');
			return false;
		}
	}

	/**
	 * Sends mail(s) using PHP mail function.
	 * 
	 * @param	array	$to	You can pass string too. It will be converted to array.
	 * @param	string	$subject	Mail Subject
	 * @param	string	$message	Message in HTML Format
	 * @param	array	$from	From name in key 0 and from e-mail in key 1
	 * @return	bool
	 */
	function phpMail($to, $subject, $message, $from=""){
		$config = ArtaLoader::Config();
		$charset = trans('_LANG_CHARSET');
		$dir = trans('_LANG_DIRECTION');
		if($dir=='ltr'){
			$align='left';
		}else{
			$align='right';
		}
		$message="<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">
<HTML>
<HEAD>
<TITLE>".htmlspecialchars($subject)."</TITLE>
<META http-equiv=\"Content-Type\" content=\"text/html; charset={$charset}\" />
<META content=\"Arta Content Management Framework\" name=\"GENERATOR\" />
<BASE href=\"".ArtaURL::getSiteURL()."\" />
</HEAD>
<BODY style=\"direction: {$dir}; text-align: {$align};\">
{$message}
</BODY>
</HTML>";
		if($from == null){
			$from =array($config->mail_admin, $config->site_name);
		}
		if(!is_array($from)){
			$from=array($from, $config->site_name);
		}
		$from_h = "=?UTF-8?B?".base64_encode($from[1]).'?= <'.$from[0].'>';
		$subject = "=?UTF-8?B?".base64_encode($subject).'?=';
		$charset=strtoupper($charset);
		$http_host = ArtaURL::getDomain();
		$headers = "From: $from_h\n";
		$headers .= "Subject: $subject\n";
		$headers .= "To: ".implode(';', $to)."\n";
		$headers .= "Date: ".strftime("%a, %d %b %Y %H:%M:%S %Z")."\n";
		$headers .= "Message-ID: <". md5(uniqid(time()))."@$http_host>\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: text/html; charset=$charset\n";
		$headers .= "Content-Transfer-Encoding: 8bit\n";
		$headers .= "X-Priority: 3\n";
		$headers .= "X-MSMail-Priority: Normal\n";
		$headers .= "X-Mailer: Arta Content Management Framework\n";

		// For some reason sendmail/qmail doesn't like \r\n
		$sendmail = @ini_get('sendmail_path');
		if($sendmail)
		{
			$headers = preg_replace("#(\r\n|\r|\n)#s", "\n", $headers);
			$message = preg_replace("#(\r\n|\r|\n)#s", "\n", $message);
		}
		else
		{
			$headers = preg_replace("#(\r\n|\r|\n)#s", "\r\n", $headers);
			$message = preg_replace("#(\r\n|\r|\n)#s", "\r\n", $message);
		}
		$to = implode('; ',$to);
		$message = wordwrap($message, 70);
		if(mail($to, $subject, $message, $headers)){
			return true;
		}else{
			ArtaError::addAdminAlert('Mail Class', 'Sending E-Mail', 'Email not sent. To test that your Mail function works correctly, head to Website Diagnostics. It\'s recommended to use SMTP mail delivering service.');
			return false;
		}
	}



}
?>