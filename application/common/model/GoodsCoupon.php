<?php
/**
 * Created by PhpStorm.
 * User: Even
 * Date: 2017/11/26
 * Time: 0:48
 */

namespace app\common\model;

use think\Db;
use think\Model;
class GoodsCoupon extends Model
{
    protected $table = 'goods_coupon';
    protected $pk = 'gc_id';

    /**
     * 客户端查询兑换券信息
     * @param $gc_status
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getGoodsCouponForCustomer()
    {
        $rs = Db::view('goods_coupon gc', 'gc_cost,gc_id')
            ->view('goods g', 'g_name,g_cover', 'g.g_id = gc.gcg_id')
            ->select();
        return $rs;
    }

    /**
     * 根据兑换券id查询其兑换的商品规格id
     * @param $gc_id
     * @return array|false|\PDOStatement|string|Model
     */
    public function getSpecificationsidByGcid($gc_id)
    {
        $rs = $this->where('gc_id', 'eq', $gc_id)
            ->field('gcgs_specifications_id')
            ->find();
        return $rs;
    }

    /**
     * 获取管理端兑换券信息
     * @param null $condition
     * @return \think\Paginator
     */
    public function getGoodsCouponForAdmin($condition=null)
    {
        if (is_array($condition) && count($condition) != 0) {
            $rs = Db::view('goods_coupon gc', 'gc_id,gc_cost,create_time')
                ->view('goods g', 'g_id,g_name', 'g.g_id = gc.gcg_id')
                ->view('goods_specifications gs', 'g_content,g_price', 'gs.gs_id = gc.gcgs_specifications_id')
                ->where($condition)
                ->paginate(10);
        } else {
            $rs = Db::view('goods_coupon gc', 'gc_id,gc_cost,create_time')
                ->view('goods g', 'g_id,g_name', 'g.g_id = gc.gcg_id')
                ->view('goods_specifications gs', 'g_content,g_price', 'gs.gs_id = gc.gcgs_specifications_id')
                ->paginate(10);
        }
        return $rs;
    }
}