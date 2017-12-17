<?php

namespace app\system\controller;

use app\common\model\Comment;
use app\common\model\Goods;
use app\common\model\Member;
use app\common\model\User;
use think\Controller;
use think\Request;
use think\Db;
use think\Image;

class CommentController extends Auth
{
    /**
     * 获取全部茶评信息
     * @return array
     */
    public function commentListForAdmin()
    {
        //接收筛选条件
        $cg_content = request()->param('cg_content');
        $c_status = request()->param('c_status');
        $create_time = request()->param('create_time');
        $u_nickname = request()->param('u_nickname');
        //拼接筛选条件数组
        $condition = array();
        if (!is_null($cg_content) && strlen($cg_content) != 0) {
            $condition['c.cg_content'] = array('like', '%'.$cg_content.'%');
            $this->assign('cg_content', $cg_content);
        }
        if (!is_null($c_status) && strlen($c_status) != 0) {
            $condition['c.c_status'] = $c_status;
            $this->assign('c_status', $c_status);
        }
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
        //查询符合条件的所有茶评数据
        $commentModel = new Comment();
        $comments = $commentModel->getCommentListForAdmin($condition);
        foreach ($comments as $comment) {
            $comment['c_img'] = json_decode($comment['c_img']);
        }
        $this->assign('commentList', $comments);
        //查询所有商品数据
        $goodsModel = new Goods();
        $goods = $goodsModel->getGoods();
        $this->assign('goodsList', $goods);
        return $this->fetch();
    }

    /**
     * 修改茶评状态
     * @return array
     */
    public function updateCommentStatus()
    {
        //获取请求参数
        $c_id = request()->param('c_id');
        $label = request()->param('label');
        //判断参数是否合法
        if (!is_null($c_id)
            && strlen($c_id) != 0
            && !is_null($label)
            && strlen($label) != 0
            && ($label == 0 || $label == 1)) {
            //根据茶评id查询是否存在该茶评信息，存在则修改，不存在则返回错误代码
            $commentInfo = Db::table('comment')
                ->find($c_id);
            if ($commentInfo) {
                $comment = new Comment();
                $comment->save([
                    'c_status' => $label
                ],['c_id' => $c_id]);
                return redirect('@system/commentListForAdmin')->with('successTs', '修改茶评状态成功');
            } else {
                return redirect('@system/commentListForAdmin')->with('errorTs', '修改茶评状态失败');
            }
        } else {
            //参数不合法，返回错误信息
            return redirect('@system/commentListForAdmin')->with('errorTs', '参数不合法，修改失败');
        }
    }


}
