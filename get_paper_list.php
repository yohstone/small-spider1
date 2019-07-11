<?php
/**
* get_paper_list.php 爬虫运行文件，
*
* @version    v0.01
* @createtime 2019/07/10
* @updatetime 
* @author     yjl(steve stone)
* @copyright  Copyright (c) yjl(steve stone)
* 循环爬取config文件中，urls数组里配置好的网页并解析内容
* 默认设置为1天爬取一次，查看页面数据是否更新
*/


require_once('./config.php');
require_once('./function.php');
require_once('./smtp.class.php');
require_once('./mail.class.php');

// 设置爬取关键字
$keyword = 'protocol reverse';

while(1){
	// - 遍历$urls中的数据，依次抓取
	for($i=0; $i < count($urls); $i++){

		// 爬取多页时使用，爬取页数设为0时，此for循环只执行一次
    	for($page=0; $page <= $urls[$i]['page_num']; ){

			$is_new = false;   // 是否有新数据标志位

			// --- 获取源码
			$html = get_html_container($urls[$i], $keyword, $page);

			// --- 将源码保存备用
			file_put_contents("./$i.html", $html);

			// --- 解析源码，得到所需内容
			$new_content_list = get_content($html, $urls[$i]);

			// --- 获取数据库文件中已存在的数据
			$db_file = "./newest_paper_$i.json";
			if(file_exists($db_file)){
				mkdir($db_file);
				$db_content_json = '';
			}else{
				$db_content_json = file_get_contents();
			}
			
			// --- 遍历新解析出的数据内容，查看是否有新数据
			foreach ($new_content_list as $paper) {
				// ---- 使用名称的唯一性判断是否是新数据
				if(!strstr($db_content_json, $paper['title'])){ // 若是新数据
					$mail_content  = "发现新Paper:<br/>\r\n".
									 "名称： {$paper['title']} <br/>\r\n".
									 "发布时间：{$paper['pubtime']} <br/>\r\n".
									 "来源/类型： {$paper['type']} <br/>\r\n".
									 "链接： {$paper['link']} <br/>\r\n".
									 "来自： {$paper['web_name']} <br/>\r\n";

					// ---- 发送提醒邮件
					$mail = new mail();
					$res  = $mail->sendMail($mail_content);
					write_log($mail_content. $res);   // 保存日志
					$is_new = true;
				}
			}

			// --- 若是新数据或者是第一次运行，则更新数据库文件
			$new_content_json = json_encode($new_content_list);
			if( $is_new || empty($db_content_json)){
				file_put_contents("./newest_paper_$i.json", $new_content_json);
				write_log("更新数据库文件\r\n"); 			 // 保存日志
			}else{
				write_log("尚未发现新论文，数据依旧\r\n"); // 保存日志
			}
			
		}
	}

	// - 间隔一天爬一次
	sleep(24*3600); 
}



?>