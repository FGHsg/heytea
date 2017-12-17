<?php

namespace app\common\model;

use think\Model;

class Address extends Model
{
    protected $table = 'address';
    protected $pk = 'aid';

    public function getAddrList($am_id){
        $addrList = $this->where('am_id',$am_id)->find();
    }

}
