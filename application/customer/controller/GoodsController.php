<?php

namespace app\customer\controller;

use app\common\model\Goods;
use app\common\model\GoodsAttribute;
use app\common\model\GoodsCarouselFigure;
use app\common\model\GoodsClass;
use app\common\model\GoodsSpecifications;
use think\Controller;
use think\Db;

class GoodsController extends Controller{

    //获取商品列表（带商品分类）
    public function getGoodsList(){
        $goodsModel = new Goods();
        $goodsClassModel = new GoodsClass();
        $goodsAttributeModel = new GoodsAttribute();
        $goodsSpecificationsModel = new GoodsSpecifications();
        //商品分类
        $goodClassList = $goodsClassModel->order('gc_id', ' ')->select();
        //商品信息
        $allGoodsList = $goodsModel->where('g_sale',array('in','1'))
                              ->field(['g_id','gc_id','g_name','g_point','g_cover','g_compression','g_attribute','g_introduction','g_specifications','g_sales_volume'])
                              ->order('create_time', 'DESC')
                              ->select();
        //商品规格
        $allGoodsSpecifications = GoodsSpecifications::all();
        //商品属性处理
        $gaInfoList = $goodsModel->getGoodsAttributeInfo();
        //轮播图处理
        $allGoodsCarouselFigure = GoodsCarouselFigure::all();
        //整理商品属性置入goods中
        foreach ($allGoodsList as $goods){
            $goods['g_attribute'] = json_decode($goods['g_attribute']);
            $goods['g_introduction'] = html_entity_decode($goods['g_introduction']);
            $gaInfo = array();
            foreach ($goods['g_attribute'] as $ga){
                $ga_id = $ga->a_id;
                $value = $ga->value;
                $goodsAttribute = $goodsAttributeModel->find($ga_id);
                $value_result = array();
                for($i=0;$i<=strlen($value)-1;$i++){
                    $value_result[]=$value[$i];
                }
                $ga_class = explode('、',$goodsAttribute['ga_class']);
                $ga_class_result = array();
                $i = 0;
                foreach ($value_result as $v){
                    if ($v==1){
                        $ga_class_result[] = $ga_class[$i];
                    }
                    $i++;
                }
                $gaInfo[] = [
                    'ga_id' => $ga_id,
                    'ga_name' => $goodsAttribute['ga_name'],
                    'ga_class' => $ga_class_result,
                ];
            }
            $goods['g_attribute'] = $gaInfo;
        }
        //整理商品规格置入goods中
        foreach ($allGoodsList as $goods){
            $g_specifications = explode('-',$goods['g_specifications']);
            $gsInfo = array();
            foreach ($g_specifications as $gs){
                $goodsSpecifications = $goodsSpecificationsModel->find($gs);
                if ($goodsSpecifications){
                    $gsInfo[] = $goodsSpecifications;
                }
            }
            $goods['g_specifications'] = $gsInfo;
        }

        foreach ($allGoodsList as $goods){
            $g_id = $goods['g_id'];
            $goods['g_carousel_figure'] = Db::table('goods_carousel_figure')->where('gcfg_id',$g_id)->order('gcf_sort',"ASC")->select();
        }
        if ($allGoodsList==null){
            $resultSet['errorCode'] = 0;
            $resultSet['message'] = '无可出售商品';
        }else{
            $resultSet['goods_class'] = $goodClassList;
            $resultSet['goods'] = $allGoodsList;
//            $resultSet['goods_specifications'] = $allGoodsSpecifications;
//            $resultSet['goods_attribute'] = $gaInfoList;
//            $resultSet['goods_carousel_figure'] = $allGoodsCarouselFigure;
        }
        return json($resultSet);
    }


    /**
     * 获取商品分类列表
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function getGoodClassList(){
        $goodClasses = GoodsClass::all();
        $goodClassList = array();
        if (is_array($goodClasses)&&!empty($goodClasses)){
            foreach ($goodClasses as $goodClass){

                $goodClassInfo = [
                    'gc_id' => $goodClass['gc_id'],
                    'gc_name' => $goodClass['gc_name'],
                    'create_time' => $goodClass['create_time'],
                ];

                $goodClassList[] = $goodClassInfo;
            }
            $resultSet['errorCode'] = 0;
            $resultSet['goodClass'] = $goodClassList;
        }else{
            $resultSet['errorCode'] = 1003;
        }
        return json($resultSet);
    }

    /**
     * 获取商品信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsInfo(){
        $g_id = input('g_id');
        if (is_null($g_id)){
            $resultSet['errorCode'] = 1001;
        }else{
            $goodsModel = new Goods();
            $goodsSpecificationsModel = new GoodsSpecifications();
            $goods = $goodsModel::get($g_id);
            if ($goods){
                $g_name = Db::table('goods')->where('g_id',$g_id)->value('g_name');
                $goods['g_name'] = $g_name;
                $goods['g_carousel_figure'] = Db::table('goods_carousel_figure')->where('gcfg_id',$g_id)->order('gcf_sort',"ASC")->select();
                $gc_name = Db::table('goods_class')->where('gc_id',$goods['gc_id'])->value('gc_name');
                $goods['gc_name'] = $gc_name;

                $goods['g_attribute'] = $goodsModel->getGoodsAttributeInfo();
                $goods['g_carousel_figure'] = Db::table('goods_carousel_figure')->where('gcfg_id',$g_id)->order('gcf_sort',"ASC")->select();
                $g_specifications = explode('-',$goods['g_specifications']);
                $gsInfo = array();

                foreach ($g_specifications as $gs){
                    $goodsSpecifications = $goodsSpecificationsModel->find($gs);
                    if ($goodsSpecifications){
                        $gsInfo[] = $goodsSpecifications;
                    }
                }
                $resultSet['goods_attribute'] = $goodsModel->getGoodsAttributeInfo();
                $goods['g_specifications'] = $gsInfo;
                $resultSet['goods'] = $goods;
                $resultSet['errorCode'] = 0;
            }else{
                $resultSet['errorCode'] = 1003;
            }
        }
        return json($resultSet);
    }







}
