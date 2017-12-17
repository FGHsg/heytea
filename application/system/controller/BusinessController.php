<?php

namespace app\system\controller;

use app\common\model\Business;
use think\Controller;
use think\Db;
use think\Request;

class BusinessController extends Auth {

    //设置店铺视图
    public function businessSetting(){
        $business = Business::all();

        $this->assign([
            'business' => $business,
        ]);
        return $this->fetch();
    }

    //修改店铺信息
    public function editBusinessInformation(){
        $business = Business::all();

        $this->assign([
            'business' => $business,
        ]);
        return $this->fetch();
    }





    /************************************以下为数据处理接口*******************************************************/


    public function setBusinessOperation(){

        $b_name = input('post.b_name');
        $b_address = input('post.b_address');
        $b_phone = input('post.b_phone');
        $b_rest = input('post.b_rest');
        $b_open = input('post.b_open');
        $b_close = input('post.b_close');
        $b_open_date = input('post.b_open_date');
        $b_minprice = input('post.b_minprice');
        $b_distribution = input('post.b_distribution');
        $b_lunchbox = input('post.b_lunchbox');
        $b_area = input('post.b_area');
        $b_autoorder = input('post.b_autoorder');
        $b_autoprint = input('post.b_autoprint');
        $b_voucher_number = input('post.b_voucher_number');
        $b_tea_min = input('post.b_tea_min');
        $b_tea_max = input('post.b_tea_max');
        $b_tea_begin = input('post.b_tea_begin');
        $b_tea_end = input('post.b_tea_end');


//        if (!is_null($b_name)&&strlen($b_name)!=0
//            &&!is_null($b_address)&&strlen($b_address)!=0
//            &&!is_null($b_open)&&strlen($b_open)!=0
//            &&!is_null($b_close)&&strlen($b_close)!=0
//            &&!is_null($b_tea_begin)&&strlen($b_tea_begin)!=0
//            &&!is_null($b_tea_end)&&strlen($b_tea_end)!=0){

        $businessData = [
            'b_name' => $b_name,
            'b_address' => $b_address,
            'b_phone' => $b_phone,
            'b_rest' => $b_rest,
            'b_open' => $b_open,
            'b_close' => $b_close,
            'b_open_date' => $b_open_date,
            'b_minprice' => $b_minprice,
            'b_distribution' => $b_distribution,
            'b_lunchbox' => $b_lunchbox,
            'b_area' => $b_area,
            'b_autoorder' => $b_autoorder,
            'b_autoprint' => $b_autoprint,
            'b_voucher_number' => $b_voucher_number,
            'b_tea_min' => $b_tea_min,
            'b_tea_max' => $b_tea_max,
            'b_tea_begin' => $b_tea_begin,
            'b_tea_end' => $b_tea_end,
        ];
        $businessModel = new Business();
        $business = $businessModel->find();
        if ($business){
            $b_id = $business->b_id;
            $business = $businessModel->update($businessData,['b_id'=>$b_id]);
        }else{
            $business = $businessModel->save($businessData);
        }

        if ($business['b_id']!=null){
            return redirect('@system/businessSetting')->with('successTs','商铺信息设置成功');
        }else{
            return redirect('@system/businessSetting')->with('errorTs','商铺信息设置失败');
        }

//        }else{
//            $resultSet['errorCode'] = 1001;
//            $resultSet['message'] = '参数错误';
//        }

//        return json($business);
    }


    public function editBusiness(){

        $b_id = input('post.b_id');
        $b_address = input('post.b_address');
        $b_phone = input('post.b_phone');
        $b_rest = input('post.b_rest');
        $b_open = input('post.b_open');
        $b_close = input('post.b_close');
        $b_open_date = input('post.b_open_date');
        $b_minprice = input('post.b_minprice');
        $b_distribution = input('post.b_distribution');
        $b_lunchbox = input('post.b_lunchbox');
        $b_area = input('post.b_area');
        $b_autoorder = input('post.b_autoorder');
        $b_autoprint = input('post.b_autoprint');
        $b_voucher_number = input('post.b_voucher_number');



        if (!is_null($b_id)&&strlen($b_id)!=0
            &&!is_null($b_address)&&strlen($b_address)!=0
            &&!is_null($b_open)&&strlen($b_open)!=0
            &&!is_null($b_close)&&strlen($b_close)!=0
            &&!is_null($b_minprice)&&strlen($b_minprice)!=0){

            $businessData = [
                'b_id' => $b_id,
                'b_address' => $b_address,
                'b_phone' => $b_phone,
                'b_rest' => $b_rest,
                'b_open' => $b_open,
                'b_close' => $b_close,
                'b_open_date' => $b_open_date,
                'b_minprice' => $b_minprice,
                'b_distribution' => $b_distribution,
                'b_lunchbox' => $b_lunchbox,
                'b_area' => $b_area,
                'b_autoorder' => $b_autoorder,
                'b_autoprint' => $b_autoprint,
                'b_voucher_number' => $b_voucher_number,
            ];

            $business = Business::update($businessData);
            if ($business){
                return redirect('@system/businessSetting')->with('successTs','商铺信息设置成功');
             }else{
                return redirect('@system/businessSetting')->with('errorTs','商铺信息设置失败');
            }
        }else{
            return redirect('@system/businessSetting')->with('errorTs','商铺信息设置失败,参数错误');
        }
    }





}
