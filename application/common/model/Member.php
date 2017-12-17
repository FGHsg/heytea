<?php

namespace app\common\model;

use think\Db;
use think\Model;

class Member extends Model
{
    protected $table = 'member';
    protected $pk ='m_id';

    /**
     * 根据会员积分获取会员等级
     * @param $m_point
     * @return mixed
     */
    public function getGidForMemberByPoint($m_point){
        $g_rank = Db::table('grade')->where('g_point','>=',$m_point)->min('g_rank');
        $g_id = Db::table('grade')->where('g_rank',$g_rank)->find()['g_id'];
        return $g_id;
    }


    /**
     * 更新会员状态
     */
    public function refreshMemberInfoStatus(){
        $m_id = input('m_id');
        $member = $this->find($m_id);
        //更新会员等级
        $m_point = $member->m_point;
        $g_rank = Db::table('grade')->where('g_point','<=',$m_point)->max('g_rank');
        $g_id = Grade::get(['g_rank'=>$g_rank])['g_id'];
        $this->save(['mg_id'=>$g_id],['m_id'=>$m_id]);
    }

    /**
     * 获取用户信息
     * @param $m_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMemberInfo($m_id){
        $member = $this::get($m_id);
        $grade = Db::table('grade')->where('g_id',$member['mg_id'])->find();
        $g_name = $grade['g_name'];
        $g_rank = $grade['g_rank'];
        $privilege = Db::table('privilege')->order('pg_rank','ASC')->select();
//        $privilege = Db::table('privilege')->where('pg_rank','<',$g_rank)->order('g_rank','ASC')->select();
        $count = Db::table('coupon')->where(['cm_id'=>$m_id,'c_status'=>0])->count();
        $responseData = [
            'm_id' => $m_id,
            'm_vipcode' => $member['m_vipcode'],
            'm_vipcodeurl' => $member['m_vipcodeurl'],
            'm_phone' => $member['m_phone'],
            'm_point' => $member['m_point'],
            'mg_id' => $member['mg_id'],
            'm_tea_point' => $member['m_tea_point'],
            'g_name' => $g_name,
            'g_rank' => $g_rank,
            'privilege' => $privilege,
            'coupon_count' => $count,
        ];
        return $responseData;
    }

    /**
     * 根据会员id查询用户的openid
     * @param $m_id
     * @return array|false|\PDOStatement|string|Model
     */
    public function getMuopenidByMid($m_id)
    {
        $rs = $this->where('m_id', $m_id)
            ->field('mu_openid')
            ->find();
        return $rs;
    }

    /**
     * 根据会员id增加会员的茶叶数量
     * @param $m_id
     * @param $teaLeaves
     * @return int|true
     */
    public function increaseMTeaPointByMid($m_id, $teaLeaves)
    {
        $rs = $this->where('m_id','eq',$m_id)
            ->setInc('m_tea_point', $teaLeaves);
        return $rs;
    }

    /**
     * 根据会员id减小会员的茶叶数量
     * @param $m_id
     * @param $teaLeaves
     * @return int|true
     */
    public function decreaseMTeaPointByMid($m_id, $teaLeaves)
    {
        $rs = $this->where('m_id', 'eq', $m_id)
            ->setDec('m_tea_point', $teaLeaves);
        return $rs;
    }

    /**
     * 根据会员id查询会员的茶叶数量
     * @param $m_id
     * @return array|false|\PDOStatement|string|Model
     */
    public function getMTeaPointByMid($m_id)
    {
        $rs = $this->where('m_id', 'eq', $m_id)
            ->field('m_tea_point')
            ->find();
        return $rs;
    }

    /**
     * 根据用户昵称查询用户会员id
     * @param $u_nickname
     * @return array|false|\PDOStatement|string|Model
     */
    public function getMidByUNickname($u_nickname)
    {
        $rs = Db::view('user')
            ->view('member', 'm_id', 'member.mu_openid = user.u_openid')
            ->where('user.u_nickname', 'eq', $u_nickname)
            ->find();
        return $rs;
    }

    /**
     * 管理端获取会员信息列表
     * @param null $condition
     * @return \think\Paginator
     *
     */
    public function getMemberListForAdmin($condition=null){
        if (is_array($condition) && count($condition)!= 0) {
            $rs = Db::view('member m', 'm_id,m_phone,m_tea_point,m_point,m_vipcode')
                ->view('user u', 'u_nickname','u.u_openid=m.mu_openid')
                ->view('grade g','g_name','g.g_id=m.mg_id')
                ->where($condition)
                ->order('m.create_time', 'DESC')
                ->paginate(10);
        }else{
            $rs = Db::view('member m', 'm_id,m_phone,m_tea_point,m_point,m_vipcode')
                ->view('user u', 'u_nickname','u.u_openid=m.mu_openid')
                ->view('grade g','g_name','g.g_id=m.mg_id')
                ->order('m.create_time', 'DESC')
                ->paginate(10);
        }
        return $rs;
    }

    /**
     * 管理端获取会员地址信息列表
     * @param null $condition
     * @return \think\Paginator
     */
    public function getAddressListForAdmin($condition=null){
        if (is_array($condition) && count($condition)!= 0) {
            $rs = Db::view('address a','a_id,am_id,a_receiver,a_phone,a_details,create_time')
                ->view('member m','mu_openid','m.m_id = a.am_id')
                ->view('user u','u_nickname','u.u_openid = m.mu_openid')
                ->where($condition)
                ->paginate(10);
        }else{
            $rs = Db::view('address a','a_id,am_id,a_receiver,a_phone,a_details,create_time')
                ->view('member m','mu_openid','m.m_id = a.am_id')
                ->view('user u','u_nickname','u.u_openid = m.mu_openid')
                ->paginate(10);
        }
        return $rs;
    }


    /**
     * 管理端获取会员等级列表
     * @param null $condition
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getGradeListForAdmin($condition = null){
        if (is_array($condition) && count($condition)!= 0) {
            $rs = Db::table('grade')->where($condition)->paginate(10);
        }else{
            $rs = Db::table('grade')->paginate(10);
        }
        return $rs;
    }

    public function getPrivilegeForAdmin(){
        $rs = Db::view('privilege p','p_id,p_name,pg_id,p_introduction,p_img,create_time')
            ->view('grade g','g_id,g_name','g.g_id = p.pg_id')->paginate(10);

        return $rs;
    }

    public function getPrivilegeByPidForAdmin($p_id){
        return Db::table('privilege')->find($p_id);
    }

    public function getConsumptionHistoryForAdmin($condition=null){
        if (is_array($condition) && count($condition)!= 0) {
            $rs = Db::view('order o','o_id,om_id,o_goods,o_sum,o_point,o_class,create_time')
                ->view('member m','m_id,mu_openid,m_vipcode','m.m_id = o.om_id')
                ->view('user u','u_nickname','u.u_openid = m.mu_openid ')
                ->where($condition)
                ->paginate(10);
        }else{
            $rs = Db::view('order o','o_id,om_id,o_goods,o_sum,o_point,o_class,create_time')
                ->view('member m','m_id,mu_openid,m_vipcode','m.m_id = o.om_id')
                ->view('user u','u_nickname','u.u_openid = m.mu_openid ')
                ->paginate(10);
        }
        return $rs;
    }



}
