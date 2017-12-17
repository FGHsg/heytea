<?php
namespace app\common\utils;
use think\Cache;

/**
 * 微信常用api
 * @author liuwenwei
 * 
 */
class WxApi{
	/**
	 * 获取access_token
	 */
	public static function get_access_token(){
		//优先从缓存中读取access_token
		if(Cache::has('access_token')){
			return Cache::get('access_token');
		}
		
		$url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".config("APP_ID")."&secret=".config("APP_SECRET");
		$ch=curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$output = curl_exec($ch);
		curl_close($ch);
		
		if($output){
			$result = json_decode($output,true);
			Cache::set('access_token', $result['access_token'],7000);//缓存access_token
			return $result['access_token'];
		}
	}
	
	/**
	 * 回复Text格式xml
	 * @param string $from 要发送的用户名
	 * @param string $to 来自用户
	 * @param string $contentStr 回复的文本内容 
	 */
	public static function responseText($from, $to, $contentStr){
		$textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[text]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					<FuncFlag>0</FuncFlag>
					</xml>";
		$time = time();
		$resultStr = sprintf($textTpl, $from, $to, $time, $contentStr);
		return $resultStr;
	}

}