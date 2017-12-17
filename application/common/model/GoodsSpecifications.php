<?php

namespace app\common\model;

use think\Model;

class GoodsSpecifications extends Model
{
    protected $table = 'goods_specifications';
    protected $pk = 'gs_id';

    protected $autoWriteTimestamp = false;

    /**
     * 根据商品id查询商品规格信息
     * @param $gsg_id
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getGoodsSpecificationsByGsgid($gsg_id)
    {
        $rs = $this->where('gsg_id', 'eq', $gsg_id)
            ->field('gs_id,g_content,g_price')
            ->select();
        return $rs;
    }

    /**
     * 根据商品id和规格id查询规格信息
     * @param $gs_id
     * @param $gsg_id
     * @return array|false|\PDOStatement|string|Model
     */
    public function getGoodsSpecificationsByGsgidAndGsid($gs_id, $gsg_id)
    {
        $rs = $this->where('gs_id', 'eq', $gs_id)
            ->where('gsg_id', 'eq', $gsg_id)
            ->find();
        return $rs;
    }

}
