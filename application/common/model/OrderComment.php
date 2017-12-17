<?php

namespace app\common\model;

use think\Db;
use think\Model;

class OrderComment extends Model
{
    protected $table = 'order_comment';
    protected $pk = 'oc_id';

    /**
     * 获取会员对应订单评价
     * @param $m_id
     * @param $page
     * @return mixed
     */
    public function getMyOrderCommentList($m_id,$page){
        $rs = Db::table('order_comment')
            ->where('ocm_id', 'eq', $m_id)
            ->order('create_time', 'DESC')
            ->page($page,10)
            ->select();
        return $rs;
    }
}
