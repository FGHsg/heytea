<?php

namespace app\common\model;

use think\Db;
use think\File;
use think\Image;
use think\Model;

class Order extends Model
{
    protected $table = 'order';
    protected $pk = 'o_id';

    /**
     * 获取当前创建订单的订单号
     * @return int|mixed|string
     */
    public function getOrderNumber(){
        $numberBefo = Db::table('order')->max('o_number');
        $todayNum = (date("Ymd").'0001');
        if ($numberBefo<$todayNum){
            $o_number = $todayNum;
        }else{
            $o_number = $numberBefo+1;
        }
        return $o_number;
    }

    /**
     * 通过状态码获取当前状态
     * @param $o_status
     * @return bool|string
     */
    public function getStatusInCh($o_status){

        if (!is_null($o_status)&&strlen($o_status)!=0){
            switch ($o_status){
                case 1 : return '待付款';
                case 2 : return '待接单';
                case 3 : return '待配送';
                case 4 : return '待评价';
                case 5 : return '已完成';
                case 6 : return '已取消';
                case 7 : return '退款中';
                case 8 : return '已退款';
                case 9 : return '拒绝退款';
            }
        }else{
            return false;
        }

    }

    /**
     * 根据订单o_goods刷新商品规格库存
     * @param $o_goods
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function refreshGoodsSpecificationsByOrder($o_goods){
        $gsModel = new GoodsSpecifications();
        foreach ($o_goods as $og){
            $gsg_id = $og->g_id;
            $g_content = $og->g_content;
            $number = $og->number;
            $ogData = [
                'gsg_id' => $gsg_id,
                'g_content' => $g_content,
            ];
            if ($gs = $gsModel->where($ogData)->find()){
                $gs_id = $gs['gs_id'];
                $g_rest = $gs['g_rest'];
                if ($g_rest == -1){
                    //无操作
                }else if ($g_rest>0&&$g_rest<=$number){
                    $gsModel->where('gs_id',$gs_id)->update(['gs_id'=>$gs_id,'g_rest'=>0]);
                }else if ($g_rest>$number){
                    $gsModel->where('gs_id',$gs_id)->update(['gs_id'=>$gs_id,'g_rest'=>($g_rest-$number)]);
                }
            }
        }
    }

    /**
     * 获取对应会员id的订单列表
     * @param $om_id
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOrderList($om_id){
        $orderList = $this->where('om_id',$om_id)->find();
        return $orderList;
    }

    /**
     * 获取会员对应状态订单列表
     * @param $m_id
     * @param $orderStatus
     * @return array|false|\PDOStatement|string|Model
     */
    public function getMemberOrderList($m_id,$orderStatus){
        $req = [
            'om_id' => $m_id,
            'o_status' => $orderStatus,
        ];
        return $this->where($req)->find();
    }

    /**
     * 获取当日订单数
     * @return int|mixed|string
     */
    public static function getOrderAmount(){
        $numberBefo = Db::table('order')->max('o_number');
        $todayNum = (date("Ymd").'0000');
        $amount = $numberBefo-$todayNum;
        if ($amount<=0){
            return 0;
        }else{
            return $amount;
        }

    }

    /**
     * 获取对应状态订单列表
     * @param $orderStatus
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCompletedOrder($orderStatus){
        return $this->where('o_status',$orderStatus)->find();
    }


    public function saveCommentImg(File $img,$orderData){
        //图片保存路径：/var/www/html/upload/img/order/comment
        $host = 'http://120.78.183.57/';
        $savePath = 'upload/img/order/comment';
        $img->validate(['size'=>1024*1024,'ext'=>'jpg,png']);
        $imgComp = Image::open($img);
        $fileName = $orderData['o_number'].time().'.jpg';
        $imgComp->thumb(500,500)->save('/var/www/html/'.$savePath.$fileName,'jpg');
        if ($imgComp){
            return $host.$savePath.$fileName;
        }else{
            return false;
        }
    }

    /**
     * 获取订单列表
     * @param null $condition
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOrderListForAdmin($condition = null){
        if (is_array($condition)&&$condition!=null){
            $rs = $this->where($condition)->order('update_time','ACS')->select();
        }else{
            $rs = $this->order('update_time','ACS')->select();
        }

        foreach ($rs as $r){
            $r['o_status'] = $this->getStatusInCh($r['o_status']);
        }
        return $rs;
    }


    /**
     * 管理端获取订单评论列表
     * @param null $condition
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getCommentListForAdmin($condition = null){
        if (is_array($condition)&&$condition!=null){
            $rs = Db::view('order_comment oc','oc_id,oco_id,ocm_id,oc_content,oc_img,oc_status,oc_score,oc_number,create_time')
                ->view('order o','o_id,o_number,o_goods','o.o_id = oc.oco_id')
                ->view('member m','m_id,mu_openid','oc.ocm_id = m.m_id')
                ->view('user u','u_openid,u_nickname','m.mu_openid = u.u_openid')
                ->where($condition)
                ->order('create_time','DESC')
                ->paginate(10);
        }else{
            $rs = Db::view('order_comment oc','oc_id,oco_id,ocm_id,oc_content,oc_img,oc_status,oc_score,oc_number,create_time')
                ->view('order o','o_id,o_number,o_goods','o.o_id = oc.oco_id')
                ->view('member m','m_id,mu_openid','oc.ocm_id = m.m_id')
                ->view('user u','u_openid,u_nickname','m.mu_openid = u.u_openid')
                ->order('create_time','DESC')
                ->paginate(10);
        }
        return $rs;
    }

    /**
     * 取消订单退款操作,同时修改商铺营业状况表
     * 1、如果是微信支付，则需要先退款，后取消订单
     * 2、线下支付，直接取消
     * status为修改后的状态
     */
    public function returnOrderOperation(Order $order,$o_status){
        //导入相关类库
        import('wxpay.lib.WxPayApi');
        //订单信息
        $transaction_id = $order->transaction_id;//微信订单交易号
        $wxpay_fee = $order->o_sum*100;//已支付的金额
        $refund_fee = $order->o_sum*100;//申请退还金额
        $out_refund_no = \WxPayConfig::MCHID.date("YmdHis");
//        $out_refund_no = md5(time());       //测试用
        $orderModel = new Order();
        $memberModel = new Member();
        //将退款单号保存到数据库
        $orderModel->where('o_id',$order->o_id)->update(['out_refund_no' => $out_refund_no]);
//        //发起退款请求
        $input = new \WxPayRefund();
        $input->SetTransaction_id($transaction_id);
        $input->SetTotal_fee($wxpay_fee);
        $input->SetRefund_fee($refund_fee);
        $input->SetOut_refund_no($out_refund_no);
        $input->SetOp_user_id(\WxPayConfig::MCHID);
        $refundRsXml = \WxPayApi::refund($input);
        $refundRs = json_decode(json_encode($refundRsXml),true);//把xml转换成数组
//        return json($refundRs);
        if($refundRs['return_code'] == 'SUCCESS' && $refundRs['result_code'] == 'SUCCESS'){//退款成功
            $orderModel->where('o_id',$order->o_id)->update([
                'o_status' => $o_status,
                'o_cancel_time' => date('Y-m-d H:i:s',time()),
            ]);
            $businessConditionModel = new BusinessCondition();
            $bc_id = $businessConditionModel->getBCIDForToday();
            $bc = $businessConditionModel->find($bc_id);
            $bcData = [
                'bc_order_count' => $bc->bc_order_count - 1,
                'bc_complete_sum' => $bc->bc_complete_sum + $order->o_sum,
            ];
            $businessConditionModel->save($bcData,['bc_id'=>$bc_id]);
            return 0;
        }else{//退款失败
            return 1;
        }
    }
}
