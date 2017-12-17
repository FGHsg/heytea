<?php
namespace app\system\controller;
use app\common\model\Admin as AdminModel;
use think\Request;
use app\common\model\Role;
use app\common\model\AdminRole;
use app\common\utils\SystemConfig;

/**
 * 管理员类
 * @author liuwenwei
 *
 */
class Admin extends Auth{
	/**
	 * 管理员列表
	 */
	public function adminList(AdminModel $admin,AdminRole $adminRole){
		//管理员列表
		$adminList = $admin->getAdminList();
		$this->assign('adminList',$adminList);
		//全部管理员角色
		$adminRoleList = $adminRole->getAdminRoleList();
		$this->assign('adminRoleList',$adminRoleList);
		
		return $this->fetch();
	}
	
	/**
	 * 添加管理员
	 */
	public function addAdmin(Request $request,AdminModel $admin,Role $role,AdminRole $adminRole){
		//角色列表
		$roleList = $role->getAllRoleList();
		$this->assign('roleList',$roleList);
	
		if($request->isPost()){
			$data = input('post.');
			$role_ids = input('post.role_ids/a');//所属角色id
			$two_region_ids = input('post.two_region_ids/a');//管理二级区域id
			
			//数据回填
			$this->assign('data',$data);
			$this->assign('role_ids',$role_ids);
			//验证数据
			if(trim($data['admin_password']) == '' || trim($data['admin_confirm_password']) == ''){
				$this->assign('errorTs','密码不能为空');
				return $this->fetch();
			}else{
				if(trim($data['admin_password']) != trim($data['admin_confirm_password'])){
					$this->assign('errorTs','两次密码不相同');
					return $this->fetch();
				}
			}

			//保存用户
			unset($data['admin_confirm_password']);
			unset($data['role_ids']);
			unset($data['upload_admin_head']);
			$insertId = $admin->addAdmin($data);
			if($insertId == -1){
				$this->assign('errorTs',$admin->getError());
				return $this->fetch();
			}
			//保存管理员角色
			$saveAdminRole = $adminRole->addAdminRole($insertId, $role_ids);
			
			return redirect('@system/adminList')->with('successTs','添加管理员成功');
		}
	
		return $this->fetch();
	}
	
	/**
	 * 上传管理员头像
	 * @return json
	 */
	public function uploadAdminHead(Request $request){
		if($request->isPost()){
			//获取表单上传文件
			$file = $request->file('upload_admin_head');
			//移动头像文件到框架应用根目录/public/uploads/ 目录下，最大为5M（1024*1024*5）
			if($file){
				$vilidates = [
						'size' => 5242880,
						'ext' => 'jpg,png,gif',
						'type' => 'image/gif,image/jpeg,image/jpg,image/pjpeg,image/x-png,image/png'
				];
				$info = $file->validate($vilidates)->rule(function($file){
					return md5(time());
				})->move(ROOT_PATH.'public'.DS.'uploads'.DS.'adminHead');
				if($info){//移动文件文件成功
					$saveImageName = DS.'public'.DS.'uploads'.DS.'adminHead'.DS.$info->getSaveName();
					return json(['isok'=>true,'imgPath'=>$saveImageName]);
				}else{//移动文件失败
					$uploadError = $file->getError();
					return json(['isok'=>false,'msg'=>$uploadError]);
				}
			}
		}
	}
	
	/**
	 * 编辑管理员
	 */
	public function editAdmin(Request $request,AdminModel $admin,Role $role,AdminRole $adminRole,$adminId=null){
		//系统管理员只允许被自己修改
		$toEditAdmin = $admin->getAdminInfo($adminId);
		if(in_array($toEditAdmin->admin_account, SystemConfig::$passPrivilege)){
			if($this->adminId != $toEditAdmin->id){
				return redirect('@system/adminList')->with('errorTs','无权限修改系统管理员');
			}
		}
		
		$this->assign('data',$toEditAdmin);
		//角色列表
		$roleList = $role->getAllRoleList();
		$this->assign('roleList',$roleList);
		//该管理员角色
		$adminRoleList = $adminRole->getAdminRoleById($adminId);
		$this->assign('adminRoleList',$adminRoleList);
		
		//接收修改信息
		if($request->isPost()){
			$data = input('post.');
			$role_ids = input('post.role_ids/a');//所属角色id
			$two_region_ids = input('post.two_region_ids/a');//管理二级区域id
			
			//数据回填
			$this->assign('data',$data);
			$this->assign('role_ids',$role_ids);
			
			//密码留空表示不修改密码
			if(trim($data['admin_password']) == ''){
				unset($data['admin_password']);
			}else{//密码不为空则验证
				if(trim($data['admin_password']) != trim($data['admin_confirm_password'])){
					$this->assign('errorTs','两次密码不相同');
					return $this->fetch();
				}
			}

			unset($data['admin_confirm_password']);
			unset($data['role_ids']);
			unset($data['upload_admin_head']);

			//更新用户
			$result = $admin->updateAdmin($data);
			if($result == -1){
				$this->assign('errorTs',$admin->getError());
				return $this->fetch();
			}
			//更新管理员角色
			$saveAdminRole = $adminRole->addAdminRole($data['id'], $role_ids);
			
	
			return redirect('@system/adminList')->with('successTs','修改管理员成功');
		}
	
		return $this->fetch();
	}
	
	/**
	 * 删除管理员
	 */
	public function deleteAdmin(AdminModel $admin,AdminRole $adminRole,$adminId){
		//系统管理员不允许删除
		$toDeleteAdmin = $admin->getAdminInfo($adminId);
		if(in_array($toDeleteAdmin->admin_account, SystemConfig::$passPrivilege)){
			return redirect('@system/adminList')->with('errorTs','系统管理员不允许删除');
		}
	
		$deleteAdminRs = $admin->deleteAdminById($adminId);
		if($deleteAdminRs){
			$adminRole->deleteAdminRoleById($adminId);//删除管理员角色
			return redirect('@system/adminList')->with('successTs','删除管理员成功');
		}else{
			return redirect('@system/adminList')->with('errorTs','删除管理员失败');
		}
	}
}