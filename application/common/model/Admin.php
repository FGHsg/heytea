<?php
namespace app\common\model;
use think\Model;

/**
 * 管理员模型类
 * @author liuwenwei
 *
 */
class Admin extends Model{
	//只读字段，写入以后就不允许被更新
	protected $readonly = ['id','admin_account','create_time'];
	
	/**
	 * 登录
	 * @param string $admin_account 管理员帐号
	 * @param string $admin_password 管理员密码
	 * @return 
	 */
	public function login($admin_account, $admin_password){
		$findRs = $this->where('admin_account',$admin_account)->find();
		if($findRs){
			if($findRs->admin_password == md5($admin_password)){
				if($findRs->status == 2){
					$this->error = '该管理员已被锁定，请联系上级管理员解锁';
					return -1;
				}else{
					return $findRs;
				}
			}else{
				$this->error = '管理员密码错误';
				return -1;
			}
		}else{
			$this->error = '管理员帐号不存在';
			return -1;
		}
	}
	
	/**
	 * 更新管理员信息
	 * @param integer $adminId 管理员id
	 * @param array $data 更新信息
	 * @return integer
	 */
	public function updateAdminInfo($adminId, $data){
		$rs = $this->where('id',$adminId)->update($data);
		return $rs;
	}
	
	/**
	 * 添加管理员
	 * @param array $data 管理员信息
	 * @return integer 添加成功返回自增id
	 */
	public function addAdmin($data){
		$findRs = $this->where('admin_account',$data['admin_account'])->find();
		if($findRs){
			$this->error = '管理员帐号已存在';
			return -1;
		}else{
			$data['admin_password'] = md5($data['admin_password']);
			$data['create_time'] = time();
			$rs = $this->allowField(true)->insertGetId($data);
			return $rs;
		}
	}
	
	/**
	 * 更新管理员信息
	 * @param array $arr 管理员信息
	 * @return mixed
	 */
	public function updateAdmin($arr){
		$findRs = $this->where('id',$arr['id'])->find();
		if($findRs){
			if(isset($arr['admin_password'])){//提交密码则修改密码
				$arr['admin_password'] = md5($arr['admin_password']);
			}
			if(!isset($arr['status'])){//未提交则是禁止
				$arr['status'] = 2;
			}
			$arr['update_time'] = time();
			$rs = $this->allowField(true)->where('id',$arr['id'])->update($arr);
			return $rs;
		}else{
			$this->error = '更新失败，管理员不存在';
			return -1;
		}
	}
	
	/**
	 * 删除管理员
	 * @param int $adminId 管理员id
	 * @return integer
	 */
	public function deleteAdminById($adminId){
		$rs = $this->where('id',$adminId)->delete();
		return $rs;
	}
	
	/**
	 * 获取管理员信息（过滤敏感信息）
	 * @param integer $adminId 管理员id
	 * @return array 返回管理员信息
	 */
	public function getAdminInfo($adminId){
		$rs = $this->field(['admin_password'],true)->where('id',$adminId)->find();
		return $rs;
	}
	
	/**
	 * 获取全部管理员（过滤敏感信息）
	 * @return array
	 */
	public function getAdminList(){
		$rs = $this->field(['admin_password'],true)->paginate();
		return $rs;
	}
	
	/**
	 * status读取器
	 */
	protected function getStatusDataAttr($value,$data){
		$statusData = [1 => '正常', 2 => '禁用'];
		return $statusData[$data['status']];
	}
	
}