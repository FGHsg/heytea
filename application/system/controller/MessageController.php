<?php

namespace app\system\controller;

use app\common\model\Message;
use think\Controller;
use think\Request;

class MessageController extends Auth{

    /**
     * 留言管理视图
     * @return mixed
     */
    public function MessageManage(){

        $create_time = input('create_time');
        $u_nickname = input('u_nickname');
        $m_status = input('m_status');

        $condition = array();
        if (!is_null($create_time)&&strlen($create_time)!=0){
            $startTime = substr($create_time, 0, 10);
            $endTime = substr($create_time, 13);
            $condition['me.create_time'] = array(array('EGT', $startTime), array('ELT', $endTime), 'AND');
            $this->assign('create_time',$create_time);
        }
        if (!is_null($u_nickname)&&strlen($u_nickname)!=0){
            $condition['u.u_nickname'] = $u_nickname;
            $this->assign('u_nickname',$u_nickname);
        }
        if (!is_null($m_status)&&strlen($m_status)!=0){
            $condition['me.m_status'] = $m_status;
            $this->assign('m_status',$m_status);
        }

        $messageModel = new Message();
        $messageList = $messageModel->getMessageListForAdmin($condition);
        $this->assign([
            'message' => $messageList,
        ]);
        return $this->fetch();
    }


    /**
     * 处理留言
     * @return $this
     */
    public function handelMessage(){
        $m_id = input('m_id');
        $messageModel = new Message();
        if ($message = $messageModel->find($m_id)){
            if ($message->m_status==0){
                $messageModel->update(['m_status'=>1],['m_id'=>$m_id]);
                return redirect('@system/MessageManage','MessageManage')->with('successTs','处理成功');
            }else{
                return redirect('@system/MessageManage','MessageManage')->with('errorTs','处理失败，留言状态不可更改');
            }
        }else{
            return redirect('@system/MessageManage','MessageManage')->with('errorTs','处理失败，该留言不存在');
        }
    }

}
