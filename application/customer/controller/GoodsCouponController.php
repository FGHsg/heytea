<?php

namespace app\customer\controller;

use app\common\model\Coupon;
use app\common\model\GoodsCoupon;
use app\common\model\Member;
use app\common\model\TeaOperation;
use think\Controller;
use think\Request;

class GoodsCouponController extends Controller
{
    /**
     * 获取兑换列表
     * @return \think\response\Json
     */
    public function getExchangeList()
    {
        //查询当前可兑换的兑换券信息
        $goodsCouponModel = new GoodsCoupon();
        $goodsCoupons = $goodsCouponModel->getGoodsCouponForCustomer();
        if (!is_null($goodsCoupons)) {
            $result = [
                'goodsCoupon' => $goodsCoupons,
                'errorCode' => 0
            ];
        } else {
            $result = [
                'goodsCoupon' => $goodsCoupons,
                'errorCode' => 1003
            ];
        }
        return json($result);
    }

    /**
     * 茶叶兑换兑换券
     * @return array
     */
    public function exchangeGoodsCoupon()
    {
        //获取请求参数
        $m_id = request()->param('m_id');
        $gc_id = request()->param('gc_id');
        $g_name = request()->param('g_name');
        $c_cost = request()->param('c_cost');
        //判断参数是否合法
        if (!is_null($m_id)
            && strlen($m_id) != 0
            && !is_null($gc_id)
            && strlen($gc_id) != 0
            && !is_null($g_name)
            && strlen($g_name) != 0
            && !is_null($c_cost)
            && strlen($c_cost) != 0) {
            //根据兑换券id查询兑换券所能兑换的商品的规格id
            $goodsCouponModel = new GoodsCoupon();
            $specifications_id = $goodsCouponModel->getSpecificationsidByGcid($gc_id);
            //新增用户兑换兑换券操作记录，修改当前用户拥有的茶叶数量，记录茶叶操作
            $couponModel = new Coupon();
            $memberModel = new Member();
            $teaOperationModel = new TeaOperation();
            //获取当前用户拥有的茶叶数量
            $currentTeaLeaves = $memberModel->getMTeaPointByMid($m_id);
            //判断用户当前拥有茶叶数是否满足兑换需求
            if ($currentTeaLeaves['m_tea_point'] >= $c_cost) {
                //开启事务
                $couponModel->startTrans();
                $memberModel->startTrans();
                $teaOperationModel->startTrans();
                //根据当前时间和当天已兑换兑换券的用户数获取兑换操作id
                $numberBefore = $couponModel->max('c_id');
                $todayNum = (date("Ymd") . '0001');
                if ($numberBefore < $todayNum) {
                    $c_id = $todayNum;
                } else {
                    $c_id = $numberBefore + 1;
                }
                //Coupon表新增数据
                $dataForCoupon = [
                    'c_id' => $c_id,
                    'cm_id' => $m_id,
                    'cgc_id' => $gc_id,
                    'c_target_id' => $specifications_id['gcgs_specifications_id'],
                    'c_target_name' => $g_name,
                    'c_cost' => $c_cost,
                    'c_status' => 0
                ];
                //TeaOperation表新增数据
                $dataForTea = [
                    'to_mid' => $m_id,
                    'to_class' => 1,
                    'to_target' => $gc_id,
                    'to_number' => $c_cost,
                    'to_rest' => $currentTeaLeaves['m_tea_point'] - $c_cost
                ];
                //判断新增操作是否成功
                if ($couponModel->create($dataForCoupon)
                    && $memberModel->decreaseMTeaPointByMid($m_id, $c_cost)
                    && $teaOperationModel->create($dataForTea)) {
                    $couponModel->commit();
                    $memberModel->commit();
                    $teaOperationModel->commit();
                    $result = [
                        'errorCode' => 0
                    ];
                } else {
                    //新增失败,rollback，返回错误码
                    $couponModel->rollback();
                    $memberModel->rollback();
                    $teaOperationModel->rollback();
                    $result = [
                        'errorCode' => 1002
                    ];
                }
            } else {
                //用户茶叶量不足，返回错误信息
                $result = [
                    'errorCode' => 1007,
                    'message' => '用户茶叶数量不足'
                ];
            }
        } else {
            //参数错误，返回错误码
            $result = [
                'errorCode' => 1001
            ];
        }
        return json($result);
    }
}
