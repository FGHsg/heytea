<?php

namespace app\system\controller;

use app\common\model\Goods;
use app\common\model\GoodsAttribute;
use app\common\model\GoodsCarouselFigure;
use app\common\model\GoodsClass;
use app\common\model\GoodsSpecifications;
use think\Controller;
use think\Db;
use think\Response;
use think\Session;

class GoodsController extends Auth {

    //商品分类
    public function goodsClass(){
        $goodClassList = Db::table('goods_class')->paginate(10);
        $this->assign([
            'goods_class' => $goodClassList,
        ]);
        return $this->fetch();
    }

    /**
     * 添加商品分类
     * @return \think\response\Redirect
     */
    public function addGoodsClass(){
        $gc_name = input('gc_name');
        if (!is_null($gc_name)&&strlen($gc_name)!=0){
            $goodsClassModel = new GoodsClass();
            $goodsClass = $goodsClassModel::create(['gc_name'=>$gc_name]);
            if ($goodsClass){
                return redirect('@system/goodsClass', ['successTs' => '商品分类添加成功']);
            }else{
                return redirect('@system/goodsClass', ['errorTs' => '商品分类添加失败']);
            }
        }else{
            //参数不合法，返回错误信息
            return redirect('@system/goodsClass', ['errorTs' => '参数不合法，修改失败']);
        }
    }

    /**
     * 修改商品分类
     * @return \think\response\Redirect
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editGoodsClass(){
        $gc_id = request()->param('gc_id');
        $gc_name_next = request()->param('gc_name_next');
        if (!is_null($gc_id)&&strlen($gc_id)!=0
            &&!is_null($gc_name_next)&&strlen($gc_name_next)!=0){
            $goods_class = Db::table('goods_class')->find($gc_id);
            if ($goods_class){
                $gc = new GoodsClass();
                $gc->save([
                    'gc_name' => $gc_name_next,
                ],['gc_id' => $gc_id]);
                return redirect('@system/goodsClass', ['successTs' => '商品分类修改成功']);
            }else{
                return redirect('@system/goodsClass', ['errorTs' => $goods_class->getError()]);
            }
        }else{
            //参数不合法，返回错误信息
            return redirect('@system/goodsClass', ['errorTs' => '参数不合法，修改失败']);
        }
    }


    /**
     * 删除商品分类
     * @return \think\response\Redirect
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function deleteGoodsClass(){
        $gc_id = request()->param('gc_id');
        $goodsClassModel = new GoodsClass();
        $goodsClass = $goodsClassModel->where('gc_id',$gc_id)->find();
        if (!is_null($goodsClass)&&strlen($goodsClass)!=0){
            $goodsModel = new Goods();
            $goods = $goodsModel->where('gc_id',$gc_id)->select();
            if (!is_null($goods)){
                foreach ($goods as $good){
                    $goodsModel->where('g_id',$good['g_id'])->update(['gc_id'=>0]);
                }
                $goodsClass->delete();
                return redirect('@system/goodsClass', ['successTs' => '商品分类删除成功']);
            }else{
                $goodsClass->delete();
                return redirect('@system/goodsClass', ['successTs' => '商品分类删除成功']);
            }
        }else{
            //参数不合法，返回错误信息
            return redirect('@system/goodsClass', ['errorTs' => '参数不合法，修改失败']);
        }

    }




    //商品列表
    public function goodsList(){

        $create_time = input('create_time');
        $gc_name = input('gc_name');
        $g_name = input('g_name');
        $g_sale = input('g_sale');
        $g_recommend = input('g_recommend');
        //table:0-全部，1-售罄
        $table = input('table');
        $goodsModel = new Goods();
        //商品分类
        $goodsClassModel = new GoodsClass();
        $goodClassList = $goodsClassModel->order('gc_id', 'DESC')->select();
        if ($table==0){
            $condition = array();
            if (!is_null($create_time) && strlen($create_time) != 0) {
                $startTime = substr($create_time, 0, 10);
                $endTime = substr($create_time, 13);
                $condition['g.create_time'] = array(array('EGT', $startTime), array('ELT', $endTime), 'AND');
                $this->assign('create_time',$create_time);
            }
            if (!is_null($gc_name) && strlen($gc_name) != 0) {
                $condition['gc.gc_name'] = $gc_name;
                $this->assign('gc_name',$gc_name);
            }
            if (!is_null($g_name)&&strlen($g_name)!=0){
                $condition['g.g_name'] = $g_name;
                $this->assign('g_name',$g_name);
            }
            if (!is_null($g_sale)&&strlen($g_sale)!=0){
                $condition['g.g_sale'] = $g_sale;
                $this->assign('g_sale',$g_sale);
            }
            if (!is_null($g_recommend)&&strlen($g_recommend)!=0){
                $condition['g.g_recommend'] = $g_recommend;
                $this->assign('g_recommend',$g_recommend);
            }
            $goods = $goodsModel->getGoodsListForAdmin($condition);
            $g_rest_list = array();
            $theList = array();
            foreach ($goods as $g){
                //将规格中的总库存作为此商品的库存
                $gsList = Db::table('goods_specifications')->where('gsg_id',$g['g_id'])->select();
                $restResult = 0;
                foreach ($gsList as $gs){
                    if ($gs['g_rest']==-1){
                        $restResult = -1;
                    }
                }
                if ($restResult!=-1){
                    $restResult = Db::table('goods_specifications')->where('gsg_id',$g['g_id'])->sum('g_rest');
                }
                $theList['g_id'] = $g['g_id'];
                $theList['g_rest'] = $restResult;
                $g_rest_list[] = $theList;
            }
            $this->assign([
                'goods' => $goods,
                'goods_class' => $goodClassList,
                'g_rest' => $g_rest_list,
            ]);
        }elseif ($table==1){
            $goods = $goodsModel->getSoldoutListForAdmin();
//            return json([
//                'goods' => $goods,
//                'goods_class' => $goodClassList,
//            ]);
            $this->assign([
                'goods' => $goods,
                'goods_class' => $goodClassList,
            ]);
        }
        return $this->fetch();
    }

    //添加商品视图
    public function addGoodsView(){
        $goodsModel = new Goods();
        $goodClassList = GoodsClass::all();
        $gaInfoList = $goodsModel->getGoodsAttributeInfo();
        $this->assign([
            'goods_class' => $goodClassList,
            'goods_attribute' => $gaInfoList,
        ]);
        return $this->fetch();
    }

    //编辑商品视图
    public function editGoodsView(){
        $g_id = input('get.g_id');
        if (Db::table('goods')->find($g_id)){
            $goodsModel = new Goods();
            $goods = $goodsModel->getGoodsInfo($g_id);
            $goodClassList = GoodsClass::all();
            $ga = $goodsModel->getGoodsAttributeInfo();
            $gsInfo = $goodsModel->getGoodsSpecifications($g_id);
            $gcf = $goodsModel->getGoodsCarouselFigure($g_id);
            $this->assign([
                'goods' => $goods,
                'goods_class' => $goodClassList,
                'goods_attribute' => $ga,
                'goods_specifications' => $gsInfo,
                'goods_carousel_figure' =>$gcf,
            ]);
            return $this->fetch();
        }else{
            return redirect($this)->with('errorTs','传入参数错误');
        }
    }




    //商品详情
    public function goodsDetail(){
        $g_id = input('g_id');
        if (Db::table('goods')->find($g_id)){
            $goodsModel = new Goods();
            $goods = $goodsModel->getGoodsInfo($g_id);
            $goodClassList = GoodsClass::all();
            $ga = $goodsModel->getGoodsAttributeInfo();
            $gsInfo = $goodsModel->getGoodsSpecifications($g_id);
            $gcf = $goodsModel->getGoodsCarouselFigure($g_id);
            $this->assign([
                'goods' => $goods,
                'goods_class' => $goodClassList,
                'goods_attribute' => $ga,
                'goods_specifications' => $gsInfo,
                'goods_carousel_figure' =>$gcf,
            ]);
            return $this->fetch();
        }else{
            return redirect($this)->with('errorTs','传入参数错误');
        }
    }

    /**
     * 商品属性
     * @return mixed
     */
    public function goodsAttribute(){
        $goodsModel = new Goods();
        $goodsAttribute = $goodsModel->getAttributeListForAdmin();
        $this->assign([
            'goods_attribute' => $goodsAttribute,
        ]);
        return $this->fetch();
    }


    /**
     * 添加商品操作（先创建商品再创建规格）
     * @return $this
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function addGoodsOperation(){
        $gc_id = input('gc_id');
        $g_name = input('g_name');
        $g_point = input('g_point');
        $g_cover = request()->file('g_cover');
        $g_compression = request()->file('g_compression');
        $g_sale = input('g_sale');
        $g_recommend = input('g_recommend');
        $g_introduction = html_entity_decode(input('g_introduction'));
        $g_attribute = json_decode(html_entity_decode(input('g_attribute')));
        $goods_specification = json_decode(html_entity_decode(input('goods_specification')));
        $goods_carousel_figure = json_decode(html_entity_decode(input('goods_carousel_figure')));


        $label = input('label');

        $goodsModel = new Goods();
        $gcfModel = new GoodsCarouselFigure();
        $gsModel = new GoodsSpecifications();
        //事务成功标志:0-成功，1-失败
        $successLabel = 0;
        //开始事务
        $goodsModel->startTrans();
        //图片命名：“商品名_cover_时间戳”
        //“商品名_compression_时间戳”
        //保存并压缩图片
        if ($g_cover!=null){
            $g_cover = $goodsModel->saveImg($g_name,$g_cover,1);
        }
        if ($g_compression!=null){
            $g_compression = $goodsModel->saveImg($g_name,$g_compression,2);
        }

        //创建失败返回信息整理
        $goodsReturn = [
            'gc_id' => $gc_id,
            'g_name' => $g_name,
            'g_point' => $g_point,
            'g_cover' => $g_cover,
            'g_compression' => $g_compression,
            'g_sale' => $g_sale,
            'g_recommend' => $g_recommend,
            'g_introduction' => $g_introduction,
            'g_attribute' => $g_attribute,
        ];
        $goodClassList = GoodsClass::all();
//        $goods_carousel_figure = json_decode($goods_carousel_figure);
//        $goods_specification = json_decode($goods_specification);

        $this->assign([
            'table'=>1,
            'goods'=>$goodsReturn,
            'goods_class' => $goodClassList,
            'goods_attribute' => $goodsModel->getGoodsAttributeInfo(),
            'goods_specifications' => $goods_specification,
            'goods_carousel_figure' => $goods_carousel_figure,
            ]);
        $errMsg = null;
        //鉴权参数是否正确并重定向，回填数据
        if (strlen($gc_id)==0){
            $errMsg = '商品分类不能为空';
            Session::flash('errorTs',$errMsg);
            return $this->fetch('addGoodsView');
        }

        if (strlen($g_name)==0){
            $errMsg = '商品名不能为空';
            Session::flash('errorTs',$errMsg);
            return $this->fetch('addGoodsView');
        }
        if (floor($g_point)!=$g_point){
            $errMsg = '商品积分错误';
            Session::flash('errorTs',$errMsg);
            return $this->fetch('addGoodsView');
        }
        if ($g_cover==null){
            $errMsg = '商品封面不能为空';
            Session::flash('errorTs',$errMsg);
            return $this->fetch('addGoodsView');
        }
        if ($g_compression==null){
            $errMsg = '商品缩略图不能为空';
            Session::flash('errorTs',$errMsg);
            return $this->fetch('addGoodsView');
        }
        if ($goods_carousel_figure==null){
            $errMsg = '商品轮播图不能为空';
            Session::flash('errorTs',$errMsg);
            return $this->fetch('addGoodsView');
//            return redirect('@system/addGoodsView',$returnInfo)->with('errorTs',$errMsg);
        }
        if ($goods_specification==null){
            $errMsg = '商品规格不能为空';
            Session::flash('errorTs',$errMsg);
            return $this->fetch('addGoodsView');
        }
        if (!is_array($g_attribute)){
            $errMsg = '商品属性不能为空';
            Session::flash('errorTs',$errMsg);
            return $this->fetch('addGoodsView');
        }
        if ($g_introduction==null){
            $errMsg = '商品描述不能为空';
            Session::flash('errorTs',$errMsg);
            return $this->fetch('addGoodsView');
        }
        if ($g_sale==null){
            $errMsg = '请选择商品是否上架';
            Session::flash('errorTs',$errMsg);
            return $this->fetch('addGoodsView');
        }
        if (is_null($g_recommend)){
            $errMsg = '请选择商品是否推荐';
            Session::flash('errorTs',$errMsg);
            return $this->fetch('addGoodsView');
        }
        //判断该商品是否存在
        if (Db::table('goods')->where('g_name',$g_name)->find()==null){
            if($g_cover&&$g_compression){
                $goodsData = [
                    'gc_id' => $gc_id,
                    'g_name' => $g_name,
                    'g_point' => $g_point,
                    'g_cover' => $g_cover,
                    'g_compression' => $g_compression,
                    'g_sale' => $g_sale,
                    'g_recommend' => $g_recommend,
                    'g_introduction' => $g_introduction,
                    'g_attribute' => $g_attribute,
                ];

                $goodsInfo = $goodsModel::create($goodsData);
                $g_id = $goodsInfo['g_id'];

                //创建轮播图
                if (!is_null($goods_carousel_figure)){

                    foreach ($goods_carousel_figure as $gcf){
                        $gcfData = [
                            'gcfg_id' => $g_id,
                            'gcf_sort' => $gcf->gcf_sort,
                            'gcf_img' => $gcf->gcf_img,
                        ];
                        if (!$gcfModel::create($gcfData)){
                            $successLabel = 1;
                            $goodsModel->rollback();
                            $goodsModel->deleteImg($gcf->gcf_img);
                            $goodsModel->deleteImg($g_cover);
                            $goodsModel->deleteImg($g_compression);
                        }
                    }
                }
                //创建商品规格并更新商品对应字段
                if (!is_null($goods_specification)){
                    foreach ($goods_specification as $gs){
                        $gsData = [
                            'gsg_id' => $g_id,
                            'g_content' => $gs->g_content,
                            'g_price' => $gs->g_price,
                            'g_rest' => $gs->g_rest?$gs->g_rest:-1,   //若g_rest为空则默认为-1
                        ];
                        $goods_specifications = $gsModel::create($gsData);
                        if (!$goods_specifications){
                            $successLabel = 1;
                            $goodsModel->rollback();
                            $goodsModel->deleteImg($$g_cover);
                            $goodsModel->deleteImg($$g_compression);
                        }
                        $goods = $goodsModel->find($g_id);
                        if ($goods['g_specifications']){
                                $g_specifications = $goods['g_specifications'].'-'.$goods_specifications['gs_id'];
                        }else{
                            $g_specifications = $goods_specifications['gs_id'];
                        }
                        if (!$goodsModel->update([
                            'g_id'=>$g_id,
                            'g_specifications' => $g_specifications
                        ])){
                            $successLabel = 1;
                            $goodsModel->rollback();
                            $goodsModel->deleteImg($$g_cover);
                            $goodsModel->deleteImg($$g_compression);
                        }
                    }
                }
                $goodsModel->commit();
                if ($successLabel == 0){
                    if ($label==0){
                        return redirect('@system/goodsList')->with('successTs','商品创建成功');
                    }else if ($label==1){
                        return redirect('@system/addGoodsView')->with('successTs','商品创建成功');
                    }
                }else{
                    $errMsg = '商品创建失败,请联系管理人员';
                    Session::flash('errorTs',$errMsg);
                    return $this->fetch('addGoodsView');
                }
            }else{
                $errMsg = '商品创建失败，图片保存失败';
                Session::flash('errorTs',$errMsg);
                return $this->fetch('addGoodsView');
            }
        }else{
            $errMsg = '该商品名称已存在';
            Session::flash('errorTs',$errMsg);
            return $this->fetch('addGoodsView');
        }
    }

    /**
     * 保存商品轮播图
     * @return bool|string
     */
    public function saveGoodsCarouselFigure(){
        $imgs = request()->file('img');
        $goodsModel = new Goods();
        return $goodsModel->saveCarouselFigure($imgs);
    }

    /**
     * 保存商品你图片：1-封面，2-缩略图
     * @return int|string
     */
    public function saveGoodsImg(){
        $img = request()->file('img');
        $g_name = request()->param('g_name');
        $table = request()->param('table');
        $goodsModel = new Goods();
        return $goodsModel->saveImg($g_name,$img,$table);
    }

    /**
     * 编辑商品信息
     * @return $this
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function editGoodsOperation(){
        $g_id = input('g_id');

        $goodsModel = new Goods();
        $gsModel = new GoodsSpecifications();


        if (Db::table('goods')->where('g_id',$g_id)->find()['g_id']!=null){
            $gc_id = input('gc_id');
            $g_name = input('g_name');
            $g_point = input('g_point');
//            $g_cover = request()->file('g_cover');
//            $g_compression = request()->file('g_compression');
            $g_cover = input('g_cover');
            $g_compression = input('g_compression');
            $g_sale = input('g_sale');
            $g_recommend = input('g_recommend');
            $g_introduction = input('g_introduction');
            $g_attribute = html_entity_decode(input('g_attribute'));
            $goods_specification = json_decode(html_entity_decode(input('goods_specification')));
            $goods_carousel_figure = json_decode(html_entity_decode(input('goods_carousel_figure')));

            //创建失败返回信息整理
            $goodsReturn = [
                'g_id' => $g_id,
                'gc_id' => $gc_id,
                'g_name' => $g_name,
                'g_point' => $g_point,
                'g_cover' => $g_cover,
                'g_compression' => $g_compression,
                'g_sale' => $g_sale,
                'g_recommend' => $g_recommend,
                'g_introduction' => $g_introduction,
                'g_attribute' => $g_attribute,
            ];
            $goodClassList = GoodsClass::all();
            $goods_carousel_figure = json_decode($goods_carousel_figure);
            $goods_specification = json_decode($goods_specification);

            $this->assign([
                'table' => 1,
                'goods'=>$goodsReturn,
                'goods_class' => $goodClassList,
                'goods_attribute' => $goodsModel->getGoodsAttributeInfo(),
                'goods_specifications' => $goods_specification,
                'goods_carousel_figure' => $goods_carousel_figure,
            ]);
            $errMsg = null;
            //鉴权参数是否正确并重定向，回填数据
            if (strlen($gc_id)==0){
                $errMsg = '商品分类不能为空';
                Session::flash('errorTs',$errMsg);
                return $this->fetch('editGoodsView');
            }
            if (strlen($g_name)==0){
                $errMsg = '商品名不能为空';
                Session::flash('errorTs',$errMsg);
                return $this->fetch('editGoodsView');
            }
            if (floor($g_point)!=$g_point){
                $errMsg = '商品积分错误';
                Session::flash('errorTs',$errMsg);
                return $this->fetch('editGoodsView');
            }
            if ($g_cover==null){
                $errMsg = '商品封面不能为空';
                Session::flash('errorTs',$errMsg);
                return $this->fetch('editGoodsView');
            }
            if ($g_compression==null){
                $errMsg = '商品缩略图不能为空';
                Session::flash('errorTs',$errMsg);
                return $this->fetch('editGoodsView');
            }
            if ($goods_carousel_figure==null){
                $errMsg = '商品轮播图不能为空';
                Session::flash('errorTs',$errMsg);
                return $this->fetch('editGoodsView');
            }
            if ($goods_specification==null){
                $errMsg = '商品规格不能为空';
                Session::flash('errorTs',$errMsg);
                return $this->fetch('editGoodsView');
            }
            if (!is_array($g_attribute)){
                $errMsg = '商品属性不能为空';
                Session::flash('errorTs',$errMsg);
                return $this->fetch('editGoodsView');
            }
            if ($g_introduction==null){
                $errMsg = '商品描述不能为空';
                Session::flash('errorTs',$errMsg);
                return $this->fetch('editGoodsView');
            }
            if ($g_sale==null){
                $errMsg = '请选择商品是否上架';
                Session::flash('errorTs',$errMsg);
                return $this->fetch('editGoodsView');
            }
            if (is_null($g_recommend)){
                $errMsg = '请选择商品是否推荐';
                Session::flash('errorTs',$errMsg);
                return $this->fetch('editGoodsView');
            }
                $goodsData = [
                    'g_id' => $g_id,
                    'gc_id' => $gc_id,
                    'g_name' => $g_name,
                    'g_point' => $g_point,
                    'g_cover' => $g_cover,
                    'g_compression' => $g_compression,
                    'g_sale' => $g_sale,
                    'g_recommend' => $g_recommend,
                    'g_introduction' => $g_introduction,
                    'g_attribute' => $g_attribute,
                ];

                if (Goods::update($goodsData)){
                    $goodsModel->startTrans();
                    $goodsSuccess = 0;  //商品编辑成功标志：0-成功，1-失败
                    //创建轮播图
//                    $gcfSuccess = 0;    //商品轮播图成功标志：0-成功，1-失败
                    if (!is_null($goods_carousel_figure)){
                        $gcfModel = new GoodsCarouselFigure();
                        $gcfModel->startTrans();
                        foreach ($goods_carousel_figure as $gcf){
                            $gcfData = [
                                'gcfg_id' => $g_id,
                                'gcf_sort' => $gcf->gcf_sort,
                                'gcf_img' => $gcf->gcf_img,
                            ];
                            if (!$gcfModel::create($gcfData)){
                                $gcfSuccess = 1;
                                $goodsSuccess = 1;
                                $goodsModel->rollback();
                                $gcfModel->rollback();
//                                $goodsModel->deleteImg($gcf->gcf_img);
                            }
                        }
                    }
                    //轮播图创建失败删除图片
                    if ($gcfSuccess==1){
                        foreach ($goods_carousel_figure as $gcf){
                            $goodsModel->deleteImg($gcf->gcf_img);
                        }
                    }

                    //创建商品规格并更新商品对应字段
                    $gs_number = null;
                    $gsModel->startTrans();     //开启事务
                    //将原信息删除
                    $gsModel->where('gsg_id',$g_id)->delete();
                    foreach ($goods_specification as $gs){
                        $gsData = [
                            'gsg_id' => $g_id,
                            'g_content' => $gs->g_content,
                            'g_price' => $gs->g_price,
                            'g_rest' => $gs->g_rest,
                        ];
//                        $gsData['g_rest'] = $gs->g_rest?$gs->g_rest:-1;//若g_rest为空则默认为-1
                        $goods_specifications = $gsModel::create($gsData);
                        if (!$goods_specifications){
                            $goodsSuccess = 1;
                            $goodsModel->rollback();
                            $gsModel->rollback();
                        }
                        $goodsModel = $goodsModel->find($g_id);
                        if ($gs_number!=null){
                            $gs_number = $gs_number.'-'.$goods_specifications['gs_id'];
                        }else{
                            $gs_number = $goods_specifications['gs_id'];
                        }
                        if (!$goodsModel->update([
                            'g_id'=>$g_id,
                            'g_specifications' => $gs_number
                        ])){
                            $goodsSuccess = 1;
                            $goodsModel->rollback();
                            $gsModel->rollback();
                        }
                    }

                    $gcfModel->commit();
                    $gsModel->commit();
                    $goodsModel->commit();
                    if ($goodsSuccess==0){
                        return redirect('@system/goodsList')->with('successTs','商品信息修改成功');
                    }elseif ($goodsSuccess==1){
                        $errMsg = '商品信息修改失败，请联系管理员';
                        Session::flash('errorTs',$errMsg);
                        return $this->fetch('editGoodsView');
                    }

                }else{
//                    $goodsModel->rollback();
//                    $goodsModel->commit();
                    $errMsg = '商品信息修改失败，请联系管理员';
                    Session::flash('errorTs',$errMsg);
                    return $this->fetch('editGoodsView');
                }
        }else{
            $errMsg = '商品不存在';
            Session::flash('errorTs',$errMsg);
            return $this->fetch('editGoodsView');
        }
    }

    /**
     * 商品上下架操作
     * @param $g_id
     * @param $table 0-下架，1-上架
     * @return $this
     */
    public function grounding($g_id, $table){
        if (!is_null($g_id)&&!is_null($table)&&strlen($table)!=0
            &&($table==0||$table==1)){
            $g_id = json_decode(html_entity_decode($g_id));
            $goodsModel = new Goods();
            if (is_array($g_id)){
                foreach ($g_id as $g){
                    $goodsModel->update(['g_sale'=>$table],['g_id'=>$g]);
                }
            }else{
                $goodsModel->update(['g_sale'=>$table],['g_id'=>$g_id]);
            }
            return redirect('@system/goodsList')->with('successTs','商品状态修改成功');
        }else{
            return redirect('@system/goodsList')->with('errorTs','商品状态修改失败，参数错误');
        }
    }

    /**
     * 设置商品是否推荐
     * @param $g_id
     * @param $table，0-不推荐，1-推荐
     * @return $this
     */
    public function setRecommend($g_id, $table){
        if (!is_null($g_id)&&!is_null($table)){
            if ($table==0||$table==1){
                $g_id = json_decode(html_entity_decode($g_id));
                $goodsModel = new Goods();
                foreach ($g_id as $g){
                    $goodsModel->update(['g_recommend'=>$table],['g_id'=>$g]);
                }
                return redirect('@system/goodsList')->with('successTs','商品状态修改成功');
            }else{
                return redirect('@system/goodsList')->with('errorTs','商品状态修改失败，参数错误');
            }
        }else{
            return redirect('@system/goodsList')->with('errorTs','商品状态修改失败，参数错误');
        }
    }

    /**
     * 删除商品操作
     * @param $g_id
     * @return $this
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function deleteGoods($g_id){
        if (!is_null($g_id)&&strlen($g_id)!=0){
            $goodsModel = new Goods();
            if ($goods = $goodsModel->find($g_id)){
                $gsModel = new GoodsSpecifications();
                $gsModel->where('gsg_id',$g_id)->delete();
                $goods->delete();
                return redirect('@system/goodsList')->with('successTs','商品删除成功');
            }else{
                return redirect('@system/goodsList')->with('errorTs','商品删除失败，商品不存在');
            }
        }else{
            return redirect('@system/goodsList')->with('errorTs','商品删除失败，参数错误');
        }
    }




    /**
     * 添加商品属性
     * @param $ga_name
     * @param $ga_class
     * @return $this
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addGoodsAttributeOperation($ga_name,$ga_class){
        if (Db::table('goods_attribute')->where('ga_name',$ga_name)->find()!=null){
            return redirect('@system/goodsAttribute')->with('errorTs','商品属性添加失败，该属性已存在');
        }else{
            $ga = new GoodsAttribute();
            $gaDate = [
                'ga_name' => $ga_name,
                'ga_class' => $ga_class,
            ];
            $gaResult = $ga::create($gaDate);
            if ($gaResult){
                return redirect('@system/goodsAttribute')->with('successTs','商品属性添加成功');
            }else{
                return redirect('@system/goodsAttribute')->with('errorTs','商品属性添加失败');
            }
        }
    }

    /**
     * 修改商品属性
     * @param $ga_id
     * @param $ga_name
     * @param $ga_class
     * @return $this
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function editGoodsAttributeOperation($ga_id,$ga_name,$ga_class){
        $ga = Db::table('goods_attribute')->where('ga_id',$ga_id)->find();
        if ($ga==null){
            return redirect('@system/goodsAttribute')->with('errorTs','商品属性修改失败，该属性不存在');
        }else{
            $gaDate = [
                'ga_id' => $ga_id,
                'ga_name' => $ga_name,
                'ga_class' => $ga_class,
            ];
            Db::table('goods_attribute')->update($gaDate);
            return redirect('@system/goodsAttribute')->with('successTs','商品属性修改成功');
        }
    }

    /**
     * 删除商品属性
     * @param $ga_id
     * @return $this
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function deleteGoodsAttributeOperation($ga_id){
        if (!Db::table('goods_attribute')->where('ga_id',$ga_id)->find()){
            return redirect('@system/goodsAttribute')->with('errorTs','商品属性修改失败，该属性不存在');
        }else{
            Db::table('goods_attribute')->where('ga_id',$ga_id)->delete();
            return redirect('@system/goodsAttribute')->with('successTs','商品属性删除成功');
        }
    }

}
