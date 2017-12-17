<?php

namespace app\customer\controller;

use app\common\model\Address;
use app\common\model\Grade;
use app\common\model\Member;
use think\Config;
use think\Controller;
use think\Db;
use think\Request;

class MemberController extends Controller{


    /**
     * 获取会员信息
     * @param $m_id
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function getMemberInfo($m_id){
        $memberModel = new Member();
        $member = Member::get($m_id);
        if ($member!=null){
            $memberModel->refreshMemberInfoStatus($m_id);
            $resultSet = $member->getMemberInfo($m_id);
            $resultSet['errorCode'] = 0;
        }else{
            $resultSet['errorCode'] = 1003;
        }
        return json($resultSet);
    }

    /**
     * 返回对应用户特权信息
     * @param $m_id
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getPrivilegeInfo($m_id){
        $gradeInfo = Grade::all();
        $mg_id = Db::table('member')->where('m_id',$m_id)->value('mg_id');
        $g_rank = Db::table('grade')->where('g_id',$mg_id)->value('g_rank');
        $g_id_list = Db::table('grade')->where('g_rank','<=',$g_rank)->column('g_id');
        $privilegeInfo = Db::table('privilege')->where('pg_id',array('in','1,2,3'))->select();
        return $privilegeInfo;
    }

    /**
     * 创建用户地址信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addAddress(){
        $am_id = input('am_id');
        $a_class = input('a_class');
        $a_receiver = input('a_receiver');
        $a_phone = input('a_phone');
        $a_area = input('a_area');
        $a_details = input('a_details');
        $resultSet['errorCode'] = 0;
        if (!is_null($am_id)&&strlen($am_id)
            &&!is_null($a_class)&&strlen($a_class)
            &&($a_class==0||$a_class==1)
            &&!is_null($a_receiver)&&strlen($a_receiver)
            &&!is_null($a_phone)&&strlen($a_phone)
            &&!is_null($a_area)&&strlen($a_area)
            &&!is_null($a_details)&&strlen($a_details)){
            //鉴权
            if (Db::table('member')->find($am_id)){

                $addressData = [
                    'am_id' => $am_id,
                    'a_class' => $a_class,
                    'a_receiver' => $a_receiver,
                    'a_phone' => $a_phone,
                    'a_area' => $a_area,
                    'a_details' => $a_details,
                ];
                $addressModel = new Address();
                $addressModel->startTrans();

                //如果添加默认地址，则将已存在的默认地址设为非默认
                if ($a_class==0){
                    $a = $addressModel->where(['am_id' => $am_id, 'a_class' => $a_class])->find();
                    if ($a){
                        if (!$addressModel->update(['a_class' => 1,],['a_id' => $a['a_id']])){
                            $addressModel->rollback();
                            $resultSet['errorCode'] = 1002;
                        }
                    }
                }
                if (!$addressModel::create($addressData)){
                    $addressModel->rollback();
                    $resultSet['errorCode'] = 1002;
                }
                $addressModel->commit();
            }else{
                $resultSet['errorCode'] = 1003;
            }
        }else{
            $resultSet['errorCode'] = 1001;
        }
        return json($resultSet);
    }

    /**
     * 更改会员地址信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function editAddress(){
        $a_id = input('a_id');
        $am_id = input('am_id');
        $a_class = input('a_class');
        $a_receiver = input('a_receiver');
        $a_phone = input('a_phone');
        $a_area = input('a_area');
        $a_details = input('a_details');
        $resultSet['errorCode'] = 0;
        if (!is_null($a_id)&&strlen($a_id)!=0
            &&!is_null($am_id)&&strlen($am_id)!=0
            &&!is_null($a_class)&&strlen($a_class)!=0
            &&($a_class==0||$a_class==1)
            &&!is_null($a_receiver)&&strlen($a_receiver)!=0
            &&!is_null($a_phone)&&strlen($a_phone)!=0
            &&!is_null($a_area)&&strlen($a_area)!=0
            &&!is_null($a_details)&&strlen($a_details)!=0){
            //鉴权
            $addressModel = new Address();
            if (Db::table('member')->find($am_id)&&$addressModel->where('a_id',$a_id)->value('am_id')==$am_id){
                $addressData = [
//                    'a_id' => $a_id,
//                    'am_id' => $am_id,
                    'a_class' => $a_class,
                    'a_receiver' => $a_receiver,
                    'a_phone' => $a_phone,
                    'a_area' => $a_area,
                    'a_details' => $a_details,
                ];
                $addressModel->startTrans();
                //如果添加默认地址，则将已存在的默认地址设为非默认
                if ($a_class==0){
                    $a = $addressModel->where(['am_id' => $am_id, 'a_class' => $a_class])->find();
                    if ($a){
                        if (!$addressModel->update(['a_class' => 1,],['a_id' => $a['a_id']])){
                            $addressModel->rollback();
                            $resultSet['errorCode'] = 1002;
                        }
                    }
                }
                if (!$addressModel->update($addressData,['a_id'=>$a_id])){
                    $addressModel->rollback();
                    $resultSet['errorCode'] = 1002;
                }
                $addressModel->commit();
            }else{
                $resultSet['errorCode'] = 1003;
            }
        }else{
            $resultSet['errorCode'] = 1001;
        }
        return json($resultSet);
    }

    /**
     * 删除地址操作
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function deleteAddress(){
        $a_id = input('a_id');
        $am_id = input('am_id');

        if (!is_null($a_id)&&strlen($a_id)
            &&!is_null($am_id)&&strlen($am_id)){
            $addressModel = new Address();
            $member = Db::table('member')->find($am_id);
            $address =Db::table('address')->find($a_id);
            if ($member&&$address){
                if ($address['am_id']==$am_id){
                    $addressModel->where('a_id',$a_id)->delete();
                    $resultSet['errorCode'] = 0;
                }else{
                    $resultSet['errorCode'] = 1002;
                }
            }else{
                $resultSet['errorCode'] = 1003;
            }

        }else{
            $resultSet['errorCode'] = 1001;
        }
        return json($resultSet);
    }

    /**
     * 获取会员对应地址信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAddressInfo(){
        $a_id = input('a_id');
        if (!is_null($a_id)&&strlen($a_id)!=0){
            $resultSet['address'] = Db::table('address')->find($a_id);
            $resultSet['errorCode'] = 0;
        }else{
            $resultSet['errorCode'] = 1001;
        }
        return json($resultSet);
    }


    /**
     * 获取会员地址列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAddressList(){
        $m_id = input('m_id');
        if (!is_null($m_id)&&strlen($m_id)!=0){
            $defalut = Db::table('address')->where(['am_id'=>$m_id,'a_class'=>0])->find();
            $otherAddress = Db::table('address')->where(['am_id'=>$m_id,'a_class'=>1])->select();
//            $resultSet['address'] = array($defalut,$otherAddress);
            if ($defalut!=null){
                $resultSet['address'][] = $defalut;
            }
            if ($otherAddress!=null){
                foreach ($otherAddress as $oa){
                    $resultSet['address'][] = $oa;
                }
            }
            $resultSet['errorCode'] = 0;
        }else{
            $resultSet['errorCode'] = 1001;
        }
        return json($resultSet);
    }

}
