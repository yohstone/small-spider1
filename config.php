<?php
/**
* config.php 配置文件
*
* @version    v0.01
* @createtime 2019/07/10
* @updatetime 
* @author     yjl(steve stone)
* @copyright  Copyright (c) yjl(steve stone)
* 配置所需爬取网页及其相应的正则表达式等
*/

// 当前路径
define("BASE_PATH", str_replace("\\", "/", realpath(dirname(__FILE__))));

// 设置时区
date_default_timezone_set('PRC');


// urls数组，配置需要抓取的网站、解析网站内容的正则表达式、cookie等信息，正则表达式需要分析网站源码，自己配置。
// 若不需要某参数可以设置为0或''
$urls = array(
	// 百度学术
	'0' => array(
		'url'   			=> 'http://xueshu.baidu.com/s?wd=%s&pn=0&tn=SE_baiduxueshu_c1gjeupa&ie=utf-8&sort=sc_time&sc_f_para=sc_tasktype%%3D%%7BfirstSimpleSearch%%7D&sc_hit=1',
		'page_num' 	        => 0, 										    			           // 1.抓取的页数，0表示不需要该参数
		'table_reg'   		=> '/<div id="content_leftrs">(.*?)<\/div>[ |\t|\r|\n]*<div id="guide-step">/s',    // 2.匹配整个表格的正则表达式
		'row_reg'   		=> '/<div class="sc_content">(.*?)<div id="[\d]*"/s',				   // 3.匹配表格每一行的正则表达式
		'title_reg'     	=> '/<h3 class="t c_font">(.*?)<\/h3>/s',                   		   // 4.匹配标题的正则表达式
		'pubtime_reg'   	=> '/data-year="(.*?)"/s',										       // 5.匹配发表时间
		'type_reg'          => '/class="v_source" title="(.*?)"/s',  							   // 6.匹配类型/来源
		'link_reg'          => '/data-url="(.*?)"/s',											   // 7.匹配链接
		'cookie'            => '',                                                           	   // 8.配置固定cookie
		'reset_cookie'      => false,                                             				   // 9.是否需要根据不同的主机设置不同的cookie，即动态cookie，若为true，抓取前会先获取适用于当前主机的动态cookie，然后拼接上方的固定cookie生成全新的cookie。
		'get_cookie_url'    => '',     															   // 10. 用于获取动态cookie的url
		'web_name'          => '百度学术'        											       // 11. 爬取的网站名称
 
	),
	// 知网，cookie问题尚未解决
	'1' => array(
		'url'   			=> 'http://kns.cnki.net/kns/brief/brief.aspx?pagename=ASP.brief_default_result_aspx&isinEn=1&dbPrefix=SCDB&dbCatalog=%e4%b8%ad%e5%9b%bd%e5%ad%a6%e6%9c%af%e6%96%87%e7%8c%ae%e7%bd%91%e7%bb%9c%e5%87%ba%e7%89%88%e6%80%bb%e5%ba%93&ConfigFile=SCDBINDEX.xml&research=off&keyValue=protocol%20reverse&S=1&sorttype=',
		'page_num' 	        => 0, 										    					   // 1.抓取的页数，0表示不需要该参数
		'table_reg'   		=> '/<table class="GridTableContent"(.*?)<\/table>/s', 	    		   // 2.匹配整个表格的正则表达式
		'row_reg'   		=> '/<TR *bgcolor=(.*?)<\/TR>/s',        		    				   // 3.匹配表格每一行的正则表达式
		'title_reg'     	=> '/<a class="fz14"(.*?)<\/a>/s',                   				   // 4.匹配标题的正则表达式
		'pubtime_reg'   	=> '/<\/td>[ |\t|\r|\n]*<td align="center">(.*?)<\/td>/s',			   // 5.匹配发表时间
		'type_reg'          => '/<td align="center">(.*?)<\/td>[ |\t|\r|\n]*<td align="right">/s', // 6.匹配类型/来源
		'link_reg'          => '/<a class="fz14" href=[\'|"](.*?)[\'|"]/s',						   // 7.匹配链接
		'cookie'            => 'Ecp_notFirstLogin=G9DJlU; UM_distinctid=16a97a7ab4978-0023402695a22c-5a40201d-1aeaa0-16a97a7ab4a322; ASP.NET_SessionId=cxaot3whzr12utdppo5mwn3x; SID_kns=123107; SID_klogin=125143; SID_crrs=125133; SID_krsnew=125133;  KNS_SortType=; SID_kcms=124105; _pk_ref=%5B%22%22%2C%22%22%2C1562749988%2C%22http%3A%2F%2Fwww.cnki.net%2F%22%5D; _pk_ses=*; RsPerPage=20; Ecp_lout=1; LID=; IsLogin=',                                  // 8.配置固定cookie
		'reset_cookie'      => true,                                             				   // 9.是否需要根据不同的主机设置不同的cookie，即动态cookie，若为true，抓取前会先获取适用于当前主机的动态cookie，然后拼接上方的固定cookie生成全新的cookie。
		'get_cookie_url'    => 'http://kns.cnki.net/kns/brief/default_result.aspx',     		   // 10. 用于获取动态cookie的url
		'web_name'          => '知网'        													   // 11. 爬取的网站名称
 

	),
	
);


// 备用user agent，用于模拟浏览器访问
$user_agent_pools = [
    "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0",
    "Mozilla/5.0 (X11; U; Linux x86_64; zh-CN; rv:1.9.2.10) Gecko/20100922 Ubuntu/10.10 (maverick) Firefox/3.6.10",
    "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0",
    "Mozilla/5.0 (X11; U; Linux x86_64; zh-CN; rv:1.9.2.10) Gecko/20100922 Ubuntu/10.10 (maverick) Firefox/3.6.10",
    "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/534.57.2 (KHTML, like Gecko) Version/5.1.7",
    "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71",
    "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.64 Safari/537.11"
]


?>