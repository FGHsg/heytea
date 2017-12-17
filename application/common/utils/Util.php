<?php
namespace app\common\utils;

/**
 * 工具类
 * @author liuwenwei
 *
 */
class Util{
	/**
	 * 生成随机字符串
	 * @param integer $length 生成字符串位数
	 * @return string
	 */
	public static function getRandStr($length){
		$char = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$str = '';
		for($i=0; $i < $length; $i++){
			$str = $str.$char[mt_rand(0,strlen($char)-1)];
		}
		return $str;
	}
	
	/**
	 * 生成随机数字
	 * @param integer $length 生成数字的位数
	 * @return string
	 */
	public static function getRandNum($length){
		$str = '';
		for($i=0; $i < $length; $i++){
			$str = $str.mt_rand(0, 9);
		}
		return $str;
	}
	
	/**
	 * 判断是否是微信内置浏览器
	 * @return boolean
	 */
	public static function isWeixin(){
		if (strpos($_SERVER['HTTP_USER_AGENT'],'MicroMessenger') !== false ) {
			return true;
		}
		return false;
	}
	
	/**
	 * 过滤emoji表情
	 * @param string $str 待过滤字符串
	 * @return string
	 */
	public static function filterEmoji($str){
		$str = preg_replace_callback('/./u',function(array $match){
			return strlen($match[0]) >= 4 ? '' : $match[0];
		},$str);
		
		return $str;
	}
}