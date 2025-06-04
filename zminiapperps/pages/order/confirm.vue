<template>
	<view class="container-v confirm">
		<!-- 创建订单 -->
		<block v-if="from">
			<view class="take-type">
				<view>收货方式：</view>
				<uni-data-checkbox v-model="take_type" @change="takeTypeChange" :localdata="takeTypes"/>
			</view>
			<view v-if="take_type == 0" class="confirm-item address" @click="showStationPopup">
				<view class="address-info" v-if="station && station.gid">
					{{station.title}}
					<text>{{'\n'}}</text>
					{{station.province?station.province+", ":''}}{{station.city?station.city+', ':''}} {{station.district?station.district+', ':''}}
					{{station.street?station.street+', ':''}} {{station.detail}}
					<text>{{'\n'}}</text>
					{{station.link_name}} {{station.link_phone}}
				</view>
				<view class="address-info" v-else>请选择水站</view>
				<uni-icons type="right" color="#999999"></uni-icons>
			</view>
			<view v-else-if="take_type == 1" class="confirm-item address" @click="showAddressPopup">
				<view class="address-info" v-if="address && address.gid">
					{{address.province?address.province+", ":''}}{{address.city?address.city+', ':''}} {{address.district?address.district+', ':''}}
					{{address.street?address.street+', ':''}} {{address.detail}}
					<text>{{'\n'}}</text>
					{{address.link_name}}，{{address.link_phone}}
				</view>
				<view class="address-info" v-else>请选择收货地址</view>
				<uni-icons type="right" color="#999999"></uni-icons>
			</view>
		</block>
		<!-- 订单详情 -->
		<block v-else-if="orderInfo.sn">
			<view class="take-type v-mb-20">
				<view class="v-mr-20">收货方式：<text class="color-desc">{{orderInfo.take_type_txt}}</text></view>
			</view>
			<view v-if="station && station.gid" class="confirm-item address">
				<view class="address-info">
					{{station.title}}
					<text>{{'\n'}}</text>
					{{station.province?station.province+", ":''}}{{station.city?station.city+', ':''}} {{station.district?station.district+', ':''}}
					{{station.street?station.street+', ':''}} {{station.detail}}
					<text>{{'\n'}}</text>
					{{station.link_name}} {{station.link_phone}}
				</view>
				<!-- <uni-icons type="right" color="#999999"></uni-icons> -->
			</view>
			<view v-else-if="address && address.gid" class="confirm-item address">
				<view class="address-info">
					{{address.province?address.province+", ":''}}{{address.city?address.city+', ':''}} {{address.district?address.district+', ':''}}
					{{address.street?address.street+', ':''}} {{address.detail}}
					<text>{{'\n'}}</text>
					{{address.link_name}}，{{address.link_phone}}
				</view>
				<!-- <uni-icons type="right" color="#999999"></uni-icons> -->
			</view>
		</block>
		<view class="address-bottom-line"></view>
		<view class="confirm-item order-items">
			<view class="order-sn" v-if="orderInfo.sn">
				编号: <text class="value">{{orderInfo.sn}}</text>
				<text class="copy-sn" @click="copySn">点击复制</text>
			</view>
			<v-order-sub-item2 v-for="(item,index) in orderInfo.subs" :key="item.gid" :item="item"/>
		</view>
<!-- 		<view class="v-pb-10 v-pl-20 v-bb-3">
			<v-line-title title="订单信息"></v-line-title>
		</view> -->

		<view class="confirm-item order-infos">
			<v-order-info-item title="商品金额" :value="'￥' + (orderInfo.goods_amount||'0.00')"></v-order-info-item>
			<v-order-info-item title="优惠金额" :value="'-￥' + (orderInfo.discount_amount||'0.00')" color="#da4754" bold></v-order-info-item>
			<v-order-info-item title="商品实付金额" :value="'￥' + (orderInfo.real_amount||'0.00')" bold></v-order-info-item>
			<v-order-info-item title="服务费" :title2="freeDeliverTxt" title2color="#da4754" :value="'￥' + (orderInfo.deliver_amount||'0.00')"></v-order-info-item>
			<v-order-info-item title="支付方式" v-if="orderInfo.status == 0" :value="payTypes[pay_type]" type="pay_type" color="#999" @action="showPayTypePopup"></v-order-info-item>
			<v-order-info-item title="支付方式" v-else :value="payTypes[pay_type]" type="pay_type" color="#999" :showRightIcon="false"></v-order-info-item>
			<v-order-info-item title="优惠券" v-if="from" :value="coupon.title||'选择优惠券'" type="coupon" color="#999" @action="showCouponPopup"></v-order-info-item>
			<v-order-info-item title="优惠券" v-else-if="orderInfo.sn" :value="coupon && coupon.title||'未使用'" color="#999"></v-order-info-item>
		</view>
<!-- 		<view class="v-pb-10 v-pl-20 v-bb-3 v-mt-20">
			<v-line-title title="订单备注"></v-line-title>
		</view> -->
		<view class="confirm-item">
			<uni-easyinput v-if="from" type="textarea" v-model="remark" trim maxlength="100" placeholder="请输入您的备注" autoHeight ></uni-easyinput>
			<view v-else-if="orderInfo.sn" class="v-p-20 v-b-r-10"><text class="v-bold">订单备注：</text>{{remark}}</view>
		</view>
		
		<view class="confirm-item" v-if="orderInfo.deliver_remark">
			<view class="v-p-20 v-b-r-10"><text class="v-bold">配送说明：</text>{{orderInfo.deliver_remark}}</view>
		</view>
		<view class="confirm-item" v-if="orderInfo.deliver_images">
			<view class="v-p-20 v-b-r-10">
				<view class="v-mb-10 v-bold">配送照片：</view>
				<view class="deliver-images">
					<image class="deliver-image" @click="showImage(index)" :src="item" v-for="(item,index) in orderInfo.deliver_images" :key="index" mode="widthFix"></image>
				</view>
			</view>
		</view>
		<!-- 底部点位 -->
		<view style="width: 100rpx; height: 120rpx;"></view>
		
		<!-- 操作栏 -->
		<v-order-confirm-bar2 v-if="from" :money="orderInfo.pay_amount||''" :number="orderInfo.goods_total" action="create" @createOrder="createOrder"/>
		<v-order-confirm-bar2 v-else-if="orderInfo.sn && orderInfo.status == 0" :money="orderInfo.pay_amount||''" :number="orderInfo.goods_total" action="pay" @payOrder="payOrder"/>
		<v-order-confirm-bar2 v-else-if="orderInfo.sn && orderInfo.status > 0" :money="orderInfo.pay_amount||''" :number="orderInfo.goods_total" action="navto" @navto="back"/>
		
		<!-- 各种弹窗 -->
		<v-pay-type-select ref="payTypeSelect" :list="payTypes" @select="selectPayType"></v-pay-type-select>
		<v-coupon-select ref="couponSelectRef" :list="couponList" @select="selectCoupon" @reset="resetCoupon"></v-coupon-select>
		<v-station-select ref="stationSelectRef" :list="stationList" @select="selectStation"></v-station-select>
		<v-address-select ref="addressSelectRef" :list="addressList" @select="selectAddress" @reload="getAddressList"></v-address-select>
	</view>
</template>

<script setup>
	import {onLoad} from '@dcloudio/uni-app'
	import {ref} from 'vue'
	
	const $enum = ref(uni.$enum)
	
	const couponTitle = ref("去使用")
	/////////////////////////////////
	const from = ref()
	let goods_sn,goods_number
	let isCreated = false
	// 订单信息
	const orderInfo = ref({})
	
	const freeDeliverTxt = ref("")
	
	// 收货方式列表
	let takeTypes = ref([])
	
	// 创建订单用的、客户的购买信息
	let preParams = {}
	// 收货方式:0自提，1送货，-1时，用户必须手动选择一次,防止用户忽略此项设置,而下错订单
	let take_type = -1
	// 订单备注
	let remark = defineModel()
	
	// 使用的优惠券
	const couponSelectRef = ref()
	const couponList = ref([])
	const coupon = ref({})
	
	// 配送收货地址
	const addressSelectRef = ref()
	const addressList = ref([])
	let address = ref({})
	
	// 水站
	const stationSelectRef = ref()
	const stationList = ref([])
	let station = ref({})
	
	// 支付方式
	let payTypes = ref({})
	let pay_type = ref("")
	const payTypeSelect = ref() // 选择支付方式
	
	let sn = "" // 订单号，用于从列表返回订单进行支付操作
	onLoad((opts)=>{
		getInitDataConfirm()
		if(opts.sn){
			sn = opts.sn
			doDetailInfo()
		}else{
			doConfirmInfo(opts)
		}
	})
	
	const copySn = ()=>{
		const txt = "订单编号：" + orderInfo.value.sn
		uni.setClipboardData({
			data: txt,
			success(res) {
				uni.$tool.tip("订单编号已复制")
			}
		})
	}
	
	// 订单支付要做的操作
	function doDetailInfo(){
		uni.$tool.setNavTitle("订单查看")
		uni.$login.judgeLogin(()=>{
			getOrderDetail()
		})
	}
	
	function getOrderDetail(){
		uni.$tool.showLoading()
		uni.$http.post("v1/getOrderDetail/"+sn).then(res=>{
			uni.$tool.hideLoading()
			orderInfo.value = res.data.order
			remark.value = orderInfo.value.remark
			pay_type.value = orderInfo.value.pay_type||""
			station.value = res.data.station
			address.value = res.data.address
			coupon.value = res.data.coupon
			payTypes.value = res.data.payTypes
		})
	}
	
	// 订单确认要做的操作
	function doConfirmInfo(opts){
		from.value = opts.from || "" // from存在,则为创建订单
		preParams = {
			from: opts.from || "",
			goods_sn: opts.goods_sn || "",
			goods_number: opts.goods_number || "",
			address_gid: opts.agid || "",
			station_gid: opts.sgid || "",
			take_type: -1,
			pay_type: "",
			gift_card_sns: []
		}
		if(preParams.from == "goods"){
			if(!preParams.goods_sn || !preParams.goods_number) return uni.$tool.tip("请输入商品编号和数量")
		}else if(preParams.from != "cart") return uni.$tool.tip("路径参数错误，购物车不存在")
		
		uni.$login.judgeLogin(()=>{
			getOrderConfirmInfo()
		})
	}
	
	function getOrderConfirmInfo(){
		uni.$tool.showLoading()
		uni.$http.post("v1/getOrderConfirmInfoV2", preParams).then(res=>{
		// uni.$http.post("v1/getOrderConfirmInfo", preParams).then(res=>{
			uni.$tool.hideLoading()
			isCreated = false
			orderInfo.value = res.data.orderInfo
			freeDeliverTxt.value = res.data.freeDeliverTxt
			if(res.data.address){
				address.value = res.data.address
				preParams.address_gid = address.value.gid
			}
			
			if(res.data.station){
				station.value = res.data.station
				preParams.station_gid = station.value.gid
			}
			
			// 可用优惠券
			couponList.value = res.data.couponList
			if(res.data.coupon){
				coupon.value = res.data.coupon
				preParams.coupon_gid = coupon.value.gid
			}else{
				coupon.value = {}
			}
			
			// 支付方式（含可用实物卡）
			payTypes.value = res.data.payTypes
			// console.log(payTypes.value.giftCardList);
			// payTypes.value.giftCardList = res.data.payTypes.giftCardList
		}).catch(err=>{
			uni.$tool.alert(err.info, ()=>{
				if(preParams.from == 'cart'){
					uni.$tool.navto("/pages/cart/cart");
				}else{
					uni.$tool.back();
				}
			})
		})
	}
	
	function getInitDataConfirm(){
		uni.$http.post("v1/getInitDataConfirm", preParams).then(res=>{
			takeTypes.value = res.data.takeTypes
		})
	}
	
	function takeTypeChange({detail}){
		take_type = detail.value
		preParams.take_type = take_type
		if(take_type == $enum.value.order.TAKE_TYPE_SELF) preParams.address_gid = ""
		else if(take_type == $enum.value.order.TAKE_TYPE_DELIVER)preParams.station_gid = ""
		reload()
	}
	function reload(){
		uni.$login.judgeLogin(()=>{
			getOrderConfirmInfo()
		})
	}
	function showAddressPopup(){
		if(addressList.value.length==0) getAddressList(()=>addressSelectRef.value.open())
		else addressSelectRef.value.open();
		
	}
	async function getAddressList(cb){
		const res = await uni.$http.get("v1/getAddressList")
		if(res.code != 1) return uni.$tool.tip(res.info||"系统错误")
		addressList.value = res.data.list
		cb && cb()
	}
	// 收货地址选择
	function selectAddress(item){
		address.value = item
		preParams.address_gid = item.gid
		addressSelectRef.value.close()
	}
	
	// 水站显示列表
	function showStationPopup(){
		if(stationList.value.length==0) getStationList(()=>stationSelectRef.value.open())
		else stationSelectRef.value.open();

	}
	async function getStationList(cb){
		let params = {
			lat: 39.136465,
			lng: 117.209932
		}
		const res = await uni.$http.get("v1/getStationListWithDistance", params)
		if(res.code != 1) return uni.$tool.tip(res.info||"系统错误")
		stationList.value = res.data.list
		cb && cb()
	}
	// 水站选择
	function selectStation(item){
		station.value = item
		preParams.station_gid = item.gid
		stationSelectRef.value.close()
	}
	
	// 优惠券显示列表
	function showCouponPopup(){
		if(couponList.value.length==0) getCouponList(()=>couponSelectRef.value.open())
		else couponSelectRef.value.open()
	}
	async function getCouponList(cb){
		// const res = await uni.$http.get("v1/getAddressList")
		// if(res.code != 1) return uni.$tool.tip(res.info||"系统错误")
		// addressList.value = res.data.list
		cb && cb()
	}
	// 选择优惠券
	function selectCoupon(e){
		couponSelectRef.value.close()
		preParams.coupon_gid = e.gid
		reload()
	}
	// 重置优惠券
	function resetCoupon(){
		coupon.value = {}
		couponSelectRef.value.close()
		preParams.coupon_gid = ""
		reload()
	}
	
	// 显示支付方式列表
	async function showPayTypePopup(){
		// const res = await uni.$http.get("v1/getPayTypeInfos")
		// if(res.code != 1) return uni.$tool.tip(res.info||"系统错误")
		// payTypes.value.giftCardList = res.data.giftCardList
		payTypeSelect.value.open()
	}
	// 选择支付方式
	function selectPayType(e){
		// 实物卡支付需要多添加几个信息
		if(e.payType == 'giftcard'){
			if(e.cardHasMoney < orderInfo.value.pay_amount) return uni.$tool.tip("所选实物卡余额不足");
			preParams.gift_card_sns = e.selectedSn;
		}else{
			preParams.gift_card_sns = [];
		}
		pay_type.value = e.payType
		payTypeSelect.value.close()
		preParams.pay_type = pay_type.value
		

	}
	
	const payOrder = uni.$tool.debounce(()=>{
		doPayOrder(orderInfo.value.sn, pay_type.value)
	})
	
	const createOrder = uni.$tool.debounce(()=>{
		if(orderInfo.value.subs.length == 0 || orderInfo.value.goods_total == 0) return uni.$tool.tip("订单中没有商品")
		if(take_type == -1) return uni.$tool.tip("请选择收货方式")
		if(take_type == $enum.value.order.TAKE_TYPE_SELF && !preParams.station_gid) return uni.$tool.tip("请选择水站")
		if(take_type == $enum.value.order.TAKE_TYPE_DELIVER && !preParams.address_gid) return uni.$tool.tip("请选择收货地址")
		if(pay_type.value == "") return uni.$tool.tip("请选择有效的支付方式")
		if(pay_type.value == "giftcard" && preParams.gift_card_sns.length == 0) return uni.$tool.tip("请选择有效的实物卡", true, null, 2)
		
		if(isCreated) return uni.$tool.tip("订单已创建，请到我的订单列表继续支付")
		preParams.remark = remark.value
		
		// console.log(preParams.pay_type, preParams.gift_card_sns);
		// return false;
		uni.$http.post("v1/createOrderFromConfirmV2", preParams).then(res=>{
			if(!res.data.sn) return uni.$tool.tip('订单创建失败')
			isCreated = true
			doPayOrder(res.data.sn, pay_type.value, true)
		})
	})
	async function doPayOrder(sn, pay_type, isFirst=false){
		if(orderInfo.value.pay_amount < 0) return uni.$tool.tip('支付金额不能小于0')
		else if(orderInfo.value.pay_amount == 0) pay_type = "yue"
		switch(pay_type){
			case 'yue':
				uni.$http.post(`v1/payOrder/${sn}`, {pay_type}).then(res=>{
					uni.$tool.navto("/pages/pay/success?type=order&sn="+sn)
				})
			break;
			case 'giftcard':
				let gift_card_sns = preParams.gift_card_sns;
				uni.$http.post(`v1/payOrder/${sn}`, {pay_type, gift_card_sns}).then(res=>{
					uni.$tool.navto("/pages/pay/success?type=order&sn="+sn)
				})
			break;
			case 'weixin':
				const res = await uni.$api.getPayInfo("order", sn)
				if(res.code != 1) return uni.$tool.tip(res.info||"系统错误")
				
				uni.$tool.payment(res.data.payInfo, ()=>{
					uni.$tool.navto("/pages/pay/success?type=order&sn="+sn)
				},()=>{
					if(isFirst){
						uni.$tool.tip("订单已创建", true, ()=>{
							uni.$tool.redirect("/pages/order/list")
						})
					}
				})
			break;
			default:
				return uni.$tool.tip("请选择有效的支付方式")
		}
	}
	
	function showImage(index){
		uni.previewImage({
			current: index,
			urls: orderInfo.value.deliver_images
		})
	}
	
	
	function back(){
		uni.$tool.back()
	}
	
</script>

<style lang="scss" scoped>
	$br: 20rpx;
	.confirm{
		min-height: 100vh;
		background-color: #f5f5f5;
		.take-type{
			padding: 0 20rpx 20rpx 20rpx;
			display: flex;
			align-items: center;
			background-color: #fff;
		}
		.confirm-item{
			// padding: 0 20rpx;
			margin: 20rpx;
			border-radius: $br;
			background-color: #fff;
			.order-sn{
				padding: 10rpx 20rpx;
				.value{
					color: #999;
				}
				.copy-sn{
					padding: 10rpx 0 10rpx 10rpx;
					white-space: nowrap;
					color: $green;
					font-weight: bold;
				}
			}
			.deliver-images{
				display: flex;
				flex-wrap: wrap;
				.deliver-image{
					width: 200rpx;
					margin-right: 20rpx;
				}
			}
		}
		.address{
			display: flex;
			justify-content: space-between;
			align-items: center;
			min-height: 90rpx;
			padding: 20rpx;
			.address-info{
				margin-right: 20rpx;
				color: $uni-secondary-color;
			}
		}
		
		.address-bottom-line {
			height: 10rpx;
			background-image: linear-gradient(-45deg,
				#2979ff88 0,
				#2979ff88 10px,
				transparent 10px,
				transparent 15px,
				#e43d3388 15px,
				#e43d3388 25px,
				transparent 25px,
				transparent 30px);
			background-size: 40px 4upx;
			background-repeat: repeat-x;
			background-position: left bottom;
			background-color: #fff;
		}
		.order-items{
			padding: 20rpx;
			.order-sub-item:last-child{
				border: 0;
			}
		}
		.order-infos{
			
		}
		.order-remark{
			padding: 20rpx;
			margin-bottom: 120rpx;
			border-radius: $br;
		}
	}
</style>
