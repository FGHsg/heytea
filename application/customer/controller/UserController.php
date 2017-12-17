<?php

namespace app\customer\controller;


use app\common\model\Member;
use app\common\model\User;
use app\common\utils\BarCodeUtil;
use app\common\utils\HttpRequest;
use app\common\utils\WxApi;
use think\Config;
use think\Controller;
use think\Collection;
use think\Db;
use think\Exception;
use think\Request;
use think\Model;

class UserController extends Controller{

    /**
     * 获取access_token和openid
     * @param $code
     * @return \app\common\utils\error|array
     */
    public function getAccessTokenAndOpenid($code){
        $APPID = config("APP_ID");
        $APPSECRET = config("APP_SECRET");
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$APPID.'&secret='.$APPSECRET.'&code='.$code.'&grant_type=authorization_code';
        $response = HttpRequest::curl_get($url);
        return $response;
    }

    /**
     * 用户登录保存信息
     * @param $code
     * @return \think\response\Json
     */
    public function login(){
        $code = input('code');
        if (!is_null($code)&&strlen($code)!=0){
            $info = $this->getAccessTokenAndOpenid($code);
            $accessToken = $info['access_token'];
            $openid = $info['openid'];
            $errmsg = $info['errmsg'];
            if ($accessToken==null){
                $resultSet['errorCode'] = $errmsg;
                return $resultSet;
            }
            $getUserInfoUrl = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$accessToken.'&openid='.$openid.'&lang=zh_CN';
            $response = HttpRequest::curl_get($getUserInfoUrl);
            $u_avatar = $response['headimgurl'];
            $u_nickname = $response['nickname'];
            $u_gender = $response['sex'];   //1-男，2-女，3-未知
            $resultSet['userInfo'] = $response;
            $resultSet['info'] = $info;
            if (!is_null($response['openid'])&&strlen($response['openid'])!=0){
                $resultSet['m_id'] = $this->createUser($openid,$u_avatar,$u_nickname,$u_gender)['m_id'];
                $resultSet['errorCode'] = 0;
            }else{
                $resultSet['errorCode'] = $errmsg;
            }
        }else{
            $resultSet['errorCode'] = 1001;
        }

        return json($resultSet);
    }


    /**
     * 保存或创建用户信息
     * @param $u_openid
     * @param $u_avatar
     * @param $u_nickname
     * @param $u_gender
     * @return \think\response\Json
     */
    public function createUser($u_openid, $u_avatar, $u_nickname, $u_gender){

        $userData = [
            'u_openid'=>$u_openid,
            'u_avatar'=>$u_avatar,
            'u_nickname'=>$u_nickname,
            'u_gender'=>$u_gender,
        ];

        $userModel = new User();
        $memberModel = new Member();

         if ($userModel->where('u_openid',$u_openid)->value('u_openid')!=null){
             $userModel->where('u_openid',$u_openid)->update($userData);
             $m_id = Db::table('member')->where('mu_openid',$u_openid)->value('m_id');
             $resultSet['m_id'] = $m_id;
         }else{

            $vipcode = $this->vipcode();
            $vipcodeurl = BarCodeUtil::barcode_create($vipcode);

            $memberData = [
                'mu_openid' => $u_openid,
                'm_vipcode' => $vipcode,
                'm_vipcodeurl' => $vipcodeurl,
                'm_point' => 0,
                'mg_id' => $memberModel->getGidForMemberByPoint(0),
                'm_tea_point' => 0,
            ];
            $user = new User();
            $member = new Member();
            //开启事务
            // $user->startTrans();
            if ($user::create($userData)){
                if (!$member::create($memberData)){
                    // $user->rollback();
                    return json(['errorCode'=>1002]);
                }
            }
            //事务提交
            // $user->commit();
            $m_id = Db::table('member')->where('mu_openid',$u_openid)->value('m_id');
            if ($m_id==null){
                $resultSet['errorCode']=0;
                $resultSet['m_id'] = $m_id;
            }else{
                $resultSet['errorCode']=1002;
            }
         }
        return $resultSet;
    }

    //生成会员码
    public function vipcode(){
        return time();
    }




}
