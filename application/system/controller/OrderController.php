<?php

namespace app\system\controller;

use app\common\model\BusinessCondition;
use app\common\model\Goods;
use app\common\model\Order;
use app\common\model\OrderComment;
use think\Controller;
use think\Db;
use think\Request;

class OrderController extends Auth {

    /**
     * 订单评论视图
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function orderCommentView(){
        $create_time = input('create_time');
        $u_nickname = input('u_nickname');
        $g_name = input('g_name');
        $oc_status = input('oc_status');

        $condition = array();
        if (!is_null($create_time)&&strlen($create_time)){
            $startTime = substr($create_time, 0, 10);
            $endTime = substr($create_time, 13);
            $condition['oc.create_time'] = array(array('EGT', $startTime), array('ELT', $endTime), 'AND');
            $this->assign('create_time',$create_time);
        }
        if (!is_null($u_nickname)&&strlen($u_nickname)){
            $condition['u.u_nickname'] = $u_nickname;
            $this->assign('u_nickname',$u_nickname);
        }
        if (!is_null($g_name)&&strlen($g_name)){
//            $s = '[{"g_id":25,"g_name":"乌龙奶茶","g_content":"大杯","g_price":18,"number":2}]';
            $condition['o.o_goods'] = array('like','%'.$g_name.'%');
            $this->assign('g_name',$g_name);
        }
        if (!is_null($oc_status)&&strlen($oc_status)){
            $condition['oc.oc_status'] = $oc_status;
            $this->assign('oc_status'.$oc_status);
        }

        $orderModel = new Order();
        $orderCommentList = $orderModel->getCommentListForAdmin($condition);
        $goodsInfo = Db::view('goods','g_id,g_name')->select();

        $theGoodsList = Order::all();
        foreach ($theGoodsList as $theList){
            $theList['o_goods'] = json_decode($theList['o_goods']);
        }
//        foreach ($orderCommentList as $oc){
//            $list = null;
//            $list = Db::table('order')->find($oc['oco_id']);
//            $list['o_goods'] = json_decode($list['o_goods']);
//            $theGoodsList[] = $list;
//        }
//        return json([
//            'order_comment' => $orderCommentList,
//            'goods' => $goodsInfo,
//            'orderGoodsList' => $theGoodsList
//        ]);
        $this->assign([
            'order_comment' => $orderCommentList,
            'goods' => $goodsInfo,
            'orderGoodsList' => $theGoodsList
        ]);
        return $this->fetch();
    }


    /**
     * 更改订单评价状态
     * @return $this
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function changeOCStatus(){
        $oc_id = input('oc_id');
        $oc_status = input('oc_status');
        if (!is_null($oc_id)&&strlen($oc_id)
            &&!is_null($oc_status)&&strlen($oc_status)
            &&($oc_status==0||$oc_status==1)){
            $orderCommentModel = new OrderComment();
            $oc = $orderCommentModel->find($oc_id);
            if ($oc){
                $orderCommentModel->isUpdate(true)->save([
                    'oc_id' => $oc_id,
                    'oc_status' => $oc_status,
                ]);
                return redirect('@system/orderCommentView')->with('successTs','订单状态修改成功');
            }else{
                return redirect('@system/orderCommentView')->with('errorTs','订单评价状态更改失败，该评价不存在');
            }

        }else{
            return redirect('@system/orderCommentView')->with('errorTs','订单评价状态更改失败，参数错误');
        }
    }


    //接单视图
    public function handleOrder(Request $request){
        $create_time = $request->param('create_time');
        $o_number = $request->param('o_number');
        $o_receiver = $request->param('o_receiver');
        $o_class = $request->param('o_class');
        $condition = array();
        $condition['o_status'] = 2;
        if (!is_null($create_time)&&strlen($create_time)!=0){
            $startTime = substr($create_time, 0, 10);
            $endTime = substr($create_time, 13);
            $condition['create_time'] = array(array('EGT', $startTime), array('ELT', $endTime), 'AND');
            $this->assign('create_time',$create_time);
        }
        if (!is_null($o_number)&&strlen($o_number)!=0){
            $condition['o_number'] = $o_number;
            $this->assign('o_number',$o_number);
        }
        if (!is_null($o_receiver)&&strlen($o_receiver)!=0){
            $condition['o_receiver'] = $o_receiver;
            $this->assign('o_receiver',$o_receiver);
        }
        if (!is_null($o_class)&&strlen($o_class)!=0){
            $condition['o_class'] = $o_class;
            $this->assign('o_class',$o_class);
        }
        $orderModel = new Order();
        $orders = $orderModel->getOrderListForAdmin($condition);
        foreach ($orders as $order) {
            $order['o_goods'] = json_decode($order['o_goods']);
            $order['create_time'] = substr($order['create_time'],11,5);
            $order['o_complete_time'] = substr($order['o_complete_time'],11,5);
        }
        $bc = new BusinessCondition();
        $count = $bc->getOrderAmount();
        $sum = $bc->getSum();
//        return json([
//            'order' => $orders,
//            'count' => $count,
//            'sum' => $sum,
//        ]);
        $this->assign([
            'order' => $orders,
            'count' => $count,
            'sum' => $sum,
        ]);
        return $this->fetch();
    }

    //催单视图
    public function reminderOrderView(Request $request){
        $create_time = $request->param('create_time');
        $o_number = $request->param('o_number');
        $o_receiver = $request->param('o_receiver');
        $o_class = $request->param('o_class');
        $condition = array();
        if (!is_null($create_time)&&strlen($create_time)!=0){
            $startTime = substr($create_time, 0, 10);
            $endTime = substr($create_time, 13);
            $condition['create_time'] = array(array('EGT', $startTime), array('ELT', $endTime), 'AND');
            $this->assign('create_time'.$create_time);
        }
        if (!is_null($o_number)&&strlen($o_number)!=0){
            $condition['o_number'] = $o_number;
            $this->assign('o_number',$o_number);
        }
        if (!is_null($o_receiver)&&strlen($o_receiver)!=0){
            $condition['o_receiver'] = $o_receiver;
            $this->assign('o_receiver',$o_receiver);
        }
        if (!is_null($o_class)&&strlen($o_class)!=0){
            $condition['o_class'] = $o_class;
            $this->assign('o_class',$o_class);
        }
        //已处理订单条件
        $condition2 = $condition;
        $condition['o_reminder'] = 1;
        $orderModel = new Order();
        $orders = $orderModel->getOrderListForAdmin($condition);
        foreach ($orders as $order) {
            $order['o_goods'] = json_decode($order['o_goods']);
            $order['o_complete_time'] = substr($order['o_complete_time'],11,5);
        }

        $condition2['o_reminder'] = 2;
        $ordersRemindered = $orderModel->getOrderListForAdmin($condition2);

        foreach ($ordersRemindered as $order2) {
            $order2['o_goods'] = json_decode($order2['o_goods']);
            $order2['create_time'] = substr($order2['create_time'],11,5);
            $order2['o_complete_time'] = substr($order2['o_complete_time'],11,5);
        }
        $bc = new BusinessCondition();
        $count = $bc->getOrderAmount();
        $sum = $bc->getSum();

//        return json([
//            'order' => $orders,
//            'ordersRemindered' => $ordersRemindered,
//            'count' => $count,
//            'sum' => $sum,
//        ]);

        $this->assign([
            'order' => $orders,
            'ordersRemindered' => $ordersRemindered,
            'count' => $count,
            'sum' => $sum,
        ]);
        return $this->fetch();
    }

    //取消订单视图
    public function cancelOrderView(Request $request){
        $create_time = $request->param('create_time');
        $o_number = $request->param('o_number');
        $o_receiver = $request->param('o_receiver');
        $o_class = $request->param('o_class');

        $condition = array();

        if (!is_null($create_time)&&strlen($create_time)!=0){
            $startTime = substr($create_time, 0, 10);
            $endTime = substr($create_time, 13);
            $condition['create_time'] = array(array('EGT', $startTime), array('ELT', $endTime), 'AND');
            $this->assign('create_time',$create_time);
        }
        if (!is_null($o_number)&&strlen($o_number)!=0){
            $condition['o_number'] = $o_number;
            $this->assign('o_number',$o_number);
        }
        if (!is_null($o_receiver)&&strlen($o_receiver)!=0){
            $condition['o_receiver'] = $o_receiver;
            $this->assign('o_receiver',$o_receiver);
        }
        if (!is_null($o_class)&&strlen($o_class)!=0){
            $condition['o_class'] = $o_class;
            $this->assign('o_class',$o_class);
        }
        $condition2 = $condition;

        $condition['o_return_status'] = 1;
        $orderModel = new Order();
        $orders = $orderModel->getOrderListForAdmin($condition);
        foreach ($orders as $order) {
            $order['o_goods'] = json_decode($order['o_goods']);
            $order['create_time'] = substr($order['create_time'],11,5);
            $order['o_complete_time'] = substr($order['o_complete_time'],11,5);
        }

        $condition2['o_return_status'] = array('in','2,3');
        $orders2 = $orderModel->getOrderListForAdmin($condition2);
        foreach ($orders2 as $order2) {
            $order2['o_goods'] = json_decode($order2['o_goods']);
            $order2['create_time'] = substr($order2['create_time'],11,5);
            $order2['o_complete_time'] = substr($order2['o_complete_time'],11,5);
        }
        $bc = new BusinessCondition();
        $count = $bc->getOrderAmount();
        $sum = $bc->getSum();

        $this->assign([
            'order' => $orders,     //未处理
            'order2' => $orders2,   //已处理
            'count' => $count,
            'sum' => $sum,
        ]);
        return $this->fetch();
    }

    //历史订单列表视图
    public function historyOrder(Request $request){
        $create_time = input('create_time');
        $o_number = input('o_number');
        $o_receiver = input('o_receiver');
        $o_status = input('o_status');
        $o_class = input('o_class');

        $condition = array();
        $condition['o_status'] = array('in','3,4,5');
        if (!is_null($create_time)&&strlen($create_time)!=0){
            $startTime = substr($create_time, 0, 10);
            $endTime = substr($create_time, 13);
            $condition['create_time'] = array(array('EGT', $startTime), array('ELT', $endTime), 'AND');
            $this->assign('create_time',$create_time);
        }
        if (!is_null($o_number)&&strlen($o_number)!=0){
            $condition['o_number'] = $o_number;
            $this->assign('o_number',$o_number);
        }
        if (!is_null($o_receiver)&&strlen($o_receiver)!=0){
            $condition['o_receiver'] = $o_receiver;
            $this->assign('o_receiver',$o_receiver);
        }
        if (!is_null($o_status)&&strlen($o_status)!=0){
            $condition['o_status'] = $o_status;
            $this->assign('o_status',$o_status);
        }
        if (!is_null($o_class)&&strlen($o_class)!=0){
            $condition['o_class'] = $o_class;
            $this->assign('o_class',$o_class);
        }

        $orderModel = new Order();
        $orders = $orderModel->getOrderListForAdmin($condition);
        foreach ($orders as $order) {
            $order['o_goods'] = json_decode($order['o_goods']);
            $order['create_time'] = substr($order['create_time'],11,5);
            $order['o_complete_time'] = substr($order['o_complete_time'],11,5);
        }
        $bc = new BusinessCondition();
        $count = $bc->getOrderAmount();
        $sum = $bc->getSum();

        $this->assign([
            'order' => $orders,
            'count' => $count,
            'sum' => $sum,
        ]);
        return $this->fetch();
    }

    /**
     * 接单操作状态改为待配送
     * @return $this
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function handleOrderOperation(){
        $o_id = input('o_id');
        $orderModel = new Order();
        $order = $orderModel->find($o_id);

        if ($order->o_status==2){
            $order->update([
                'o_id'=>$o_id,
                'o_status'=>3,
                'o_handle_time'=>date('Y-m-d H:i:s',time()),        //设置接单时间
            ]);
            return redirect('@system/handleOrder')->with('successTs','接单成功');
        }else{
            return redirect('@system/handleOrder')->with('errorTs','该订单状态不可接单');
        }
    }

    /**
     * 发起配送，状态改为待收货
     * @return $this
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sendOrderOperation(){
        $o_id = input('o_id');
        $orderModel = new Order();
        $order = $orderModel->find($o_id);

        if ($order->o_status==3){
            $order->update([
                'o_id'=>$o_id,
                'o_status'=>4,
                'o_begin_delivery_time'=> date('Y-m-d H:i:s',time()),       //设置发起配送时间
            ]);
            return redirect('@system/historyOrder')->with('successTs','发起配送成功');
        }else{
            return redirect('@system/historyOrder')->with('errorTs','该订单状态不可发起配送');
        }
    }

    //打印操作，更新打印次数
    public function printOperation(){
        $o_id = input('o_id');
        if (!is_null($o_id)&&strlen($o_id)!=0){
            $order = Db::table('order')->find($o_id);
            if ($order){
                Db::table($order)->where(['o_id'=>$o_id])->setInc('o_print_times');
                return redirect('@system/historyOrder')->with('successTs','打印成功');
            }else{
                return redirect('@system/historyOrder')->with('errorTs','该订单不存在');
            }

        }else{
            return redirect('@system/historyOrder')->with('errorTs','参数错误');
        }
    }

    /**
     * 取消订单操作（接单状态使用，不用于用户申请退款状态中）
     * @return $this
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cancelOrderOperation(){
        $o_id = input('o_id');
        $orderModel = new Order();
        $order = $orderModel->find($o_id);
        if ($order['o_status']==2||$order['o_status']==8){
            $result = $orderModel->returnOrderOperation($order,7);      //退款，同时设置订单取消时间
            $goodsModel = new Goods();
            $goodsModel->decSalesVolumeByOid($o_id);
//            return $result;
            if ($result==0){
                return redirect('@system/handleOrder')->with('successTs','接单取消成功');
            }elseif ($result==1){
                return redirect('@system/handleOrder')->with('errorTs','订单取消失败');
            }
        }else{
            return redirect('@system/handleOrder')->with('errorTs','订单取消失败，当前状态不可取消');
        }

    }

    /**
     * 处理催单操作
     * @return $this
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function handelReminderOrder(){
        $o_id = request()->param('o_id');
        if (is_null($o_id)){
            return redirect('@system/reminderOrderView')->with('errorTs','参数错误');
        }else {
            $orderModel = new Order();
            $order = $orderModel->find($o_id);
            if ($order!=null){
                $o_reminder = $order['o_reminder'];
                if ($o_reminder==1){
                    $orderModel::update(['o_id'=>$o_id,'o_reminder'=>2]);
                    return redirect('@system/reminderOrderView')->with('successTs','催单处理成功');
                }else{
                    return redirect('@system/reminderOrderView')->with('errorTs','改状态不可处理催单');
                }
            }else{
                return redirect('@system/reminderOrderView')->with('errorTs','订单不存在，无法执行');
            }
        }
    }


    /**
     * 接受用户退款请求,用于用户主动退款时
     * @return $this
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function acceptReturnOrder(){
        $o_id = input('o_id');
        if (is_null($o_id)){
            return redirect('@system/cancelOrderView')->with('errorTs','参数错误');
        }else{
            $orderModel = new Order();
            $order = $orderModel->find($o_id);
            if ($order->o_return_status==1){
                $orderModel->returnOrderOperation($order,9);
                $orderModel->save(['o_return_status'=>2],['o_id'=>$o_id]);
                $goodsModel = new Goods();
                $goodsModel->decSalesVolumeByOid($o_id);
                return redirect('@system/cancelOrderView')->with('successTs','退款成功');
            }else{
                return redirect('@system/cancelOrderView')->with('errorTs','该订单状态无法同意退单');
            }
        }
    }


    /**
     * 拒绝用户退单请求
     * @return $this
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function rejectReturnOrder(){
        $o_id = input('o_id');
        if (is_null($o_id)){
            return redirect('@system/cancelOrderView')->with('errorTs','参数错误');
        }else{
            $orderModel = new Order();
            $order = $orderModel->find($o_id);
            if ($order->o_return_status==1){
                $orderModel->save(['o_return_status'=>3],['o_id'=>$o_id]);
                return redirect('@system/cancelOrderView')->with('successTs','已拒绝该订单退单请求');
            }else{
                return redirect('@system/cancelOrderView')->with('errorTs','该订单状态无法拒绝退单');
            }

        }
    }

}
