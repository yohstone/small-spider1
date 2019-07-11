<?php

/**
* mail.class.php 邮件类，用于创建并发送邮件
*
* @version    v0.01
* @createtime 2019/07/03
* @updatetime 
* @author     yjl(steve stone)
* @copyright  Copyright (c) yjl(steve stone)
* 教程请参考：https://steve.blog.csdn.net/article/details/95526888
*/


class Mail{
	// SMTP相关参数配置。
	// SMTP的配置由邮箱服务商提供。一般设置outlook收邮件填写的那些东西就是了。
	private $mysmtp_server      = 'smtp.126.com';
	private $mysmtp_port        = 25;
	private $mysmtp_auth        = true;
	private $mysmtp_account     = 'steve_stone@126.com';// 邮箱账号
	private $mysmtp_pass        = 'xxxxxxxxxx';   		// 邮箱授权密码
	private $mailfrom    		= 'steve_stone@126.com';
	private $mailto         	= 'stone_movies@126.com';
	private $mailfrom_name   	= '新论文提醒';
	//SMTP配置结束*************

	function sendMail($mail_content){
		
		
		// 定时发送邮件
		$mail_subject    = '论文更新提醒 '.substr(time(), 6);	    //邮件标题
		$content = '请注意：'."<br/>".$mail_content;
		$time = time();

		$mail_body       = $content;   //邮件内容
		$mail_type       = 'HTML';

		// 创建SMTP对象
		$mysmtp = new smtp($this->mysmtp_server, $this->mysmtp_port, $this->mysmtp_auth, 
			                $this->mysmtp_account, $this->mysmtp_pass, $this->mailfrom);
		
		//开始发送
		$sent = $mysmtp->sendmail($this->mailto, $this->mailfrom, $this->mailfrom_name, 
			                       $mail_subject, $mail_body, $mail_type);
		if($sent === TRUE){		//发送成功
			return "Sent success!\r\n";
		}else{
			return 'Sent failed!'. $mysmtp->logs. "\r\n";	
		}
		
	}
}


?>
