<?php

namespace app\customer\controller;

use app\common\model\CarouselFigure;
use think\Controller;
use think\Request;

class CarouselFigureController extends Controller
{
    /**
     * 获取首页轮播图信息
     * @return \think\response\Json
     */
    public function getCarouselFigure()
    {
        //查询状态为显示的轮播图
        $carouselFigureModel = new CarouselFigure();
        $carouselFigures = $carouselFigureModel->getCarouselFigureForCustomer();
        foreach ($carouselFigures as $carouselFigure) {
            if (is_null($carouselFigure['cf_href']) || strlen($carouselFigure['cf_href']) == 0) {
                $carouselFigure['cf_href'] = 'javascript:void(0)';
            }
        }
        return json($carouselFigures);
    }
}
