<?php

namespace app\common\model;

use think\Db;
use think\Model;

class TeaOperation extends Model
{
    protected $table = 'tea_operation';
    protected $pk = 'to_id';


    /**
     * 获取该用户当日的采摘茶叶操作
     * @param $m_id
     * @return array|false|\PDOStatement|string|Model
     */
    public function getTodayPackTeaOperation($m_id, $todayTime)
    {
        $rs = $this->where('to_mid', 'eq', $m_id)
            ->where('to_class', 'eq', 4)
            ->where('create_time', '>', $todayTime)
            ->find();
        return $rs;
    }

    /**
     * 获取该用户的茶树操作记录（茶叶明细）
     * @param $m_id
     * @param $page
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getTeaOperationInfoByMid($m_id, $page)
    {
        $rs = $this->where('to_mid', 'eq', $m_id)
            ->order('create_time', 'DESC')
            ->page($page, 10)
            ->select();
        return $rs;
    }


    /**
     * 获取管理端茶叶记录
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getTeaOperationInfo($condition=null)
    {
        if (is_array($condition) && count($condition) != 0) {
            $rs = Db::view('tea_operation t', 'to_class,to_target,to_number,to_rest,create_time')
                ->view('member m', 'm_id', 'm.m_id = t.to_mid')
                ->view('user u', 'u_avatar,u_nickname', 'u.u_openid = m.mu_openid')
                ->where($condition)
                ->order('t.create_time', 'DESC')
                ->paginate(10);
        }else {
            $rs = Db::view('tea_operation t', 'to_class,to_target,to_number,to_rest,create_time')
                ->view('member m', 'm_id', 'm.m_id = t.to_mid')
                ->view('user u', 'u_avatar,u_nickname', 'u.u_openid = m.mu_openid')
                ->order('t.create_time', 'DESC')
                ->paginate(10);
        }
        return $rs;
    }

    /**
     * 根据id修改分享操作的分享对象
     * @param $to_id
     * @param $m_id
     * @return $this
     */
    public function updateToTargetByToid($to_id, $m_id)
    {
        $rs = $this->where('to_id', 'eq', $to_id)
            ->where('to_class', 'eq', 2)
            ->where('to_target', 'eq', 0)
            ->update(['to_target' => $m_id]);
        return $rs;
    }

    /**
     * 根据茶叶操作id查询茶叶操作者的会员id
     * @param $to_id
     * @return array|false|\PDOStatement|string|Model
     */
    public function getToMidByToid($to_id)
    {
        $rs = $this->where('to_id', 'eq', $to_id)
            ->field('to_mid')
            ->find();
        return $rs;
    }


    /**
     * 查询1天前未退还茶叶的无人领取的分享茶叶操作
     * @param $m_id
     * @param $yesterdayTime
     * @return array|false|\PDOStatement|string|Model
     */
    public function getLastShareOperation($m_id, $yesterdayTime)
    {
        $rs = $this->where('to_mid', 'eq', $m_id)
            ->where('to_class', 'eq', 2)
            ->where('to_target', 'eq', 0)
            ->where('create_time', '<', $yesterdayTime)
            ->find();
        return $rs;
    }

    /**
     * 修改分享操作为被退还的赠予操作
     * @param $to_id
     * @return $this
     */
    public function updateReturnTeaOperation($to_id)
    {
        $rs = $this->where('to_id', 'eq', $to_id)
            ->update(['to_class' => 5]);
        return $rs;
    }
}
