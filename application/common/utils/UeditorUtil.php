<?php
/**
 * Created by PhpStorm.
 * User: Di
 * Date: 2017/12/5 0005
 * Time: 13:15
 */

namespace app\common\utils;


class UeditorUtil{


    public function uploads(){
        $img = request()->file('img');

        $host = Config::get('hostip');
        $savePath = 'public/uploads/ueditor/image/';
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



}