<?php

namespace app\common\model;

use think\Model;

class CarouselFigure extends Model
{
    protected $table = 'carousel_figure';
    protected $pk = 'cf_id';

    protected $autoWriteTimestamp = false;

    /**
     * 管理端获取首页轮播图列表
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getCarouselFigureListForAdmin()
    {
        $rs = $this->order('cf_sort', 'ASC')
            ->paginate(5);
        return $rs;
    }

    /**
     * 客户端获取首页轮播图列表
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getCarouselFigureForCustomer()
    {
        $rs = $this->where('cf_status', 'eq', 1)
            ->order('cf_sort', 'ASC')
            ->field('cf_sort,cf_href,cf_url')
            ->select();
        return $rs;
    }
}
