<?php

namespace app\customer\controller;

use app\common\model\Business;
use app\common\model\Coupon;
use app\common\model\Member;
use app\common\model\TeaOperation;
use app\common\model\User;
use think\Controller;
use think\Request;

class TeaController extends Controller{

    /**
     * 客户端采茶叶
     * @return \think\response\Json
     */
    public function getTeaLeaves()
    {
        //获取请求参数
        $m_id = request()->param('m_id');
        $b_id = request()->param('b_id');
        //判断参数是否合法
        if (!is_null($m_id)
            && strlen($m_id) != 0
            && !is_null($b_id)
            && strlen($b_id) != 0) {
            //查询随机生成茶叶数量的上下限
            $businessModel = new Business();
            $business = $businessModel->getTeaMinAndMaxByBid($b_id);
            //随机生成茶叶数量
            $teaLeaves = rand($business['b_tea_min'], $business['b_tea_max']);
            //实例化model对象
            $memberModel = new Member();
            $teaOperationModel = new TeaOperation();
            //查询当前用户的茶叶数
            $currentTeaLeaves = $memberModel->getMTeaPointByMid($m_id);
            //开启事务
            $memberModel->startTrans();
            $teaOperationModel->startTrans();
            $data = [
                'to_mid' => $m_id,
                'to_class' => 4,
                'to_target' => 0,
                'to_number' => $teaLeaves,
                'to_rest' => ($currentTeaLeaves['m_tea_point'] + $teaLeaves)
            ];
            if ($memberModel->increaseMTeaPointByMid($m_id,$teaLeaves) && $teaOperationModel->create($data)) {
                $memberModel->commit();
                $teaOperationModel->commit();
                $result = [
                    'errorCode' => 0,
                    'tea_point' => $teaLeaves
                ];
            } else {
                $memberModel->rollback();
                $teaOperationModel->rollback();
                $result = [
                    'errorCode' => 1002
                ];
            }
        } else {
            $result = [
                'errorCode' => 1001
            ];
        }
        return json($result);
    }

    /**
     * 获取当前用户茶树操作记录（茶叶明细）
     * @return \think\response\Json
     */
    public function getDetailTeaOperation()
    {
        //获取参数
        $m_id = request()->param('m_id');
        $page = request()->param('page');
        $b_id = request()->param('b_id');
        //判断参数是否合法
        if (!is_null($m_id)
            && strlen($m_id) != 0
            && !is_null($page)) {
            $teaOperationModel = new TeaOperation();
            //判断当天是否已采过茶叶，判断当前时间是否在采茶时间范围内
            $businessModel = new Business();
            $teaTime = $businessModel->getTeaBeginAndTeaEndByBid($b_id);
            $todayTime = date('Y-m-d 00:00:00');
            $currentTime = date('H:i:s');
            if ($teaOperationModel->getTodayPackTeaOperation($m_id, $todayTime)
                || $currentTime <= $teaTime['b_tea_begin']
                || $currentTime >= $teaTime['b_tea_end']) {
                $result['isGet'] = 0;
            } else {
                $result['isGet'] = 1;
                $result['currentTime'] = $currentTime;
            }
            //判断该用户是否有超过24小时未被人领取的分享茶叶操作
            $yesterdayTime = date('Y-m-d H:i:s', strtotime('-1 day'));
            $shareOperation = $teaOperationModel->getLastShareOperation($m_id, $yesterdayTime);
            $memberModel = new Member();
            if ($shareOperation) {
                //获取该用户当前拥有的茶叶数量
                $currentTeaLeaves = $memberModel->getMTeaPointByMid($m_id);
                $data = [
                    'to_mid' => $m_id,
                    'to_class' => 6,
                    'to_rest' => ($currentTeaLeaves['m_tea_point'] + $shareOperation['to_number']),
                    'to_target' => 0,
                    'to_number' => $shareOperation['to_number']
                ];
                //开启事务
                $memberModel->startTrans();
                $teaOperationModel->startTrans();
                //若有则将分享的茶叶返回给该用户，将分享操作修改为被退还的赠予操作，并记录退还操作
                if ($memberModel->increaseMTeaPointByMid($m_id,$shareOperation['to_number'])
                    && $teaOperationModel->updateReturnTeaOperation($shareOperation['to_id'])
                    && $teaOperationModel->create($data)) {
                    $memberModel->commit();
                    $teaOperationModel->commit();
                } else {
                    $memberModel->rollback();
                    $teaOperationModel->rollback();
                    $result = [
                        'errorCode'=>1002
                    ];
                    return json($result);
                }
            }
            //查询当前用户的茶树操作信息（分页）
            $teaOperation = $teaOperationModel->getTeaOperationInfoByMid($m_id, $page);
            $memberTeaPoint = $memberModel->getMTeaPointByMid($m_id);
            $result['memberTeaPoint'] = $memberTeaPoint['m_tea_point'];
            $result['detailTeaOperation'] = $teaOperation;
            $result['errorCode'] = 0;
        } else {
            //参数错误，返回错误码
            $result = [
                'detailTeaOperation' => array(),
                'errorCode' => 1001
            ];
        }
        return json($result);
    }

    /**
     * 客户端分享茶叶
     * @return \think\response\Json
     */
    public function shareTeaLeaves()
    {
        //获取请求参数
        $m_id = request()->param('m_id');
        $teaLeaves = request()->param('teaLeaves');
        //判断参数是否合法，不合法则返回错误码
        if (!is_null($m_id)
            && strlen($m_id) != 0
            && !is_null($m_id)
            && !empty($teaLeaves)) {
            //实例化模型类
            $memberModel = new Member();
            $teaOperationModel = new TeaOperation();
            //开启事务
            $memberModel->startTrans();
            $teaOperationModel->startTrans();
            //修改当前用户的茶叶数量，并新增茶叶操作记录，两个操作都成功则commit，其中一个失败则rollback
            $currentTeaLeaves = $memberModel->getMTeaPointByMid($m_id);
            //判断当前用户所拥有的茶叶数量是否满足分享需求
            if ($currentTeaLeaves['m_tea_point'] >= $teaLeaves) {
                $data = [
                    'to_mid' => $m_id,
                    'to_class' => 2,
                    'to_target' => 0,
                    'to_number' => $teaLeaves,
                    'to_rest' => ($currentTeaLeaves['m_tea_point'] - $teaLeaves)
                ];
                $teaOperation = $teaOperationModel->create($data);
                if ($teaOperation && $memberModel->decreaseMTeaPointByMid($m_id, $teaLeaves)) {
                    $memberModel->commit();
                    $teaOperationModel->commit();
                    $result = [
                        'to_id' => $teaOperation['to_id'],
                        'errorCode' => 0
                    ];
                } else {
                    //数据库异常，返回错误码
                    $memberModel->rollback();
                    $teaOperationModel->rollback();
                    $result = [
                        'errorCode' => 1002
                    ];
                }
            } else {
                //用户茶叶数量不足，返回错误信息
                $result = [
                    'errorCode' => 1007
                ];
            }
        } else {
            //参数不合法，返回错误码
            $result = [
                'errorCode' => 1001
            ];
        }
        return json($result);
    }

    /**
     * 客户端领取茶叶
     * @return array
     */
    public function receiveTeaLeaves()
    {
        //获取请求参数
        $to_id = request()->param('to_id');
        $m_id = request()->param('m_id');
        $teaLeaves = request()->param('teaLeaves');
        //判断参数是否合法
        if (!is_null($to_id)
            && strlen($to_id) != 0
            && !is_null($m_id)
            && strlen($m_id) != 0
            && !is_null($teaLeaves)
            && strlen($teaLeaves) != 0) {
            $teaOperationModel = new TeaOperation();
            $memberModel = new Member();
            //查询受赠用户的茶叶数量，判断是否存在该用户
            $member = $memberModel->getMTeaPointByMid($m_id);
            if ($member) {
                //判断当前分享是否已被领取
                $shareTeaOperation = $teaOperationModel->get($to_id);
                if($shareTeaOperation['to_target'] == 0) {
                    //开启事务
                    $teaOperationModel->startTrans();
                    $memberModel->startTrans();
                    //根据分享者的分享茶叶操作的id更新分享对象（即c_target字段），并获取分享者的会员id
                    $teaOperation = $teaOperationModel->updateToTargetByToid($to_id, $m_id);
                    if ($teaOperation) {
                        //查询分享者的会员id
                        $participator = $teaOperationModel->getToMidByToid($to_id);
                        //获取受赠者的当前茶叶数量，增加受赠者的茶叶数量，并且记录领取操作
                        $data = [
                            'to_mid' => $m_id,
                            'to_class' => 3,
                            'to_target' => $participator['to_mid'],
                            'to_number' => $teaLeaves,
                            'to_rest' => $member['m_tea_point'] + $teaLeaves
                        ];
                        //判断操作是否成功，成功则commit，失败则rollback
                        if ($memberModel->increaseMTeaPointByMid($m_id, $teaLeaves) && $teaOperationModel->create($data)) {
                            $teaOperationModel->commit();
                            $memberModel->commit();
                            $result = [
                                'errorCode' => 0
                            ];
                        } else {
                            $teaOperationModel->rollback();
                            $memberModel->rollback();
                            $result = [
                                'errorCode' => 1002
                            ];
                        }
                    } else {
                        //数据库异常，返回错误码
                        $teaOperationModel->rollback();
                        $result = [
                            'errorCode' => 1002
                        ];
                    }
                } else {
                    //该分享已被领取，返回错误码
                    $result = [
                        'errorCode' => 1006
                    ];
                }
            } else {
                //数据库中不存在该信息，返回错误码
                $result = [
                    'errorCode' => 1003
                ];
            }
        } else {
            //参数不合法，返回错误码
            $result = [
                'errorCode' => 1001
            ];
        }
        return json($result);
    }

}
