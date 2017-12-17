<?php

namespace app\customer\controller;

use app\common\model\Business;
use think\Controller;
use think\Db;

class BusinessController extends Controller{

    /**
     * 获取店铺信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getBusinessInfo(){
        $businessInfo = Db::table('business')->field('b_id,b_address,b_phone,b_rest,b_open,b_close,b_open_date,
                                  b_minprice,b_distribution,b_lunchbox,b_area,b_tea_begin,b_tea_end')->select();
        return json($businessInfo);
    }




    public function getLocation(){
        $ip = input('ip');
        $ak = 'N1x06gjw7lXAmfv2r4FCH0ZYRsCnefky';
        $coor = 'bd09ll';
        $url = 'http://api.map.baidu.com/location/ip?ip='.$ip.'&ak='.$ak.'&coor='.$coor;


    }

}
