<?php

namespace app\system\controller;

use app\common\model\Business;
use app\common\model\Coupon;
use app\common\model\Member;
use app\common\model\TeaOperation;
use app\common\model\User;
use Symfony\Component\Yaml\Tests\B;
use think\Controller;
use think\Request;

class TeaController extends Auth {

    /**
     * 管理端获取茶叶操作记录列表
     * @return \think\response\Json
     */
    public function teaOperationList()
    {
        //接收筛选条件
        $to_class = request()->param('to_class');
        $u_nickname = request()->param('u_nickname');
        //拼接筛选条件数组
        $condition = array();
        if (!is_null($to_class) && strlen($to_class) != 0) {
            $condition['t.to_class'] = $to_class;
            $this->assign('to_class', $to_class);
        }
        if (!is_null($u_nickname) && strlen($u_nickname) != 0) {
            $memberModel = new Member();
            $member = $memberModel->getMidByUNickname($u_nickname);
            $condition['t.to_mid'] = $member['m_id'];
            $this->assign('u_nickname', $u_nickname);
        }
        //查询茶叶记录
        $teaOperationModel = new TeaOperation();
        $couponModel = new Coupon();
        $teaOperations = $teaOperationModel->getTeaOperationInfo($condition);
        foreach ($teaOperations as $teaOperation) {
            //若为兑换操作，则同时查询所兑换的商品名称
            if ($teaOperation['to_class'] == 1) {
                $coupon = $couponModel->getCTargetNameByCid($teaOperation['to_target']);
                $teaOperation['g_name'] = $coupon['c_target_name'];
            }
        }
        $this->assign('teaOperationList', $teaOperations);
        return $this->fetch();
    }

    /**
     * 根据店铺id修改茶叶设置
     * @return \think\response\Json
     */
    public function setTea()
    {
        //获取请求参数
        $b_id = request()->param('b_id');
        $b_tea_min = request()->param('b_tea_min');
        $b_tea_max = request()->param('b_tea_max');
        $b_tea_begin = request()->param('b_tea_begin');
        $b_tea_end = request()->param('b_tea_end');
        //判断参数是否合法
        if (!is_null($b_id)
            && strlen($b_id) != 0
            && !is_null($b_tea_min)
            && strlen($b_tea_min) != 0
            && !is_null($b_tea_max)
            && strlen($b_tea_max) != 0
            && !is_null($b_tea_begin)
            && strlen($b_tea_begin) != 0
            && !is_null($b_tea_end)
            && strlen($b_tea_end) != 0) {
            $data = [
                'b_tea_min' => $b_tea_min,
                'b_tea_max' => $b_tea_max,
                'b_tea_begin' => $b_tea_begin,
                'b_tea_end' => $b_tea_end
            ];
            $businessModel = new Business();
            //根据店铺id修改该店铺的茶叶设置
            if ($businessModel->save($data,['b_id' => $b_id])) {
                return redirect('@system/teaOperationList')->with('successTs', '修改茶叶设置成功');
            } else {
                return redirect('@system/teaOperationList')->with('errorTs', '修改茶叶设置失败');
            }
        } else {
            //参数不合法，返回错误信息
            return redirect('@system/teaOperationList')->with('errorTs', '修改茶叶设置失败，参数不合法');
        }
    }

}
