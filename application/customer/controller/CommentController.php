<?php

namespace app\customer\controller;

use app\common\model\Comment;
use app\common\model\Goods;
use app\common\model\Member;
use app\common\model\User;
use think\Controller;
use think\Request;
use think\Db;
use think\Image;

class CommentController extends Controller{
    /**
     * 获取全部审核通过的茶评信息
     * @return array
     */
    public function commentListForCustomer()
    {
        $page = request()->param('page');
        $commentList = array();
        //判断参数是否合法
        if (!is_null($page) && strlen($page) != 0) {
            //查询当前页面需显示的审核通过的茶评（根据创建时间倒序排序）
            $commentModel = new Comment();
            $comments = $commentModel->getCommentListForCustomer($page);
            if (!empty($comments)) {
                $memberModel = new Member();
                $userModel = new User();
                foreach ($comments as $comment) {
                    //根据关联会员id查询用户id
                    $member = $memberModel->getMuopenidByMid($comment['cm_id']);
                    //根据用户id查询用户头像和昵称
                    $user = $userModel->getUavatarAndUnicknameByUopenid($member['mu_openid']);
                    $commentInfo = [
                        'c_id' => $comment['c_id'],
                        'c_score' => $comment['c_score'],
                        'c_content' => $comment['c_content'],
                        'create_time' => substr($comment['create_time'],5,5),
                        'c_img' => json_decode($comment['c_img']),
                        'cg_content' => $comment['cg_content'],
                        'u_avatar' => $user['u_avatar'],
                        'u_nickname' => $user['u_nickname']
                    ];
                    $commentList[] = $commentInfo;
                }
            }
            $result = [
                'comment' => $commentList,
                'errorCode' => 0
            ];
            return json($result);
        } else {
            //参数不合法，返回错误码
            $result = [
                'comment' => $commentList,
                'error_code'=>1001,
                'message' => '参数不合法'
            ];
            return json($result);
        }
    }

    /**
     * 获取当前用户发表的茶评信息
     * @return array
     */
    public function myCommentList()
    {
        //获取请求参数
        $page = request()->param('page');
        $m_id = request()->param('m_id');
        $commentList = array();
        //判断参数是否合法
        if (!is_null($page)
            && strlen($page) != 0
            && !is_null($m_id)
            && strlen($m_id) != 0) {
            //查询当前页面需要显示的茶评信息
            $commentModel = new Comment();
            $comments = $commentModel->getMyCommentListForCustomer($m_id, $page);
            if (!empty($comments)) {
                $memberModel = new Member();
                $userModel = new User();
                foreach ($comments as $comment) {
                    //根据关联会员id查询用户id
                    $member = $memberModel->getMuopenidByMid($comment['cm_id']);
                    //根据用户id查询用户头像和昵称
                    $user = $userModel->getUavatarAndUnicknameByUopenid($member['mu_openid']);
                    $commentInfo = [
                        'c_id' => $comment['c_id'],
                        'c_score' => $comment['c_score'],
                        'c_content' => $comment['c_content'],
                        'create_time' => substr($comment['create_time'],5,5),
                        'c_img' => json_decode($comment['c_img']),
                        'cg_content' => $comment['cg_content'],
                        'u_avatar' => $user['u_avatar'],
                        'u_nickname' => $user['u_nickname']
                    ];
                    $commentList[] = $commentInfo;
                }
            }
            $result = [
                'comment' => $commentList,
                'errorCode' => 0
            ];
            return json($result);
        } else {
            //参数不合法，返回错误码
            $result = [
                'comment' => $commentList,
                'errorCode' => '1001',
                'message' => '参数不合法'
            ];
            return json($result);
        }
    }

    /**
     * 新增茶评
     * @return array
     */
    public function addComment()
    {
        //获取参数
        $cg_content = request()->param('cg_content');
        $cm_id = request()->param('cm_id');
        $c_score = request()->param('c_score');
        $c_content = request()->param('c_content');
        $imgs = request()->file('c_img');
        //判断参数是否合法
        if (!is_null($cg_content)
            && strlen($cg_content) != 0
            && !is_null($cm_id)
            && strlen($cm_id) != 0
            && !is_null($c_score)
            && strlen($c_score) != 0
            && $c_score >= 1
            && $c_score <= 5
            && !is_null($c_content)
            && strlen($c_content) != 0) {
            $host = 'http://120.78.183.57/';
            $savePath = 'public/uploads/comment/';
            $c_img = array();
            //判断是否有图片上传
            if ($imgs) {
                $i = 1;
                //上传茶评图片
                foreach ($imgs as $img) {
                    //设置文件上传限制，允许上传的最大文件大小：10M，类型为jpg或png
                    $img->validate(['size' => 10485760, 'ext' => 'jpg,png']);
                    $imgComp = Image::open($img);
                    $fileName = md5(time()) . '-' . $i . '.jpg';
                    $imgUploadResult = $imgComp->thumb(360, 360)
                        ->save(ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'comment' . DS . $fileName, 'jpg');
                    if ($imgUploadResult) {
                        $c_img[] = $host . $savePath . $fileName;
                    }
                    $i++;
                }
            }
            //判断是否上传成功，成功则新增数据，失败则返回错误码
            if (count($c_img) == count($imgs)) {
                //设置新增茶评的信息
                $commentData = [
                    'cg_content' => $cg_content,
                    'cm_id' => $cm_id,
                    'c_score' => $c_score,
                    'c_content' => $c_content,
                    'c_status' => 0,
                    'c_img' => json_encode($c_img)
                ];
                //创建comment的model对象
                $commentModel = new Comment();
                //判断新增是否成功
                if ($commentModel->create($commentData)) {
                    $result = [
                        'errorCode' => 0
                    ];
                } else {
                    //删除已上传的图片，返回错误信息
                    for ($i = 0; $i <= count($c_img); $i++) {
                        unlink(ROOT_PATH . DS . substr($c_img[$i], 21));
                    }
                    $result = [
                        'errorCode' => 1002,
                        'message' => '数据库异常'
                    ];
                }
            } else {
                //删除已上传的图片，返回错误信息
                for ($i = 0; $i <= count($c_img); $i++) {
                    unlink(ROOT_PATH . DS . substr($c_img[$i], 21));
                }
                $result = [
                    'errorCode' => 1007,
                    'message' => '文件上传失败'
                ];
            }
            return json($result);
        } else {
            //返回错误信息
            $result = [
                'errorCode' => 1001,
                'message' => '参数不合法'
            ];
            return json($result);
        }

    }

}
