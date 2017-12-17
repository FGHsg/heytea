<?php

namespace app\common\model;

use think\Db;
use think\Model;

class Message extends Model
{
    protected $table = 'message';
    protected $pk = 'm_id';

    /**
     * 获取用户留言列表
     * @param null $condition
     * @return \think\Paginator
     */
    public function getMessageListForAdmin($condition = null){
        if (is_array($condition)&&$condition!=null){
            $rs = Db::view('message me','m_id,mm_id,m_content,m_status,create_time')
                ->view('member m','m_id,mu_openid','m.m_id = me.mm_id')
                ->view('user u','u_openid,u_nickname','u.u_openid = m.mu_openid')
                ->where($condition)
                ->order('create_time','DESC')
                ->paginate(10);
        }else{
            $rs = Db::view('message me','m_id,mm_id,m_content,m_status,create_time')
                ->view('member m','m_id,mu_openid','m.m_id = me.mm_id')
                ->view('user u','u_openid,u_nickname','u.u_openid = m.mu_openid')
                ->order('create_time','DESC')
                ->paginate(10);
        }
        return $rs;
    }
}
