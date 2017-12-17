<?php

namespace app\system\controller;

use app\common\model\Grade;
use app\common\model\Privilege;
use think\Config;
use app\common\model\Member;
use think\Controller;
use think\Db;
use think\Image;

class MemberController extends Auth {


    /**
     * 获取用户信息列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function userList(){

        //筛选条件
        $g_id = request()->param('g_id');
        $m_phone = request()->param('m_phone');
        $u_nickname = request()->param('u_nickname');

        $condition = array();
        if (!is_null($g_id)&&strlen($g_id)!=0){
            $condition['g.g_id'] = $g_id;
            $this->assign('g_id',$g_id);
        }
        if (!is_null($m_phone)&&strlen($m_phone)!=0){
            $condition['m.m_phone'] = $m_phone;
            $this->assign('m_phone',$m_phone);
        }
        if (!is_null($u_nickname)&&strlen($u_nickname)!=0){
            $condition['u.u_nickname'] = $u_nickname;
            $this->assign('u_nickname',$u_nickname);
        }

        $member = new Member();
        $members = $member->getMemberListForAdmin($condition);
        $grade = Db::table('grade')->select();
//        return json([
//            'member' => $members,
//            'grade' => $grade,
//        ]);
        $this->assign([
            'member' => $members,
            'grade' => $grade,
        ]);
        return $this->fetch();
    }

    //地址管理列表
    public function addressManage(){
        $u_nickname = input('u_nickname');
        $a_receiver = input('a_receiver');
        $a_phone = input('a_phone');
        $a_details = input('a_details');

        $condition = array();
        if (!is_null($u_nickname)&&strlen($u_nickname)!=0){
            $condition['u.u_nickname'] = $u_nickname;
            $this->assign('u_nickname',$u_nickname);
        }
        if (!is_null($a_receiver)&&strlen($a_receiver)!=0){
            $condition['a.a_receiver'] = $a_receiver;
            $this->assign('a_receiver',$a_receiver);
        }
        if (!is_null($a_phone)&&strlen($a_phone)!=0){
            $condition['a.a_phone'] = $a_phone;
            $this->assign('a_phone',$a_phone);
        }
        if (!is_null($a_details)&&strlen($a_details)!=0){
            $condition['a.a_details'] = $a_details;
            $this->assign('a_details',$a_details);
        }
        $member = new Member();
        $addressList = $member->getAddressListForAdmin($condition);
//        return json(['addressList' => $addressList]);
        $this->assign([
            'addressList' => $addressList,
        ]);
        return $this->fetch();
    }

    //等级管理列表
    public function gradeManage(){

        $g_name = input('g_name');
        $g_point = input('g_point');
        $condition = array();
        if (!is_null($g_name)&&strlen($g_name)!=0){
            $condition['g.g_name'] = $g_name;
            $this->assign('g_name',$g_name);
        }
        if (!is_null($g_point)&&strlen($g_point)!=0){
            $condition['g.g_point'] = $g_point;
            $this->assign('g_point',$g_point);
        }

        $member = new Member();
        $gradeList = $member->getGradeListForAdmin($condition);
//        return json(['grade' => $gradeList,]);
        $this->assign([
            'grade' => $gradeList,
        ]);
        return $this->fetch();
    }


    /**
     * 添加等级
     * @return $this
     */
    public function addGrade(){
        $g_name = input('g_name');
        $g_point = input('g_point');

        $gradeModel = new Grade();
        $grade1 = $gradeModel->where('g_name',$g_name)->find();
        $grade2 = $gradeModel->where('g_point',$g_point)->find();

        if (!$grade1&&!$grade2){
            $gradeData = [
                'g_name' => $g_name,
                'g_point' => $g_point,
            ];
            $gradeModel::create($gradeData);
            return redirect('@system/gradeManage')->with('successTs','等级添加成功');
        }else{
            return redirect('@system/gradeManage')->with('errorTs','参数有误，对应等级已存在或积分点冲突');
        }

    }

    /**
     * 编辑会员等级接口
     * @return $this
     */
    public function editGrade(){
        $g_id = input('g_id');
        $g_name = input('g_name');
        $g_point = input('g_point');

        if (!is_null($g_id)&&strlen($g_id)!=0){
            $condition = array();
            $condition['g_id'] = $g_id;
            if (!is_null($g_name)&&strlen($g_name)!=0){
                $condition['g_name'] = $g_name;
                $this->assign('g_name',$g_name);
            }
            if (!is_null($g_point)&&strlen($g_point)!=0){
                $condition['g_point'] = $g_point;
                $this->assign('g_point',$g_point);
            }
            $grade = new Grade();
            $grade->update($condition);
            return redirect('@system/gradeManage')->with('successTs','等级修改成功！');
        }else{
            return redirect('@system/gradeManage')->with('errorTs', '传入参数有错，g_id必须');
        }
    }


    /**
     * 删除等级操作
     * @return $this
     */
    public function deleteGrade(){
        $g_id = input('g_id');
        if (!is_null($g_id)&&strlen($g_id)!=0){
            $gradeModel = new Grade();
            $grade = $gradeModel->where('g_id',$g_id)->find();
            if (!is_null($grade)&&strlen($grade)!=0){
                $member = new Member();
                $members = $member->where('mg_id',$g_id)->select();
                foreach ($members as $m){
                    $member->where('m_id',$m->m_id)->update(['mg_id'=>0]);
                }
                $grade->delete();
                return redirect('@system/gradeManage')->with('successTs','等级删除成功');
            }else{
                return redirect('@system/gradeManage')->with('errorTs','该等级不存在');
            }
        }else{
            return redirect('@system/gradeManage')->with('errorTs','参数错误');;
        }
    }


    //特权管理
    public function privilegeManage(){
        $member = new Member();
        $privilege = $member->getPrivilegeForAdmin();

//        return json(['privilege' => $privilege,]);
        $this->assign([
            'privilege' => $privilege,
        ]);
        return $this->fetch();
    }

    //添加特权视图
    public function addPrivilegeView(){
        $grade = Db::table('grade')->paginate(10);
//        return json(['grade' => $grade,]);
        $this->assign([
            'grade' => $grade,
        ]);
        return $this->fetch();
    }

    /**
     * 添加特权接口
     * @return $this
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addPrivilegeOperation(){
        $p_name = input('p_name');
        $g_id = input('g_id');
        $p_img = request()->file('p_img');
        $p_introduction = input('p_introduction');

        $g_rank = Db::table('grade')->where('g_id',$g_id)->value('g_rank');

        $privilege = new Privilege();
        $grade = new Grade();
        if ($privilege->where('p_name',$p_name)->find()||!$grade->where('g_id',$g_id)->find()){
            return redirect('@system/privilegeManage')->with('errorTs','特权已存在');
        }else{
            $host = 'http://120.78.183.57/';
            $savePath = 'public/uploads/privilege/';
            $p_img->validate(['size' => 10485760, 'ext' => 'jpg,png']);
            $imgComp = Image::open($p_img);
            $fileName = md5(time())  . '.jpg';
            $imgUploadResult = $imgComp->thumb(360, 360)
                ->save(ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'privilege' . DS . $fileName, 'jpg');
            if ($imgUploadResult) {
                $imgurl = $host . $savePath . $fileName;
                $privilegeData = [
                    'p_name' => $p_name,
                    'pg_id' => $g_id,
                    'pg_rank' => $g_rank,
                    'p_img' => $imgurl,
                    'p_introduction' => $p_introduction,
                ];
                $privilege::create($privilegeData);
                return redirect('@system/privilegeManage')->with('successTs','特权创建成功');
            }else{
                return redirect('@system/privilegeManage')->with('errorTs','特权创建失败');
            }
        }
    }


    /**
     * 修改特权视图
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function editPrivilegeView(){
        $p_id = input('p_id');
        $privilege = Db::table('privilege')->find($p_id);
        $grade = Db::table('grade')->paginate(10);

        //return json(['privilege' => $privilege, 'grade' => $grade,]);
        $this->assign([
            'privilege' => $privilege,
            'grade' => $grade,
        ]);
        return $this->fetch();
    }

    /**
     * 特权编辑操作接口
     * @return $this
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editPrivilegeOperation(){
        $p_id = input('p_id');
        $p_name = input('p_name');
        $pg_id = input('pg_id');
        $p_img = request()->file('p_img');
        $p_introduction = input('p_introduction');
        //设置特权参数
        $privilegeData = array();
        if (!is_null($pg_id)&&strlen($pg_id)){
            $privilegeData['pg_id'] = $pg_id;
            $this->assign('pg_id',$pg_id);
        }
        if (!is_null($p_name)&&strlen($p_name)!=0){
            $privilegeData['p_name'] = $p_name;
            $this->assign('p_name',$p_name);
        }
        if (!is_null($p_introduction)&&strlen($p_introduction)){
            $privilegeData['p_introduction'] = $p_introduction;
            $this->assign('p_introduction',$p_introduction);
        }
        if (!is_null($p_id)&&strlen($p_id)!=0){
            $privilegeModel = new Privilege();
            $privilege = $privilegeModel->get($p_id);
            //保存特权图
            $p_img_befo = $privilege['p_img'];
            if ($p_img){
                $host = 'http://120.78.183.57/';
                $savePath = 'public/uploads/privilege/';
                $p_img->validate(['size' => 10485760, 'ext' => 'jpg,png']);
                $imgComp = Image::open($p_img);
                $fileName = md5(time())  . '.jpg';
                $imgUploadResult = $imgComp->thumb(360, 360)
                    ->save(ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'privilege' . DS . $fileName, 'jpg');
                if ($imgUploadResult) {
                    //图片保存成功，删除原图
                    unlink(ROOT_PATH.substr($p_img_befo,21));
                    $imgurl = $host . $savePath . $fileName;
                    $privilegeData['p_id'] = $p_id;
                    $privilegeData['p_img'] = $imgurl;
                    $privilegeModel->update($privilegeData,['p_id',$p_id]);
                    return redirect('@system/privilegeManage')->with('successTs','特权修改成功');
                }else{
                    return redirect('@system/privilegeManage')->with('errorTs','特权修改失败');
                }
            }else{
                $privilegeData['p_id'] = $p_id;
                $privilegeModel->update($privilegeData,['p_id',$p_id]);
                return redirect('@system/privilegeManage')->with('successTs','特权修改成功');
            }
        }else{
            return redirect('@system/privilegeManage')->with('errorTs','传入参数错误');
        }
    }


    /**
     * 删除特权操作
     * @return $this
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function deletePrivilegeOperation(){
        $p_id = input('p_id');
        if (!is_null($p_id)&&strlen($p_id)!=0){
            $privilegeModel = new Privilege();
            $privilege = $privilegeModel->find($p_id);
            if ($privilege){
                $imgurl = $privilege->p_img;
                if ($privilegeModel->where('p_id',$p_id)->delete()){
                    unlink(ROOT_PATH.substr($imgurl,21));
                    return redirect('@system/privilegeManage')->with('successTs','特权删除成功');
                }else{
                    return redirect('@system/privilegeManage')->with('errorTs','特权删除失败');
                }
            }else{
                return redirect('@system/privilegeManage')->with('errorTs','传入参数错误，查无此特权');
            }
        }else{
            return redirect('@system/privilegeManage')->with('errorTs','传入参数错误');
        }
    }



    //消费记录视图
    public function consumptionHistory(){
        $create_time = input('create_time');
        $u_nickname = input('u_nickname');
        $o_class = input('o_class');
        $condition = array();
        if (!is_null($create_time)&&strlen($create_time)!=0){
            $startTime = substr($create_time, 0, 10);
            $endTime = substr($create_time, 13);
            $condition['o.create_time'] = array(array('EGT', $startTime), array('ELT', $endTime), 'AND');
            $this->assign('create_time',$create_time);
        }
        if (!is_null($u_nickname)&&strlen($u_nickname)!=0){
            $condition['u.u_nickname'] = $u_nickname;
            $this->assign('u_nickname',$u_nickname);
        }
        if (!is_null($o_class)&&strlen($o_class)!=0){
            $condition['o.o_class'] = $o_class;
            $this->assign('o_class',$o_class);
        }
        $member = new Member();
        $consumptionHistory = $member->getConsumptionHistoryForAdmin($condition);
//        return json(['consumptionHistory' => $consumptionHistory,]);
        $this->assign([
            'consumptionHistory' => $consumptionHistory,
        ]);
        return $this->fetch();
    }



}
