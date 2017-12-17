<?php

namespace app\system\controller;

use app\common\model\CarouselFigure;
use think\Controller;
use think\Request;
use think\Image;

class CarouselFigureController extends Auth
{
    /**
     * 首页轮播图列表
     * @return mixed
     */
    public function carouselFigureList()
    {
        //获取首页轮播图列表
        $carouselFigureModel = new CarouselFigure();
        $carouselFigureList = $carouselFigureModel->getCarouselFigureListForAdmin();
        $this->assign('carouselFigureList', $carouselFigureList);
        return $this->fetch();
    }

    /**
     * 新增首页轮播图
     * @return $this
     */
    public function addCarouselFigure(Request $request)
    {
        if ($request->isPost()) {
            //获取请求参数
            $cf_title = input('cf_title');
            $cf_sort = input('cf_sort');
            $cf_href = input('cf_href');
            $cf_url = request()->file('cf_url');
            $cf_status = input('cf_status');
            $coord = json_decode(html_entity_decode(input('coord')));
            //判断参数是否合法
            if (!is_null($cf_title)
                && strlen($cf_title) != 0
                && !is_null($cf_sort)
                && strlen($cf_sort) != 0
                && !is_null($cf_url)
                && strlen($cf_url) != 0
                && !is_null($cf_status)
                && strlen($cf_status) != 0) {
                //保存上传的图片
                $host = 'http://120.78.183.57/';
                $savePath = 'public/uploads/carousel_figure/';
                $cf_url->validate(['size' => 10485760, 'ext' => 'jpg,png']);
                $imgComp = Image::open($cf_url);
                $fileName = md5(time()) . '.jpg';
                $imgUploadResult = $imgComp->crop($coord->width, $coord->height, $coord->x, $coord->y)->thumb(360, 360)
                    ->save(ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'carousel_figure' . DS . $fileName, 'jpg');
                //判断图片是否上传成功
                if ($imgUploadResult) {
                    //新增首页轮播图
                    $carouselFigureModel = new CarouselFigure();
                    $data = [
                        'cf_title' => $cf_title,
                        'cf_sort' => $cf_sort,
                        'cf_href' => $cf_href,
                        'cf_url' => $host . $savePath . $fileName,
                        'cf_status' => $cf_status
                    ];
                    if ($carouselFigureModel->create($data)) {
                        return redirect('@system/carouselFigureList')->with('successTs', '新增成功');
                    } else {
                        //新增失败，删除已上传图片，返回错误信息
                        unlink(ROOT_PATH . DS . $savePath . $fileName);
                        return redirect('@system/carouselFigureList')->with('errorTs', '新增失败');
                    }
                } else {
                    //图片上传失败，返回错误信息
                    return redirect('@system/carouselFigureList')->with('errorTs', '新增失败，图片上传错误');
                }
            } else {
                //参数不合法，返回错误提示
                return redirect('@system/carouselFigureList')->with('errorTs', '新增失败，参数不合法');
            }
        }
        return $this->fetch();
    }

    /**
     * 修改首页轮播图
     * @param Request $request
     * @return $this|mixed
     */
    public function updateCarouselFigure(Request $request)
    {
        //获取首页轮播图id
        $cf_id = input('cf_id');
        //根据首页轮播图id获取轮播图信息
        $carouselFigureModel = new CarouselFigure();
        $carouselFigure = $carouselFigureModel->get($cf_id);
        $this->assign('carouselFigure', $carouselFigure);
        //接收修改信息
        if ($request->isPost()) {
            //保存修改信息
            $cf_title = input('cf_title');
            $cf_sort = input('cf_sort');
            $cf_href = input('cf_href');
            $cf_url = request()->file('cf_url');
            $cf_status = input('cf_status');
            //判断参数是否合法
            if (!is_null($cf_title)
                && strlen($cf_title) != 0
                && !is_null($cf_sort)
                && strlen($cf_sort) != 0
                && !is_null($cf_status)
                && strlen($cf_status) != 0){
                //判断是否有上传图片
                if ($cf_url) {
                    //保存上传图片并删除原有图片
                    $host = 'http://120.78.183.57/';
                    $savePath = 'public/uploads/carousel_figure/';
                    $cf_url->validate(['size' => 10485760, 'ext' => 'jpg,png']);
                    $imgComp = Image::open($cf_url);
                    $fileName = md5(time()) . '.jpg';
                    $imgUploadResult = $imgComp->thumb(360, 360)
                        ->save(ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'carousel_figure' . DS . $fileName, 'jpg');
                    if ($imgUploadResult) {
                        unlink(ROOT_PATH . DS . substr($carouselFigure['cf_url'], 21));
                        $data = [
                            'cf_title' => $cf_title,
                            'cf_sort' => $cf_sort,
                            'cf_href' => $cf_href,
                            'cf_status' => $cf_status,
                            'cf_url' => $host . $savePath . $fileName
                        ];
                    } else {
                        //文件上传失败，返回错误信息
                        $this->assign('errorTs', '修改失败，文件上传错误');
                        return $this->fetch();
                    }
                } else {
                    $data = [
                        'cf_id' => $cf_id,
                        'cf_title' => $cf_title,
                        'cf_sort' => $cf_sort,
                        'cf_href' => $cf_href,
                        'cf_status' => $cf_status
                    ];
                }
                if ($carouselFigureModel->update($data, ['cf_id' => $cf_id])) {
                    return redirect('@system/carouselFigureList')->with('successTs', '修改成功');

                } else {
                    $this->assign('errorTs', '修改失败');
                }
            } else {
                $this->assign('errorTs', '修改失败，参数不合法');
            }
        }
        return $this->fetch();
    }

    /**
     * 删除首页轮播图
     * @return $this
     */
    public function deleteCarouselFigure()
    {
        //获取请求参数
        $cf_id = input('cf_id');
        //判断参数是否合法
        if (!is_null($cf_id) && strlen($cf_id)) {
            //根据首页轮播图id获取数据
            $carouselFigureModel = new CarouselFigure();
            $carouselFigure = $carouselFigureModel->get($cf_id);
            if ($carouselFigure) {
                //进行删除操作
                if ($carouselFigure->delete()) {
                    //删除首页轮播图图片
                    unlink(ROOT_PATH . DS . substr($carouselFigure['cf_url'], 21));
                    //删除成功，返回成功提示
                    return redirect('@system/carouselFigureList')->with('successTs', '删除成功');
                } else {
                    //删除失败，返回错误提示
                    return redirect('@system/carouselFigureList')->with('errorTs', '删除失败');
                }
            } else {
                //该首页轮播图不存在，返回错误提示
                return redirect('@system/carouselFigureList')->with('errorTs', '删除失败，该首页轮播图不存在');
            }
        } else {
            //参数不合法，返回错误提示
            return redirect('@system/carouselFigureList')->with('errorTs', '删除失败，参数不合法');
    }
    }
}
