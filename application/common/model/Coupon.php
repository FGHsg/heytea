<?php

namespace app\common\model;

use think\Db;
use think\Model;

class Coupon extends Model
{
    protected $table = 'coupon';
    protected $pk = 'c_id';

    /**
     * 根据兑换券号码查询兑换券对应的商品名称
     * @param $c_id
     * @return array|false|\PDOStatement|string|Model
     */
    public function getCTargetNameByCid($c_id)
    {
        $rs = $this->where('c_id', 'eq', $c_id)
            ->field('c_target_name')
            ->find();
        return $rs;
    }

    /**
     * 获取管理端兑换管理列表
     * @param $condition
     * @return \think\Paginator
     */
    public function getCouponList($condition=null)
    {
        if (is_array($condition) && count($condition) != 0) {
            $rs = Db::view('coupon c', 'c_id,c_cost,create_time,c_target_name')
                ->view('member m', 'm_id', 'm.m_id = c.cm_id')
                ->view('user u', 'u_nickname', 'u.u_openid = m.mu_openid')
                ->view('goods_specifications gs', 'g_content', 'gs.gs_id = c.c_target_id')
                ->where('c.c_status', 'eq', 0)
                ->where($condition)
                ->order('c.create_time', 'DESC')
                ->paginate(10);
        } else {
            $rs = Db::view('coupon c', 'c_id,c_cost,create_time,c_target_name')
                ->view('member m', 'm_id', 'm.m_id = c.cm_id')
                ->view('user u', 'u_nickname', 'u.u_openid = m.mu_openid')
                ->view('goods_specifications gs', 'g_content', 'gs.gs_id = c.c_target_id')
                ->where('c.c_status', 'eq', 0)
                ->order('c.create_time', 'DESC')
                ->paginate(10);
        }
        return $rs;
    }

    /**
     * 根据会员id查询兑换券信息
     * @param $cm_id
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getCouponByCmid($cm_id)
    {
        $rs = $this->where('cm_id', 'eq', $cm_id)
            ->order('create_time', 'DESC')
            ->field('c_id,c_target_name,c_status,create_time')
            ->select();
        return $rs;
    }
}
