<?php

namespace app\common\model;

use think\Model;
use think\Db;

class Comment extends Model
{
    protected $table = 'comment';
    protected $pk = 'c_id';

    /**
     * 客户端获取茶评列表（分页）
     * @param $start
     * @return mixed
     */
    public function getCommentListForCustomer($page)
    {
        $rs = Db::table('comment')
            ->where('c_status', 'eq',1)
            ->order('create_time', 'DESC')
            ->page($page, 10)
            ->select();
        return $rs;
    }

    /**
     * 管理端获取茶评列表
     * @param null $condition
     * @return \think\Paginator
     */
    public function getCommentListForAdmin($condition=null)
    {
        if (is_array($condition) && count($condition) != 0) {
            $rs = Db::view('comment c', 'c_id,cg_content,c_status,c_score,c_content,c_img,create_time')
                ->view('member m', 'm_id', 'm.m_id = c.cm_id')
                ->view('user u', 'u_avatar,u_nickname', 'u.u_openid = m.mu_openid')
                ->where($condition)
                ->order('c.create_time', 'DESC')
                ->paginate(5);
        }else{
            $rs = Db::view('comment c', 'c_id,cg_content,c_status,c_score,c_content,c_img,create_time')
                ->view('member m', 'm_id', 'm.m_id = c.cm_id')
                ->view('user u', 'u_avatar,u_nickname', 'u.u_openid = m.mu_openid')
                ->order('c.create_time', 'DESC')
                ->paginate(5);
        }

        return $rs;
    }

    /**
     * 客户端获取我的茶评列表
     * @param $m_id
     * @param $page
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMyCommentListForCustomer($m_id, $page)
    {
        $rs = Db::table('comment')
            ->where('cm_id', 'eq', $m_id)
            ->order('create_time', 'DESC')
            ->page($page,10)
            ->select();
        return $rs;
    }
}
