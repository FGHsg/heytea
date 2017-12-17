<?php

namespace app\common\model;

use think\Model;

class Business extends Model
{
    protected $table = 'business';
    protected $pk = 'b_id';




    /**
     * 根据店铺id获取该店铺的采茶的范围上限和下限
     * @param $b_id
     * @return array|false|\PDOStatement|string|Model
     */
    public function getTeaMinAndMaxByBid($b_id)
    {
        $rs = $this->where('b_id', 'eq', $b_id)
            ->field('b_tea_min,b_tea_max')
            ->find();
        return $rs;
    }

    /**
     * 根据店铺id获取该店铺的采茶开始时间和结束时间
     * @param $b_id
     * @return array|false|\PDOStatement|string|Model
     */
    public function getTeaBeginAndTeaEndByBid($b_id)
    {
        $rs = $this->where('b_id', 'eq', $b_id)
            ->field('b_tea_begin,b_tea_end')
            ->find();
        return $rs;
    }

}
