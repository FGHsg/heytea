<?php

namespace app\common\model;

use think\Config;
use think\Db;
use think\File;
use think\Image;
use think\Model;
use app\common\utils\UeditorUtil;

class Goods extends Model
{
    protected $table = 'goods';
    protected $pk = 'g_id';

    /**
     * 查询商品列表
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoods()
    {
        $rs = $this->field('g_name,g_id')
            ->select();
        return $rs;
    }

    /**
     * 整理对应商品信息
     * @param $g_id
     * @return array|false|null|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsInfo($g_id){
       $goods = $this->find($g_id);
       if ($goods){
           $goods['g_attribute'] = json_decode($goods['g_attribute']);
           $goods['g_specifications'] = explode('-',$goods['g_specifications']);
           return $goods;
       }else{
           return null;
       }
    }

    public function getAttributeListForAdmin(){
        return Db::table('goods_attribute')->order('create_time','DESC')->paginate(10);
    }

    /**
     * 整理商品属性信息
     * @return array
     * @throws \think\exception\DbException
     */
    public function getGoodsAttributeInfo(){
        //商品属性处理
        $allGoodsAttribute = GoodsAttribute::all();
        $gaInfoList = array();
        foreach ($allGoodsAttribute as $ga){
            $gaInfo = null;
            $gaInfo['ga_id'] = $ga['ga_id'];
            $gaInfo['ga_name'] = $ga['ga_name'];
            $ga_class = explode('、',$ga['ga_class']);
            $gaInfo['ga_class'] = $ga_class;
            $gaInfoList[] = $gaInfo;
        }
        return $gaInfoList;
    }

    /**
     * 获取对应商品的商品规格
     * @param $g_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsSpecifications($g_id){
        $goods = $this->find($g_id);
        $g_specifications = $goods->g_specifications;
        $g_specifications = explode('-',$g_specifications);
        $gsInfo = array();
        foreach ($g_specifications as $gs){
            $gsInfo[] = Db::table('goods_specifications')->find($gs);
        }
        return $gsInfo;
    }


    /**
     * 获取对应商品的轮播图
     * @param $g_id
     * @return false|null|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsCarouselFigure($g_id){
        $goods = $this->find($g_id);
        if ($goods){
            $gcfList = Db::table('goods_carousel_figure')
                ->where('gcfg_id',$g_id)
                ->order('gcf_sort',"ASC")
                ->select();
            if ($gcfList==null){
                return null;
            }else{
                return $gcfList;
            }
        }else{
            return null;
        }

    }

    public function deleteImg($img){
        unlink(ROOT_PATH.DS.substr($img,21));
    }

    /**
     * 根据商品id查询商品名称
     * @param $g_id
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGnameByGid($g_id)
    {
        $rs = $this->where('g_id', 'eq', $g_id)
            ->field('g_name')
            ->find();
        return $rs;
    }


    /**
     * 保存商品图片
     * @param $g_name
     * @param $img
     * @param $table 图片类型标志：1-封面，2-缩略图
     * @return int|string
     */
    public function saveImg($g_name,File $img,$table){
        if ($table==1){$type = 'cover';}elseif ($table==2){$type = 'compression';}
//        $host = 'http://120.78.183.57/';
        $host = Config::get('hostip');
        $savePath = 'public/uploads/goods/';
        //设置限制条件，1M，类型为jpg或png
        $img->validate(['size'=>1024000,'ext'=>'jpg,png']);
        $imgComp = Image::open($img);
        $fileName = $g_name.'_'.$type.'_'.time().'.jpg';
        //图片处理压缩
        $imgComp->thumb(360,360)->save(ROOT_PATH.$savePath.$fileName,'jpg');
        if ($imgComp){
            return $host.$savePath.$fileName;
        }else{
            return false;
        }
    }

    /**
     * 保存商品轮播图
     * @param File $img
     * @return bool|string
     */
    public function saveCarouselFigure(File $img){
//        $host = 'http://120.78.183.57/';
        $host = Config::get('hostip');
        $savePath = 'public/uploads/goodsCarouselFigure/';
        //设置限制条件，1M，类型为jpg或png
        $img->validate(['size'=>1024000,'ext'=>'jpg,png']);
        $imgComp = Image::open($img);
        $fileName = time().'.jpg';
        //图片处理压缩
        $imgComp->thumb(360,360)->save(ROOT_PATH.$savePath.$fileName,'jpg');
        if ($imgComp){
            return $host.$savePath.$fileName;
        }else{
            return false;
        }
    }


    /**
     * 管理端获取商品列表
     * @param null $condition
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getGoodsListForAdmin($condition = null){
        if (is_array($condition) && count($condition) != 0) {
            $rs = Db::view('goods g', 'g_id,gc_id,g_name,g_point,g_cover,g_compression,g_sale,g_recommend,g_introduction,create_time')
                ->view('goods_class gc','gc_id,gc_name','gc.gc_id = g.gc_id')
//                ->view('goods_specifications gs','gs_id,gsg_id,g_rest','gs.gsg_id = g_id')
                ->where($condition)
                ->order('g.create_time', 'DESC')
                ->paginate(10);
        }else{
            $rs = Db::view('goods g', 'g_id,gc_id,g_name,g_point,g_cover,g_compression,g_sale,g_recommend,g_introduction,create_time')
                ->view('goods_class gc','gc_id,gc_name','gc.gc_id = g.gc_id')
//                ->view('goods_specifications gs','gs_id,gsg_id,g_rest','gs.gsg_id = g_id')
                ->order('g.create_time', 'DESC')
                ->paginate(10);
        }
        return $rs;
    }

    /**
     * 获取商品售罄列表
     * @return \think\Paginator
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSoldoutListForAdmin(){
        $goods = Goods::all();
        $g_id_list = array();
        foreach ($goods as $good){
            $g_id = $good->g_id;
            $gsList = Db::table('goods_specifications')->where('gsg_id',$g_id)->select();
            $restResult = 0;
            foreach ($gsList as $gs){
                if ($gs['g_rest']==-1){
                    $restResult = -1;
                }
            }
            if ($restResult!=-1){
                $restResult = Db::table('goods_specifications')->where('gsg_id',$g_id)->sum('g_rest');
            }
            if ($restResult == 0){
                $g_id_list[] = $g_id;
            }
        }
//        return $g_id_list;
        return Db::view('goods g','g_id,gc_id,g_name,g_point,g_cover,g_compression,g_sale,
        g_recommend,g_introduction,g_attribute,g_specifications,create_time')
            ->view('goods_class gc','gc_id,gc_name','g.gc_id = gc.gc_id')
            ->where('g_id',array('in',$g_id_list))
            ->order('g.create_time','DESC')
            ->paginate(10);

    }


    /**
     * 根据g_id和number更新销售状况
     * @param $g_id
     * @param $number
     * @throws \think\Exception
     */
    public function incSalesVolume($g_id, $number){
        $this->where('g_id',$g_id)->setInc('g_sales_volume',$number);
    }


    public function incSalesVolumeByOid($o_id){
        $o_goods = json_decode(Db::table('order')->where('o_id',$o_id)->value('o_goods'));
        foreach ($o_goods as $og){
            $og_g_id = $og->g_id;
            $og_number = $og->number;
            $this->where('g_id',$og_g_id)->setInc('g_sales_volume',$og_number);
        }
    }

    /**
     * 传入订单id减少销量纪录
     * @param $o_id
     * @throws \think\Exception
     */
    public function decSalesVolumeByOid($o_id){
        $o_goods = json_decode(Db::table('order')->where('o_id',$o_id)->value('o_goods'));
        foreach ($o_goods as $og){
            $og_g_id = $og->g_id;
            $og_number = $og->number;
            $this->where('g_id',$og_g_id)->setDec('g_sales_volume',$og_number);
        }
    }

}

