<?php

namespace app\customer\controller;

use app\common\model\Business;
use app\common\model\BusinessCondition;
use app\common\model\Goods;
use app\common\model\Member;
use app\common\model\Order;
use app\common\model\OrderComment;
use app\common\model\User;
use app\common\utils\HttpRequest;
use app\common\utils\WxApi;
use think\Config;
use think\Controller;
use think\Db;
use think\File;
use think\Image;
use think\Request;
use think\Session;

class OrderController extends Controller{


    /**
     * 获取订单信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOrderInfo(){
        $o_id = input('o_id');
        $orderModel = new Order();
        $order = $orderModel->where('o_id',$o_id)->find();
        $order['o_goods'] = json_decode($order['o_goods']);
        return json($order);
    }

    /**
     * 获取会员对应订单列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOrderInfoList(){
        $m_id = input('m_id');
        if (!is_null($m_id)&&strlen($m_id)!=0){
            if (Member::get($m_id)!=null){
                $orderModel = new Order();
                $orders = $orderModel->where('om_id',$m_id)->order('create_time','DESC')->select();
                foreach ($orders as $order){
                    $order['o_goods'] = json_decode($order['o_goods']);
                }
                return json($orders);
            }else{
                return json([
                    'errorMessage' => '用户不存在',
                ]);
            }
        }else{
            return json([
                'errorMessage' => '参数错误',
            ]);
        }

    }

    /**
     * 根据状态获取订单列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getOrderInfoListByStatus(){
        $m_id = input('m_id');
        $o_status = input('status');
        if (!is_null($m_id)&&strlen($m_id)!=0){
            if (Member::get($m_id)!=null){
                $orderModel = new Order();
                $orders = $orderModel
                            ->where(['om_id'=>$m_id,'o_status'=>$o_status])
                            ->order('create_time','DESC')
                            ->select();
                foreach ($orders as $order){
                    $order['o_goods'] = json_decode($order['o_goods']);
                }
                return json($orders);
            }else{
                return json([
                    'errorMessage' => '用户不存在',
                ]);
            }
        }else{
            return json([
                'errorMessage' => '参数错误',
            ]);
        }

    }


    /**
     * 获取用户订单评价
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getMyOrderCommentList(){
        $m_id = input('m_id');
        $page = input('page');
        if (!is_null($m_id)&&strlen($m_id)!=0
            &&!is_null($page)&&strlen($page)!=0){
            if (Db::table('member')->find($m_id)==null){
                $resultSet['errorCode'] = 1003;
            }else{
                $orderCommentModel = new OrderComment();
                $orderComments = $orderCommentModel->getMyOrderCommentList($m_id,$page);
                if (!empty($orderComments)){
                    $memberModel = new Member();
                    $userModel = new User();
                    $orderCommentList = array();
                    foreach ($orderComments as $orderComment){
                        //根据关联会员id查询用户id
                        $member = $memberModel->getMuopenidByMid($orderComment['ocm_id']);
                        //根据用户id查询用户头像和昵称
                        $user = $userModel->getUavatarAndUnicknameByUopenid($member['mu_openid']);
                        $orderCommentInfo = [
                            'oc_id' => $orderComment['oc_id'],
                            'oco_id' => $orderComment['oco_id'],
                            'ocm_id' => $orderComment['ocm_id'],
                            'oc_content' => $orderComment['oc_content'],
                            'oc_score' => $orderComment['oc_score'],
                            'oc_img' => json_decode($orderComment['oc_img']),
                            'oc_number' => $orderComment['oc_number'],
                            'create_time' => substr($orderComment['create_time'],5,5),
                            'u_avatar' => $user['u_avatar'],
                            'u_nickname' => $user['u_nickname'],
                        ];
                        $orderCommentList[] = $orderCommentInfo;
                    }
                    $resultSet = [
                        'comment' => $orderCommentList,
                        'errorCode' => 0
                    ];
                }
            }
        }else{
            $resultSet['errorCode'] = 1001;
        }
        return json($resultSet);
    }


    //订单流程
    //创建订单：1
    /**
     * 创建订单
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function createOrder(){

        $om_id = input('om_id');
        $o_address = input('o_address');
        $o_receiver = input('o_receiver');
        $o_phone = input('o_phone');
        $o_lunchbox = input('o_lunchbox');
        $o_lunchbox_number = input('o_lunchbox_number');
        $o_distribution = input('o_distribution');
        $o_sum = input('o_sum');
        $o_point = input('o_point');
        $o_class = input('o_class');
        $o_goods = html_entity_decode(html_entity_decode(input('o_goods')));
        $o_remarks = input('o_remarks');
        $o_delivery_time = input('o_delivery_time');

        if (!is_null($om_id)&&strlen($om_id)!=0
            &&!is_null($o_address)&&strlen($o_address)!=0
            &&!is_null($o_receiver)&&strlen($o_receiver)!=0
            &&!is_null($o_phone)&&strlen($o_phone)!=0
            &&!is_null($o_lunchbox)&&strlen($o_lunchbox)!=0
            &&!is_null($o_lunchbox_number)&&strlen($o_lunchbox_number)!=0
            &&!is_null($o_distribution)&&strlen($o_distribution)!=0
            &&!is_null($o_sum)&&strlen($o_sum)!=0
            &&!is_null($o_point)&&strlen($o_point)!=0
            &&!is_null($o_class)&&strlen($o_class)!=0
            &&!is_null($o_goods)&&strlen($o_goods)!=0
            &&!is_null($o_remarks)&&strlen($o_remarks)!=0
            &&!is_null($o_delivery_time)&&strlen($o_delivery_time)!=0
        ){
            $orderModel = new Order();
            $o_number = $orderModel->getOrderNumber();

            $orderDate = [
                'o_number' => $o_number,
                'o_status' => 1,
                'om_id' => $om_id,
                'o_address' => $o_address,
                'o_receiver' => $o_receiver,
                'o_phone' => $o_phone,
                'o_lunchbox' => $o_lunchbox,
                'o_lunchbox_number' => $o_lunchbox_number,
                'o_distribution' => $o_distribution,
                'o_sum' => $o_sum,
                'o_point' => $o_point,
                'o_class' => $o_class,
                'o_goods' => $o_goods,
                'o_remarks' => $o_remarks,
                'o_delivery_time' => $o_delivery_time,
            ];
            $order = $orderModel::create($orderDate);
            if ($order->o_id!=null){
                $orderModel->refreshGoodsSpecificationsByOrder(json_decode($order['o_goods']));
                $resultSet['errorCode'] = 0;
                $order['o_goods'] = json_decode($order['o_goods']);
                //更新商品销售状况，选购商品销售量相应增加
                $goodsModel = new Goods();
                foreach ($order['o_goods'] as $og){
                    $og_g_id = $og->g_id;
                    $og_number = $og->number;
                    $goodsModel->incSalesVolume($og_g_id,$og_number);
                }
                $resultSet['o_id'] = $order->o_id;
                $resultSet['order'] = $order;
            }else{
                $resultSet['errorCode'] = 1002;
            }
        }else{
            $resultSet['errorCode'] = 1001;
        }
        return json($resultSet);
    }

    //付款后待商家接单：2    (支付操作)

    public function getTicket(){
//        if (Session::has('access_token')){
//            if (time()-Session::get('akStartTime')<3600){
//                $accessToken = Session::get('access_token');
//                if ($accessToken==null){
//                    Session::delete('access_token');
//                    $accessToken = WxApi::get_access_token();
//                    Session::set('access_token',$accessToken);
//                    Session::set('akStartTime',time());
//                }
//            }else{
//                Session::delete('access_token');
//                $accessToken = WxApi::get_access_token();
//                Session::set('access_token',$accessToken);
//                Session::set('akStartTime',time());
//            }
//        }else{
            $accessToken = WxApi::get_access_token();
            Session::set('access_token',$accessToken);
            Session::set('akStartTime',time());
//        }
        $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$accessToken.'&type=jsapi';
        $rs = HttpRequest::curl_get($url);
        return $rs;
    }

    /**
     * 客户端接收签名验证
     * @return \think\response\Json
     */
    public function generateConfig(){
        import('wxpay.lib.WxPayJsApiPay');//导入相关类库
//        if (Session::has('jsapi_ticket')){
//            if (time()-Session::get('jtStartTime')<3600){
//                $jsapi_ticket = Session::get('jsapi_ticket');
//                if ($jsapi_ticket==null){
//                    Session::delete('jsapi_ticket');
                    $jsapi_ticket = $this->getTicket()['ticket'];
//                    Session::set('jsapi_ticket',$jsapi_ticket);
//                    Session::set('jtStartTime',time());
//                }
//            }else{
//                Session::delete('jsapi_ticket');
//                $jsapi_ticket = $this->getTicket()['ticket'];
//                Session::set('jsapi_ticket',$jsapi_ticket);
//                Session::set('jtStartTime',time());
//            }
//        }else{
//            $jsapi_ticket = $this->getTicket()['ticket'];
//            Session::set('jsapi_ticket',$jsapi_ticket);
//            Session::set('jtStartTime',time());
//        }
        $noncestr = (String)\WxPayApi::getNonceStr(32);
        $timestamp = time();
        $url = urldecode(input('url'));
        $str = 'jsapi_ticket='.$jsapi_ticket.'&noncestr='.$noncestr.'&timestamp='.$timestamp.'&url='.$url;
        $signature = 'jsapi_ticket='.$jsapi_ticket.'&noncestr='.$noncestr.'&timestamp='.$timestamp.'&url='.$url;
        $signature = sha1($signature);
        return json([
            'appId' => Config::get('APP_ID'),
            'timestamp' => $timestamp,
            'noncestr' => $noncestr,
            'signature' => $signature,
            'jsapi_ticket' => $jsapi_ticket,
            'url' => $url,
            'str' => $str,
        ]);
    }

    public function jsapi(){
        import('wxpay.lib.WxPayJsApiPay');//导入相关类库
        $o_number = input('o_number');
        $o_sum = input('o_sum');
        $m_id = input('m_id');
        $u_openid = Db::table('member')->where('m_id',$m_id)->value('mu_openid');

//        if (!is_null($orderInfo)&&strlen($orderInfo)!=0){
//            if($this->request->isAjax()){
                $tools = new \JsApiPay();

                //②、统一下单

                $order_body = 'heytea-奶茶';
                $currBuyWay = '附加数据，在查询API和支付通知中原样返回，可作为自定义参数使用';
                $out_trade_no = $o_number;
                $order_total = $o_sum*100;
//                $u_openid = $u_openid;

//                $orderModel = new Order();
//                return json([
//                    'errorCode' => 0,
//                    'o_number' => $out_trade_no,
//                    'o_sum' => $order_total,
//                    'u_openid' => $u_openid,
//                ]);

                /*------------------------以下对接微信接口--------------------------*/

                $input = new \WxPayUnifiedOrder();
                $input->SetBody($order_body);
                $input->SetAttach($currBuyWay);
                $input->SetOut_trade_no($out_trade_no);
                $input->SetTotal_fee($order_total);
                $input->SetTime_start(date("YmdHis"));
                $input->SetTime_expire(date("YmdHis", time() + 600));
                $input->SetGoods_tag("test");
                $input->SetNotify_url("http://naicha.walkerbang.com/customer/notify");//异步通知
                $input->SetTrade_type("JSAPI");     //设置扫码支付
                $input->SetOpenid($u_openid);
                $order = \WxPayApi::unifiedOrder($input);
//                return json([
//                    $input,$order
//                ]);
                $jsApiParameters = $tools->GetJsApiParameters($order);
                $resultSet['errorCode'] = 0;
                return json(['isok'=>true,'rs'=>$jsApiParameters,$resultSet]);
//            }else{
//                $resultSet['errorCode'] = 1001;
//                return json($resultSet);
//            }
//        }

    }


    /**
     * 异步通知（支付成功微信通知地址）
     * 应在此处进行订单相关逻辑处理
     * 处理完订单业务，应当回复success告诉微信服务器，停止继续异步通知(注意：echo之后不能return)
     */
    public function notify(){
        //获取微信服务器post过来的xml数据，并转化为对象
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        libxml_disable_entity_loader(true);
        $dataObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $dataArr = json_decode(json_encode($dataObj),true);//xml转换为json，再转换为数组

        //1.查询订单，判断订单真实性
        if(!$this->Queryorder($dataArr['transaction_id'])){
            return false;
        }

        /*************处理订单相关业务逻辑***************/
        //响应微信服务器，停止微信继续异步通知

        $successMessage = '<xml>
						<return_code><![CDATA[SUCCESS]]></return_code>
						<return_msg><![CDATA[OK]]></return_msg>
						</xml>';
        $failMessage = '<xml>
						<return_code><![CDATA[FAIL]]></return_code>
						<return_msg><![CDATA[OK]]></return_msg>
						</xml>';
        //2.验证数据合法性（比如订单是否是后台存在，支付金额是否一样等）
        //获取订单号
        $o_number = $dataArr['out_trade_no'];
        $transaction_id = $dataArr['transaction_id'];//微信支付订单号
//        $orderInfo = $dataArr['attach'];
//        $complete_time = $dataArr['time_end'];
        $total_fee = $dataArr['total_fee'];
        $orderModel = new Order();
        $businessConditionModel = new BusinessCondition();
        $order = $orderModel->where('o_number',$o_number)->find();
        if ($order){
            $o_status = $order->o_status;
            if ($o_status==1){
                $orderModel->where('o_id',$order->o_id)->update([
                    'o_status'=>2,
                    'transaction_id'=>$transaction_id,
                    'o_pay_time' => date("Y-m-d H:i:s",time()),     //设置支付成功时间
                ]);
                $bc_id = $businessConditionModel->getBCIDForToday();
                $bc = $businessConditionModel->find($bc_id);
                $bcData = [
                    'bc_order_count' => $bc->bc_order_count + 1,
                    'bc_complete_sum' => $bc->bc_complete_sum + $total_fee,
                ];
                $businessConditionModel->save($bcData,['bc_id',$bc_id]);
            }else{
                $orderModel->where('o_id',$order->o_id)->update([
                    'transaction_id'=>$transaction_id,
                ]);
            }
            $responseXml = $successMessage;
        }else{
            $responseXml = $failMessage;
        }
        //3.更新订单完成后，回复以下告诉微信处理完成，不再推送
        echo $responseXml;
    }

    /**
     * 支付成功确认接口
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function paySuccess(){
        $o_number = input('o_number');
        $m_id = input('m_id');
        $orderModel = new Order();
        $businessConditionModel = new BusinessCondition();
        $order = $orderModel->where('o_number',$o_number)->find();
        if ($order){
            if ($order['om_id']==$m_id){
                $o_status = $order->o_status;
                if ($o_status==1){
                    $orderModel->where('o_id',$order->o_id)->update([
                        'o_status'=>2,
                        'o_pay_time' => date("Y-m-d H:i:s",time()),     //设置支付成功时间
                    ]);
                    $bc_id = $businessConditionModel->getBCIDForToday();
                    $bc = $businessConditionModel->find($bc_id);
                    $bcData = [
                        'bc_order_count' => $bc->bc_order_count + 1,
                        'bc_complete_sum' => $bc->bc_complete_sum + $order['o_sum'],
                    ];
                    $businessConditionModel->save($bcData,['bc_id',$bc_id]);
                }
                $resultSet['erorCode'] = 0;
            }else{
                $resultSet['erorCode'] = 1001;
            }
        }else{
            $resultSet['erorCode'] = 1003;
        }
        return $resultSet;
    }

    /**
     * 查询订单
     */
    protected function Queryorder($transaction_id){
        //导入相关类库
        import('wxpay.lib.WxPayApi');

        $input = new \WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = \WxPayApi::orderQuery($input);

        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            return true;
        }
        return false;
    }




    //用户收货后待评价：5    (订单评价)
    public function confirmOrder(){
        $m_id = request()->param('m_id');
        $o_id = request()->param('o_id');
        $order = Db::table('order')->where(['o_id'=>$o_id,'om_id'=>$m_id])->find();
        if ($order==null){
            $resultSet['errorCode'] = 1003;
        }else{
            if ($order['o_status']==4){
                Order::update([
                    'o_id' => $o_id,
                    'o_status' => 5,
                    'o_delivery_time' =>date("Y-m-d H:i:s",time()),     //修改送达时间
                ]);
                $memberModel = new Member();
                $memberModel->where('m_id',$m_id)->setInc('m_point',$order['o_point']);
                $resultSet['errorCode'] = 0;
            }else{
                $resultSet['errorCode'] = 1007;
            }
        }
        return json($resultSet);
    }

    //用户评价后自动转为已完成：6
    public function commentOrder(){
        //获取参数
        $oco_id = input('oco_id');
        $ocm_id = input('m_id');
        $oc_content = input('oc_content');
        $oc_score = input('oc_score');
        $imgs = request()->file('c_img');

        //判断参数是否合法
        if (!is_null($oco_id)&& strlen($oco_id) != 0
            && !is_null($oc_content)&& strlen($oc_content) != 0
            && !is_null($oc_score)&& strlen($oc_score) != 0
            && $oc_score>=1&& $oc_score<=5) {

            //判断该订单是否存在
            $order = Order::get($oco_id);
            if ($order['o_status']==5||$order['o_status']==6){
                if ($order['oc_id']==null){
                    //不存在则为用户收货后订单评论
                    $host = 'http://120.78.183.57/';
                    $savePath = 'public/uploads/order/comment/';
                    $c_img = array();
                    $i = 1;
                    if ($imgs==null){
                        //如果没上传图片
                        $commentData = [
                            'oco_id' =>$oco_id,
                            'ocm_id' =>$ocm_id,
                            'oc_content' =>$oc_content,
                            'oc_status' => 0,
                            'oc_score' => $oc_score,
                            'oc_number' => 0,
                        ];
                        //创建comment的model对象
                        $commentModel = new OrderComment();
                        $commentModel = $commentModel->create($commentData);
                        $commentModel->startTrans();
                        //判断新增是否成功
                        if (!$order::update([
                            'o_id' => $oco_id,
                            'oc_id'=>$commentModel['oc_id'],
                            'o_status'=>6,
                            'o_complete_time' => date('Y-m-d H:i:s',time()),
                        ])){
                            $commentModel->rollback();
                        }
                        if ($commentModel) {
                            $resultSet['errorCode'] = 0;
                        } else {
                            $resultSet['errorCode'] = 1002;
                        }
                        $commentModel->commit();
                    }else{
                        //上传茶评图片
                        foreach ($imgs as $img) {
                            //设置文件上传限制，允许上传的最大文件大小：10M，类型为jpg或png
                            $img->validate(['size' => 10485760, 'ext' => 'jpg,png']);
                            $imgComp = Image::open($img);
                            $fileName =md5(time()).'-'.$i .'.jpg';
                            $imgUploadResult = $imgComp->thumb(360, 360)
                                ->save(ROOT_PATH.$savePath.$fileName,'jpg');
                            if ($imgUploadResult) {
                                $c_img[$i] =  $host . $savePath . $fileName;
                            }
                            $i++;
                        }
                        //存在则为对订单的评价
                        //判断是否上传成功，成功则新增数据，失败则返回错误码
                        if (count($c_img) == count($imgs)) {
                            //设置订单评论的信息
                            $commentData = [
                                'oco_id' =>$oco_id,
                                'ocm_id' =>$ocm_id,
                                'oc_content' =>$oc_content,
                                'oc_status' => 0,
                                'oc_score' => $oc_score,
                                'oc_number' => 0,
                                'oc_img' => json_encode($c_img)
                            ];
                            //创建comment的model对象
                            $commentModel = new OrderComment();
                            $commentModel = $commentModel->create($commentData);
                            $commentModel->startTrans();
                            //判断新增是否成功
                            if (!$order->save([
                                'oc_id'=>$commentModel['oc_id'],
                                'o_status'=>6,
                                'o_complete_time' => date('Y-m-d H:i:s',time()),    //用户评论后订单完成保存，订单完成时间
                                ])){
                                $commentModel->rollback();
                            }
                            if ($commentModel) {
                                $resultSet['errorCode'] = 0;
                            } else {
                                $commentModel->rollback();
                                //删除已上传的图片，返回错误信息
                                for ($i = 1; $i <= count($c_img); $i++) {
                                    unlink(ROOT_PATH.substr($c_img[$i],21));
                                }
                                $resultSet = [
                                    'errorCode' => 1002,
                                    'message' => '数据库异常'
                                ];
                            }
                            $commentModel->commit();
                        } else {
                            //删除已上传的图片，返回错误信息
                            for ($i = 1; $i <= count($c_img)+1; $i++) {
                                unlink(ROOT_PATH.substr($c_img[$i],21));
                            }
                            $resultSet = [
                                'errorCode' => 1007,
                                'message' => '文件上传失败',
                            ];
                        }
                    }

                }else{
                    //若存在对应评论，则直接保存，不上传图片
                    $commentData = [
                        'oco_id' =>$oco_id,
                        'ocm_id' =>$ocm_id,
                        'oc_content' =>$oc_content,
                        'oc_status' => 0,
                        'oc_score' => $oc_score,
                        'oc_number' => 0,
                    ];
                    $resultSet['errorCode'] = 0;
                    $orderCommentModel = new OrderComment();
                    $commentModel = $orderCommentModel::create($commentData);
                    $commentModel->startTrans();
                    $orderComment = $orderCommentModel->find($order['oc_id']);
                    if (!$commentModel){
                        $commentModel->rollback();
                        $resultSet['errorCode'] = 1002;
                    }
                    if (!$orderComment->setInc('oc_number')){
                        $commentModel->rollback();
                        $resultSet['errorCode'] = 1002;
                    }
                    $commentModel->commit();
                    if($resultSet['errorCode']==0){
                        $resultSet['oco_id'] = $oco_id;
                        $resultSet['ocm_id'] = $ocm_id;
                        $resultSet['oc_content'] = $oc_content;
                        $resultSet['oc_score'] = $oc_score;
                    }
                }
            }else{
                $resultSet['errorCode'] = 1007;
            }

        } else {
            //返回错误信息
            $resultSet = [
                'errorCode' => 1001,
                'message' => '参数不合法'
            ];
        }
        return json($resultSet);
    }

    //其他操作

    //催单：
    public function reminderOrder() {
        $o_id = input('o_id');
        if (is_null($o_id)) {
            $resultSet['errorCode'] = 1001;
        } else {
            $orderModel = new Order();
            $order = $orderModel->find($o_id);
            if ($order != null) {
                //订单在待发货（3）和待收货（4）状态可进行催单
                $o_status = $order['o_status'];
                if ($o_status != 3 && $o_status != 4) {
                    $resultSet['errorCode'] = 1007;
                } else {
                    $orderModel::update([
                        'o_id'=>$o_id,
                        'o_reminder'=>1,
                    ]);
                    $resultSet['errorCode'] = 0;
                }
            } else {
                $resultSet['errorCode'] = 1003;
            }
        }
        return json($resultSet);
    }



            //取消订单：
    //15分钟无操作后自动取消
    //在待接单状态也可进行退款操作，直接取消订单和退款成功，不需审核
    //待发货，3-8（退款中）-9（已退款）
    //待收货，4-8-9
    /**
     * @return \think\response\Json 若返回的errorCode为1007，则表示当前状态不可更改
     */
    public function cancelOrder(){
        $o_id = input('o_id');
        $om_id = input('om_id');
        $o_reason = input('o_reason');
        if (is_null($o_id)||is_null($om_id)||is_null($o_reason)){
            $resultSet['errorCode'] = 1001;
        }else{
            $goodsModel = new Goods();
            $orderModel = new Order();
            $order = $orderModel->where(['o_id'=>$o_id,'om_id'=>$om_id])->find();
            //订单处于待接单状态直接取消无需审核
            if (!is_null($order)){
                $o_status = $order['o_status'];
                if ($o_status==1){
                    Order::update(['o_id'=>$o_id,'o_status'=>7]);
                    $goodsModel->decSalesVolumeByOid($o_id);
                    $resultSet['erroeCode'] = 0;
                }else if ($o_status==2){
                    //待接单状态，直接取消订单
                    $resultSet['erroeCode'] = $orderModel->returnOrderOperation($order,7);
                    $goodsModel->decSalesVolumeByOid($o_id);
                }else if ($o_status==3||$o_status==4){
                    if (!is_null($o_reason)&&strlen($o_reason)){
                        Order::update([
                            'o_id'=>$o_id,
                            'o_return_status'=>1,
                            'o_reason'=>$o_reason
                        ]);
                    }else{
                        Order::update([
                            'o_id'=>$o_id,
                            'o_return_status'=>1,
                            ]);
                    }
                    $resultSet['erroeCode'] = 0;
                }else{
                    $resultSet['errorCode'] = 1007;
                }
            }else{
                $resultSet['errorCode'] = 1003;
            }
        }
        return json($resultSet);
    }

}
