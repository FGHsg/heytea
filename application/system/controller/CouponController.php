<?php

namespace app\system\controller;

use app\common\model\Coupon;
use think\Controller;
use app\common\model\Member;
use think\Request;

class CouponController extends Auth
{
    /**
     * 获取兑换管理列表
     */
    public function couponList()
    {
        //获取请求参数
        $create_time = request()->param('create_time');
        $u_nickname = request()->param('u_nickname');
        $c_id = request()->param('c_id');
        //拼接筛选条件
        $condition = array();
        if (!is_null($create_time) && strlen($create_time) != 0) {
            $startTime = substr($create_time, 0, 10);
            $endTime = substr($create_time, 13);
            $condition['c.create_time'] = array(array('EGT', $startTime), array('ELT', $endTime), 'AND');
            $this->assign('create_time', $create_time);
        }
        if (!is_null($u_nickname) && strlen($u_nickname) != 0) {
            $memberModel = new Member();
            $member = $memberModel->getMidByUNickname($u_nickname);
            $condition['c.cm_id'] = $member['m_id'];
            $this->assign('u_nickname', $u_nickname);
        }
        if (!is_null($c_id) && strlen($c_id) != 0) {
            $condition['c.c_id'] = $c_id;
            $this->assign('c_id', $c_id);
        }
        //查询兑换管理列表内容（分页）
        $couponModel = new Coupon();
        $couponList = $couponModel->getCouponList($condition);
        $this->assign('couponList', $couponList);
        return $this->fetch();
    }

    /**
     * 修改兑换状态
     * @return \think\response\Redirect
     */
    public function updateCouponStatus()
    {
        //获取请求参数
        $c_id = request()->param('c_id');
        $label = request()->param('label');
        //判断参数是否合法
        if (!is_null($c_id)
            && strlen($c_id) != 0
            && !is_null($label)
            && strlen($label) != 0
            && ($label == 1 || $label == 2)) {
            //根据兑换id修改兑换状态
            $couponModel = new Coupon();
            if ($couponModel->save(['c_status' => $label], ['c_id' => $c_id])) {
                //修改成功，返回成功提示
                return redirect('@system/couponList')->with('success', '修改成功');
            } else {
                //修改失败，返回错误信息
                return redirect('@system/couponList')->with('errorTs', '修改失败');
            }
        } else {
            //参数不合法，返回错误信息
            return redirect('@system/couponList')->with('errorTs', '参数不合法，修改失败');
        }
    }
}
