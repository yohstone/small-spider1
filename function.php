<?php
/**
* function.php 爬虫所使用到的各类函数
*
* @version    v0.01
* @createtime 2019/07/10
* @updatetime 
* @author     yjl(steve stone)
* @copyright  Copyright (c) yjl(steve stone)
* 教程请参考：https://steve.blog.csdn.net/article/details/95526888
*/


/* 爬取网站源码的容器函数，用于根据需要构造url、获取cookie等
 * $url_info   : 网页的配置信息
 * $keyword    : 需要抓取的关键字
 * $page       : 需要抓取的页数，若为0，表示url不需要设置爬取页码
 * return      : 返回$url对应的网站源码
 */
function get_html_container($url_info, $keyword, $page){

    // 构造url 
    if(!empty($keyword)){
        $url = sprintf($url_info['url'], urlencode($keyword) ); // 填入爬取的关键字
    }else{
        $url = $url_info['url'];
    }     
    
    // 设置爬取的页码，若需要的话
    if($page != 0){
        $page++;
        if($page > $url_info['page_num']){
            break;
        }
        $url = sprintf($url, $page);  // 填入页码
    }

    // 生成cookie，若需要的话
    $cookie = $url_info['cookie']; // 固定cookie
    if($url_info['reset_cookie']){
        $header = get_headers($url_info['get_cookie_url']);
        $header = implode(',', $header);    
        $cookie_reg = '/Set-Cookie: *(.*?),/s';
        preg_match_all($cookie_reg, $header, $cookie_res);  // 匹配当前主机的请求头部信息的set-cookie数据
        $cookie .= implode('; ', $cookie_res[1]);           // 将匹配结果与上面的cookie拼接成完整的cookie
    }

    //$referer =  'http://kns.cnki.net/kns/brief/default_result.aspx';

    // 开始爬取源码
    $html = get_html($url, $cookie);
    $log_info = $url." 爬取结束, 爬取结果为 ". round(strlen($html)/1024, 2) ."KB \r\n";
    write_log($log_info);   // 保存日志

    // - 若爬取失败，换种方式重新爬取，预留
    // if( strlen($html) < 5120 ) 
    // ......

     // 判断网页编码，若不是utf-8则转码
    if( !is_utf8($html) ){
        $html = iconv("GBK", "UTF-8", $html);
    }

    // 返回爬取结果
    return $html; 
    
}




/* 爬取网站的html源码
 * $url        : 网站链接
 * $cookie     : 请求头部中的cookie值，即header中的cookie值
 * $proxy      : 代理ip，默认为空（若爬取源码失败，可能是主机ip被封，此时可以使用已有的代理再次尝试获取）
 * $proxy_port : 代理端口号，默认为空
 * $referer    : 来源地址，有些网站需要有该参数才能爬取到源码
 * $gzip       : 是否使用gzip编码
 * return      : 返回$url对应的网站源码
 */
function get_html($url, $cookie='', $proxy='', $proxy_port='', $referer='', $gzip=false) {
    $ch = curl_init();
    // 设置选项，包括URL
    curl_setopt($ch, CURLOPT_URL, $url);
    //curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.3 Safari/537.36');
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.64 Safari/537.11');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);      // 允许页面跳转，获取重定向
    curl_setopt($ch, CURLOPT_HEADER, 0);              // 设置头文件的信息作为数据流输出
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);            // 60秒超时
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // https跳过检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // https跳过检查

    if($gzip) curl_setopt($ch, CURLOPT_ENCODING, "gzip"); // 编码格式

    if($cookie != '') {
        $coo = "Cookie:$cookie";
        $headers[] = $coo;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);  // 设置请求头部信息
    }
    if($referer != '') {
        curl_setopt($ch, CURLOPT_REFERER, $referer);
    }
    if($proxy != '' and $proxy_port != '') {
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
    }
    
    // 获取内容
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}


/* 解析指定网站源码的内容
 * $html     : 网页源码
 * $url_info : 源码网站相关信息，其中包含了需要匹配内容的正则表达式
 * return    : 返回网站内容解析结果
 */
function get_content($html, $url_info){
    // 匹配整个表格
    preg_match($url_info['table_reg'], $html, $main_res);
    if(empty($main_res)){
        $log_info = $url_info['web_name']. " 列表匹配失败!\r\n";
        write_log($log_info);
    }

    // 匹配表格中的每行
    preg_match_all($url_info['row_reg'], $main_res[0], $rows_res, PREG_SET_ORDER);
    if(empty($rows_res)){
        $log_info = $url_info['web_name']. " 单行匹配失败!\r\n";
        write_log($log_info);
    }

    // 遍历每行数据
    foreach ($rows_res as $row) {
        //print_r($row);
        // 匹配此行标题
        preg_match($url_info['title_reg'], $row[1], $title_res);
        if(empty($title_res)){
            $log_info = $url_info['web_name']. " 标题匹配失败!\r\n";
            write_log($log_info);
        }

        // 匹配此行数据发布时间
        preg_match($url_info['pubtime_reg'], $row[1], $pubtime_res);
        if(empty($pubtime_res)){
            $log_info = $url_info['web_name']. " 发布时间匹配失败!\r\n";
            write_log($log_info);
        }

        // 匹配此行数据类型
        preg_match($url_info['type_reg'], $row[1], $type_res);
        if(empty($type_res)){
            $log_info = $url_info['web_name']. " 数据类型匹配失败!\r\n";
            write_log($log_info);
        }
        // 匹配此行链接
        preg_match($url_info['link_reg'], $row[1], $link_res);
        if(empty($link_res)){
            $log_info = $url_info['web_name']. " 链接匹配失败!\r\n";
            write_log($log_info);
        }

        // 合并结果
        $content_res[] = array(
            'title'   => str_replace(array("\r\n", "\r", "\n", "  "), '', strip_tags($title_res[0])),   // 去除多余的空格、标签与换行符
            'pubtime' => strip_tags(trim($pubtime_res[1])),
            'type'    => str_replace(array("\r\n", "\r", "\n", "   "), '', strip_tags($type_res[1]) ),  // 去除多余的空格、标签与换行符
            'link'    => $link_res[1],                                                                  //"http://kns.cnki.net/KCMS/". substr($link_res[1], 5),    // 知网拼接成有效地址
            'web_name' => $url_info['web_name'],                                          // 爬取的网站名称，当爬取的网站多时可用来区分数据来源
        );
    }
    if(empty($content)){
        write_log("源码解析失败！\r\n"); // 保存日志
    }else{
        write_log("源码解析成功！\r\n"); // 保存日志
    }
    return $content_res;
}




/* 判断是否是utf-8编码
 * $word : 需要判断的文本数据或字符串数据
 * return ： 若是utf-8编码的数据，则返回true，否则返回false
 */

function is_utf8($word){
    return (
           preg_match("/^([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}/", $word) == true 
        || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){1}$/", $word) == true 
        || preg_match("/([".chr(228)."-".chr(233)."]{1}[".chr(128)."-".chr(191)."]{1}[".chr(128)."-".chr(191)."]{1}){2,}/", $word) == true
    );
}


/* 将日志数据写入文件中，记录下来
 * $log_info : 需要写入日志文件的数据
 */
function write_log($log_info){
    file_put_contents('./spider.log', date('Y-m-d H:i:s ').$log_info, FILE_APPEND);
}
?>