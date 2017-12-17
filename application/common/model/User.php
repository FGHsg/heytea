<?php

namespace app\common\model;

use think\Model;

class User extends Model
{
    protected $table = 'user';
    protected $pk = 'u_openid';
    protected $autoWriteTimestamp = false;

    private $u_openid;
    private $u_avatar;
    private $u_nickname;
    private $u_gender;
    private $u_place;

    /**
     * 根据用户openid查询用户头像和昵称
     * @param $u_openid
     * @return array|false|\PDOStatement|string|Model
     */
    public function getUavatarAndUnicknameByUopenid($u_openid)
    {
        $rs = $this->where('u_openid', $u_openid)
            ->field('u_avatar,u_nickname')
            ->find();
        return $rs;
    }

    /**
     * 根据用户openid查询用户昵称
     * @param $u_openid
     * @return array|false|\PDOStatement|string|Model
     */
    public function getUnicknameByUopenid($u_openid)
    {
        $rs = $this->where('u_openid', $u_openid)
            ->field('u_nickname')
            ->find();
        return $rs;
    }

}
