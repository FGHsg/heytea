<?php
use think\Route;

/****************************index模块***********************************/
Route::get('/', 'index/index/index');//首页
//Index控制器（此控制器无拦截）
Route::group(['prefix' => 'index/index/'], function () {
	Route::any('wx_index', 'wx_index');//微信通知url
	Route::get('definedMenu', 'definedMenu');//定义微信菜单
	Route::get('dingshui', 'dingshui');//公众号入口
	Route::get('getUserOpenId', 'getUserOpenId');//获取用户openid
});


/****************************system模块**********************************/
//Login控制器
Route::group(['prefix' => 'system/login/'], function () {
	Route::any('system/login', 'login');//登录页面
	Route::any('system/logout', 'logout');//退出登录
});

//Admin控制器
Route::group(['prefix' => 'system/admin/'], function () {
	Route::get('system/adminList', 'adminList');//管理员列表
	Route::any('system/addAdmin', 'addAdmin');//添加管理员
	Route::any('system/editAdmin', 'editAdmin');//修改管理员
	Route::get('system/deleteAdmin', 'deleteAdmin');//删除管理员
	Route::post('system/uploadAdminHead', 'uploadAdminHead');//上传管理员头像
});

//Rule控制器
Route::group(['prefix' => 'system/rule/'], function () {
	Route::get('system/ruleList', 'ruleList');//权限列表
	Route::any('system/addRule', 'addRule');//添加权限
	Route::any('system/editRule', 'editRule');//修改权限
	Route::get('system/sortRule', 'sortRule');//权限排序
	Route::get('system/deleteRule', 'deleteRule');//删除权限
});

//Role控制器
Route::group(['prefix' => 'system/role/'], function () {
	Route::get('system/roleList', 'roleList');//角色列表
	Route::any('system/addRole', 'addRole');//添加角色
	Route::any('system/editRole', 'editRole');//修改角色
	Route::get('system/deleteRole', 'deleteRole');//删除角色
});

//Setting控制器
Route::group(['prefix' => 'system/setting/'], function () {
	Route::get('system/setting', 'setting');//网站设置页面
	Route::post('system/editSetting', 'editSetting');//编辑网站设置
});

//WxWelcome控制器
Route::group(['prefix' => 'system/wx_welcome/'], function () {
	Route::any('system/welcomeSetting', 'welcomeSetting');//微信关注回复设置
});

//WxMenu控制器
Route::group(['prefix' => 'system/wx_menu/'], function () {
	Route::get('system/menuList', 'menuList');//菜单列表
	Route::any('system/addMenu', 'addMenu');//添加菜单
	Route::any('system/editMenu', 'editMenu');//编辑菜单
	Route::get('system/deleteMenu', 'deleteMenu');//删除菜单
	Route::get('system/sortMenu', 'sortMenu');//菜单排序
	Route::get('system/pushWxMenu', 'pushWxMenu');//推送微信菜单
});

//WxMaterial控制器
Route::group(['prefix' => 'system/wx_material/'], function () {
	Route::get('system/getNewsList', 'getNewsList');//获取永久图文素材列表
	Route::get('system/getImageList', 'getImageList');//获取永久素材图片列表
	Route::get('system/get_contents','get_contents');//输出文件图片流
});

//Comment控制器
Route::group(['prefix' => 'system/comment_controller/'], function(){
    Route::any('system/commentListForAdmin', 'commentListForAdmin');//获取管理端的茶评列表
    Route::any('system/updateCommentStatus', 'updateCommentStatus');//管理端修改茶评状态
});

//OrderController
Route::group(['prefix' => 'system/order_controller/'], function(){
//    Route::any('system/getOrderViewInfo','getOrderViewInfo');
    Route::any('system/handleOrder','handleOrder');//接单视图
    Route::any('system/reminderOrderView','reminderOrderView');//催单列表视图
    Route::any('system/cancelOrderView','cancelOrderView');//退单取消订单审核视图
    Route::any('system/historyOrder','historyOrder');//历史订单视图
    Route::any('system/orderCommentView','orderCommentView');//订单评论视图

    Route::any('system/handleOrderOperation','handleOrderOperation');//接单操作
    Route::any('system/cancelOrderOperation','cancelOrderOperation');//取消订单操作
    Route::any('system/handelReminderOrder','handelReminderOrder');//处理催单操作
    Route::any('system/acceptReturnOrder','acceptReturnOrder');//接受用户退款请求
    Route::any('system/rejectReturnOrder','rejectReturnOrder');//拒绝用户退单请求
    Route::any('system/sendOrderOperation','sendOrderOperation');//发起配送操作
    Route::any('system/printOperation','printOperation');//打印操作
    Route::any('system/changeOCStatus','changeOCStatus');//更改订单评价状态
});

//Tea控制器
Route::group(['prefix' => 'system/tea_controller/'], function () {
    Route::any('system/teaOperationList', 'teaOperationList');//管理端茶叶记录
    Route::any('system/setTea', 'setTea');//管理端茶叶设置
});
//UserController
Route::group(['prefix' => 'system/user_controller/'], function(){

});

//MemberController
Route::group(['prefix' => 'system/member_controller/'], function(){
    Route::any('system/userList','userList');//用户列表视图
    Route::any('system/addressManage','addressManage');//地址管理
    Route::any('system/gradeManage','gradeManage');//等级管理
    Route::any('system/privilegeManage','privilegeManage');//特权管理
    Route::any('system/addPrivilegeView','addPrivilegeView');//添加特权视图
    Route::any('system/editPrivilegeView','editPrivilegeView');//编辑特权视图
    Route::any('system/consumptionHistory','consumptionHistory');//消费记录视图

    Route::any('system/addGrade','addGrade');//添加等级接口
    Route::any('system/editGrade','editGrade');//修改等级接口
    Route::any('system/deleteGrade','deleteGrade');//删除等级接口
    Route::any('system/addPrivilegeOperation','addPrivilegeOperation');//特权添加操作接口
    Route::any('system/editPrivilegeOperation','editPrivilegeOperation');//特权修改操作接口
    Route::any('system/deletePrivilegeOperation','deletePrivilegeOperation');//特权删除操作接口
});

//GoodsController
Route::group(['prefix' => 'system/goods_controller/'], function(){
    Route::any('system/goodsClass','goodsClass');//商品分类视图
    Route::any('system/goodsList','goodsList');//商品列表视图
    Route::any('system/addGoodsView','addGoodsView');//添加商品视图
    Route::any('system/editGoodsView','editGoodsView');//编辑商品视图
    Route::any('system/goodsDetail','goodsDetail');//商品详情视图
    Route::any('system/goodsAttribute','goodsAttribute');//商品属性视图


    Route::any('system/addGoodsClass','addGoodsClass');//添加商品分类操作
    Route::any('system/editGoodsClass','editGoodsClass');//编辑商品分类操作
    Route::any('system/deleteGoodsClass','deleteGoodsClass');//删除商品分类操作
    Route::any('system/addGoodsAttributeOperation','addGoodsAttributeOperation');//添加商品属性
    Route::any('system/editGoodsAttributeOperation','editGoodsAttributeOperation');//修改商品属性操作
    Route::any('system/deleteGoodsAttributeOperation','deleteGoodsAttributeOperation');//删除商品属性操作
    Route::any('system/addGoodsOperation','addGoodsOperation');//添加商品操作
    Route::any('system/editGoodsOperation','editGoodsOperation');//编辑商品信息操作
    Route::any('system/grounding','grounding');//商品上下架操作
    Route::any('system/setRecommend','setRecommend');//设置商品是否推荐
    Route::any('system/deleteGoods','deleteGoods');//删除商品操作

//    Route::any('system/editGoodsSpecification','editGoodsSpecification');//更新商品规格
    Route::any('system/saveGoodsCarouselFigure','saveGoodsCarouselFigure');//保存商品轮播图（单张）
    Route::any('system/saveGoodsImg','saveGoodsImg');//保存商品图片（单张）；
//    Route::any('system/editGoodsCarouselFigure','editGoodsCarouselFigure');//更新商品轮播图

});

//BusinessController
Route::group(['prefix' => 'system/business_controller/'], function(){
    Route::any('system/businessSetting', 'businessSetting');//店铺信息设置
    Route::any('system/editBusinessInformation','editBusinessInformation');//修改店铺信息

    Route::any('system/editBusiness','editBusiness');//设置商铺操作

});

//GoodsCouponController
Route::group(['prefix' => 'system/goods_coupon_controller/'], function () {
    Route::any('system/goodsCouponList', 'goodsCouponList');//管理端兑换设置列表
    Route::any('system/addGoodsCoupon', 'addGoodsCoupon');//管理端新增兑换券
    Route::any('system/updateGoodsCoupon', 'updateGoodsCoupon');//管理端修改兑换券
    Route::any('system/deleteGoodsCoupon', 'deleteGoodsCoupon');//管理端删除兑换券
});

//CouponController
Route::group(['prefix' => 'system/coupon_controller/'], function () {
    Route::any('system/couponList', 'couponList');//管理端获取兑换管理列表
    Route::any('system/updateCouponStatus', 'updateCouponStatus');//管理端修改兑换状态
});

//CarouselFigureController
Route::group(['prefix' => 'system/carousel_figure_controller/'], function () {
    Route::any('system/carouselFigureList', 'carouselFigureList');//管理端首页轮播图列表
    Route::any('system/addCarouselFigure', 'addCarouselFigure');//管理端新增首页轮播图
    Route::any('system/updateCarouselFigure', 'updateCarouselFigure');//管理端修改首页轮播图
    Route::any('system/deleteCarouselFigure', 'deleteCarouselFigure');//管理端删除首页轮播图
});


//留言管理
Route::group(['prefix' => 'system/message_controller/'],function (){
    Route::any('system/MessageManage','MessageManage');//留言管理视图

    Route::any('system/handelMessage','handelMessage');//留言处理
});


/**************************************以下为客户端接口**********************************************************/


//Comment控制器
Route::group(['prefix' => 'customer/comment_controller/'], function(){
    Route::post('customer/commentListForCustomer', 'commentListForCustomer');//获取客户端的茶评列表
    Route::post('customer/myCommentList', 'myCommentList');//获取客户端的我的茶评列表
    Route::post('customer/addComment', 'addComment');//新增茶评
});

//OrderController
Route::group(['prefix' => 'customer/order_controller/'], function(){
    Route::post('customer/createOrder', 'createOrder');//创建订单
    Route::post('customer/generateConfig','generateConfig');//客户端接收签名验证
    Route::post('customer/jsapi','jsapi');//支付操作
    Route::post('customer/paySuccess','paySuccess');//支付成功确认接口
    Route::post('customer/notify','notify');//异步通知接口
    Route::post('customer/confirmOrder','confirmOrder');//用户确认订单变成待评价状态（5）
    Route::post('customer/reminderOrder','reminderOrder');//催单操作(10)
    Route::post('customer/commentOrder','commentOrder');//订单评论
    Route::post('customer/cancelOrder','cancelOrder');//取消订单

    Route::post('customer/getOrderInfo','getOrderInfo');//获取对应订单信息
    Route::post('customer/getOrderInfoList','getOrderInfoList');//获取会员对应所有订单信息
    Route::post('customer/getOrderInfoListByStatus','getOrderInfoListByStatus');//获取对应状态订单列表信息
    Route::post('customer/getMyOrderCommentList','getMyOrderCommentList');//获取用户订单评价
});

//TeaController
Route::group(['prefix' => 'customer/tea_controller/'], function () {
    Route::post('customer/getTeaLeaves', 'getTeaLeaves');//客户端采茶叶
    Route::post('customer/getDetailTeaOperation', 'getDetailTeaOperation');//客户端获取茶树操作记录（茶叶明细）
    Route::post('customer/shareTeaLeaves', 'shareTeaLeaves');//客户端分享茶叶
    Route::post('customer/receiveTeaLeaves', 'receiveTeaLeaves');//客户端领取茶叶
});
//UserController
Route::group(['prefix' => 'customer/user_controller/'], function(){
    Route::post('customer/login','login');//用户登录获取用户信息
    Route::post('customer/createUser', 'createUser');//创建更新用户信息
});

//MemberController
Route::group(['prefix' => 'customer/member_controller/'], function(){
    Route::post('customer/getMemberInfo', 'getMemberInfo');//更新获取用户信息

    Route::post('customer/getPrivilegeInfo','getPrivilegeInfo');//获取用户特权信息

    Route::post('customer/addAddress','addAddress');//创建会员地址信息
    Route::post('customer/editAddress','editAddress');//编辑会员地址信息
    Route::post('customer/deleteAddress','deleteAddress');//删除会员地址信息
    Route::post('customer/getAddressInfo','getAddressInfo');//获取会员具体地址信息
    Route::post('customer/getAddressList','getAddressList');//获取会员地址信息列表

});

//GoodsController
Route::group(['prefix' => 'customer/goods_controller/'], function(){
    Route::get('customer/getGoodsList', 'getGoodsList');//获取商品列表
    Route::get('customer/getGoodClassList','getGoodClassList');//获取商品分类列表
    Route::post('customer/getGoodsInfo','getGoodsInfo');//获取对应商品信息
});

//GoodsCouponController
Route::group(['prefix' =>'customer/goods_coupon_controller/'], function () {
    Route::get('customer/getExchangeList', 'getExchangeList');//客户端获取兑换列表
    Route::post('customer/exchangeGoodsCoupon', 'exchangeGoodsCoupon');//客户端茶叶兑换兑换券
});

//CouponController
Route::group(['prefix' => 'customer/coupon_controller/'], function () {
    Route::post('customer/getCouponList', 'getCouponList');//客户端获取兑换券列表
});

//BusinessController
Route::group(['prefix' => 'customer/business_controller/'], function(){
    Route::post('customer/setBusiness','setBusiness');//设置商铺信息
    Route::post('customer/editBusiness','editBusiness');//修改商铺时间

    Route::get('customer/getBusinessInfo','getBusinessInfo');//获取店铺信息
});

//CarouselFigureController
Route::group(['prefix' => 'customer/carousel_figure_controller/'], function () {
    Route::get('customer/getCarouselFigure', 'getCarouselFigure');//客户端获取首页轮播图
});

//MessageController
Route::group(['prefix' => 'customer/message_controller/'],function (){
    Route::post('customer/sendMessage','sendMessage');//发送留言
});





Route::miss('system/error/notFound','GET');//404未找到页面