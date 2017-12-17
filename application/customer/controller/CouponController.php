<?php

namespace app\customer\controller;

use app\common\model\Coupon;
use think\Controller;
use think\Request;

class CouponController extends Controller
{
    /**
     * 获取兑换券列表
     * @return \think\response\Json
     */
    public function getCouponList()
    {
        //获取请求参数
        $m_id = input('m_id');
        //判断参数是否合法
        if (!is_null($m_id) && strlen($m_id) != 0) {
            //根据会员id获取兑换券列表
            $couponModel = new Coupon();
            $couponList = $couponModel->getCouponByCmid($m_id);
            $result = [
                'errorCode' => 0,
                'couponList' => $couponList
            ];
        } else {
            //参数不合法，返回错误码
            $result = [
                'errorCode' => 1001
            ];
        }
        return json($result);
    }
}
