<?php

namespace app\system\controller;

use app\common\model\Goods;
use app\common\model\GoodsCoupon;
use app\common\model\GoodsSpecifications;
use app\common\model\Order;
use think\Controller;
use think\Db;
use think\Request;

class GoodsCouponController extends Auth
{
    /**
     * 获取兑换设置列表
     * @return mixed
     */
    public function goodsCouponList()
    {
        //获取请求参数
        $g_id = request()->param('g_id');
        $gc_cost = request()->param('gc_cost');
        //拼接筛选条件
        $condition = array();
        if (!is_null($g_id) && strlen($g_id) != 0) {
            $condition['gc.gcg_id'] = $g_id;
            $this->assign('g_id', $g_id);
        }
        if (!is_null($gc_cost) && strlen($gc_cost) != 0) {
            $condition['gc.gc_cost'] = $gc_cost;
            $this->assign('gc_cost', $gc_cost);
        }
        //查询兑换设置列表（分页）
        $goodsCouponModel = new GoodsCoupon();
        $goodsCouponList = $goodsCouponModel->getGoodsCouponForAdmin($condition);
        $this->assign('goodsCouponList', $goodsCouponList);
        //查询商品列表
        $goodsModel = new Goods();
        $goodsSpecificationsModel = new GoodsSpecifications();
        $goodsList = $goodsModel->getGoods();
        foreach ($goodsList as $goods) {
            //根据商品id查询商品的规格
            $goodsSpecifications = $goodsSpecificationsModel->getGoodsSpecificationsByGsgid($goods['g_id']);
            $goods['goodsSpecifications'] = $goodsSpecifications;
        }
        $this->assign('goodsList', $goodsList);
        return $this->fetch();
    }

    /**
     * 添加兑换券
     * @return $this|string
     */
    public function addGoodsCoupon()
    {
        //获取请求参数
        $gcg_id = request()->param('gcg_id');
        $gcgs_specifications_id = request()->param('gcgs_specifications_id');
        $gc_cost = request()->param('gc_cost');
        //判断参数是否合法
        if (!is_null($gcg_id)
            && strlen($gcg_id) != 0
            && !is_null($gcgs_specifications_id)
            && strlen($gcgs_specifications_id) != 0
            && !is_null($gc_cost)
            && strlen($gc_cost) != 0) {
            //判断该商品与商品规格是否对应
            $goodsSpecificationsModel = new GoodsSpecifications();
            if ($goodsSpecificationsModel->getGoodsSpecificationsByGsgidAndGsid($gcgs_specifications_id, $gcg_id)) {
                //添加兑换券信息
                $goodsCouponModel = new GoodsCoupon();
                $data = [
                    'gcg_id' => $gcg_id,
                    'gcgs_specifications_id' => $gcgs_specifications_id,
                    'gc_cost' => $gc_cost
                ];
                if ($goodsCouponModel->create($data)) {
                    //添加成功，返回成功提示
                    return redirect('@system/goodsCouponList')->with('successTs', '添加成功');
                } else {
                    //添加失败，返回错误提示
                    return redirect('@system/goodsCouponList')->with('errorTs', '添加失败');
                }
            } else {
                return redirect('@system/goodsCouponList')->with('errorTs', '添加失败，商品与商品规格不对应');
            }
        } else {
            //参数不合法，返回错误提示
            return redirect('@system/goodsCouponList')->with('errorTs', '添加失败，参数不合法');
        }
    }

    /**
     * 修改兑换券
     * @return $this
     */
    public function updateGoodsCoupon()
    {
        //获取请求参数
        $gc_id = request()->param('gc_id');
        $gcg_id = request()->param('gcg_id');
        $gcgs_specifications_id = request()->param('gcgs_specifications_id');
        $gc_cost = request()->param('gc_cost');
        //判断参数是否合法
        if (!is_null($gc_id)
            && strlen($gc_id) != 0
            && !is_null($gcg_id)
            && strlen($gcg_id) != 0
            && !is_null($gcgs_specifications_id)
            && strlen($gcgs_specifications_id) != 0
            && !is_null($gc_cost)
            && strlen($gc_cost) != 0) {
            //判断该商品与商品规格是否对应
            $goodsSpecificationsModel = new GoodsSpecifications();
            if ($goodsSpecificationsModel->getGoodsSpecificationsByGsgidAndGsid($gcgs_specifications_id, $gcg_id)) {
                //修改兑换券信息
                $goodsCouponModel = new GoodsCoupon();
                $data = [
                    'gcg_id' => $gcg_id,
                    'gcgs_specifications_id' => $gcgs_specifications_id,
                    'gc_cost' => $gc_cost
                ];
                if ($goodsCouponModel->save($data, ['gc_id' => $gc_id])) {
                    //修改成功，返回成功提示
                    return redirect('@system/goodsCouponList')->with('successTs', '修改成功');
                } else {
                    //修改失败，返回错误提示
                    return redirect('@system/goodsCouponList')->with('errorTs', '修改失败');
                }
            } else {
                //商品与商品规格不对应，返回错误提示
                return redirect('@system/goodsCouponList')->with('errorTs', '修改失败，商品与商品规格不对应');
            }
        } else {
            //参数不合法，返回错误提示
            return redirect('@system/goodsCouponList')->with('errorTs', '修改失败，参数不合法');
        }
    }

    /**
     * 删除兑换券
     * @return $this
     */
    public function deleteGoodsCoupon()
    {
        //获取请求参数
        $gc_id = request()->param('gc_id');
        //判断参数是否合法
        if (!is_null($gc_id) && strlen($gc_id) != 0) {
            //删除兑换券
            $goodsCouponModel = new GoodsCoupon();
            $goodsCoupon = $goodsCouponModel->get($gc_id);
            if ($goodsCoupon) {
                $goodsCoupon->delete();
                //删除成功，返回成功提示
                return redirect('@system/goodsCouponList')->with('successTs', '修改成功');
            } else {
                //删除失败，返回错误提示
                return redirect('@system/goodsCouponList')->with('errorTs', '删除失败');
            }
        } else {
            //参数不合法，返回错误提示
            return redirect('@system/goodsCouponList')->with('errorTs', '删除失败，参数不合法');
        }
    }


}
