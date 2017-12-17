<?php

namespace app\customer\controller;

use app\common\model\Message;
use think\Controller;
use think\Db;
use think\Request;

class MessageController extends Controller{

    /**
     * 客户端发送留言评论
     * @return \think\response\Json
     */
    public function sendMessage(){
        $mm_id = input('m_id');
        $m_content = input('m_content');

        if (!is_null($mm_id)&&strlen($mm_id)!=0
            &&!is_null($m_content)&&strlen($m_content)!=0){
            if (Db::table('member')->find($mm_id)){
                $messageData = [
                    'mm_id' => $mm_id,
                    'm_content' => $m_content,
                    'm_status' => 0,
                ];
                $messageModel = new Message();
                $message = $messageModel->save($messageData);
                if ($message){
                    $resultSet['errorCode'] = 0;
                }else{
                    $resultSet['errorCode'] = 1002;
                }
            }else{
                $resultSet['errorCode'] = 1003;
            }

        }else{
            $resultSet['errorCode'] = 1001;
        }
        return json($resultSet);
    }



}
