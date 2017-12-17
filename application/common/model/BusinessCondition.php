<?php

namespace app\common\model;

use think\Model;

class BusinessCondition extends Model{

    protected $table = 'business_condition';
    protected $pk = 'bc_id';


    /**
     * 获取当日已完成订单数
     * @return false|int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOrderAmount(){
        $bc_id = $this->getBCIDForToday();
        return $this->where('bc_id',$bc_id)->value('bc_order_count');
    }

    /**
     * 获取当日营业总金额
     * @return int|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSum(){
        $bc_id = $this->getBCIDForToday();
        return $this->where('bc_id',$bc_id)->value('bc_complete_sum');
    }

    /**
     * 返回当日商铺信息bc_id，若当前无该字段则新建之
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getBCIDForToday(){
        $todayNum = (date("Y-m-d").' 00:00:00');
        $bcTime = $this->max('create_time');
        dump($bcTime>$todayNum);
        if ($bcTime>$todayNum){
            return $this->where(['create_time'=>$bcTime])->find()['bc_id'];
        }else{
            $bcData = [
                'bc_order_count' => 0,
                'bc_complete_sum' => 0,
            ];
            $this::create($bcData);
            return $this->max('bc_id');
        }
    }

}
