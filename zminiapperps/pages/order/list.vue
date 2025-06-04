<template>
	<view class="container-v order-list">
		<view class="order-tabs">
			<uni-segmented-control :current="current" :values="orderTabs" style-type="text"
				active-color="#5aa1d8" @clickItem="select" />
			<view class="v-bb-1"></view>
		</view>
		<swiper class="order-swiper" :current="current" @change="swiperChange">
			<swiper-item v-for="(item,index) in group" :key="index">
				<view v-if="current == 2 && applyInvoiceList.length > 0" class="invoice-btn" @click.stop="showInvoiceFormPopup">
					<view class="v-btn btn-sm btn-red">申请开票</view>
				</view>
				<scroll-view :scroll-y="isHidePopup" style="height: 100%" @scrolltolower="scrolltolower"
					refresher-enabled :refresher-triggered="isLoading"
					@refresherrefresh="reload"
					@refresherrestore="reloadStop"
					@refresherabort="reloadStop">
					<view class="order-item" v-for="(vo,index) in item.list" :key="vo.id">
						<v-order-item2 @showPopup="isHidePopup = false"  @hidePopup="isHidePopup = true" :item="vo"
						@takeSuccess="takeSuccess" @cancelSuccess="cancelSuccess" @refundApplySuccess="refundApplySuccess"
						@showFinishOrderPopup="showFinishOrderPopup"
						@showRefundReasonPopup="showRefundReasonPopup"
						@selectInvoice="selectInvoice"
						:isInvoice="applyInvoiceList.includes(vo.sn)"
						/>
					</view>
					<uni-load-more :status="item.loadStatus" @click="getList"></uni-load-more>
				</scroll-view>
			</swiper-item>
		</swiper>
		<!-- 退款原因 -->
		<uni-popup ref="refundReasonPopup" type="dialog">
			<uni-popup-dialog mode="input" :maxlength="50" :beforeClose="true" title="退款原因" placeholder="请输入退款原因"
				@close="refundReasonPopup.close()" @confirm="refundConfirm"></uni-popup-dialog>
		</uni-popup>
		<!-- 核销二维码 -->
		<uni-popup ref="finishQrcodePopup" background-color="#fff">
			<view class="popup-content">
				<view class="popup-title">核销二维码</view>
				<image :src="currOrder.qrcode" mode="widthFix"></image>
				<view>{{currOrder.sn}}</view>
				<view class="finish-qrcode-tip">{{finishQrcodeTip}}</view>
				<view class="v-w100 v-flex-center" style="width: 100%;">
					<v-button-w80 title="关 闭" :style="{marginLeft:0,width:'100%'}" @action="finishQrcodePopup.close()"></v-button-w80>
				</view>
			</view>
		</uni-popup>
		<!-- 申请开票 -->
		<uni-popup ref="applyInvoiceFormPopup" background-color="#fff">
			<view class="popup-content">
				<view class="popup-title">申请开票信息</view>
				<view class="form-item">
					<uni-segmented-control style="width: 300rpx;" activeColor="#6abfff" :current="invoiceFormData.buyer_type??0" :values="['自然人','公司']" @clickItem="buyerTypeChange" styleType="button"></uni-segmented-control>
				</view>
				<view class="form-item">
					<text class="">订单数量：</text>
					<view class="form-item-input">
						<text class="v-bold">{{applyInvoiceList.length}}</text>
					</view>
				</view>
				<view class="form-item">
					<text class="form-item-title require">开票类型：</text>
					<view class="form-item-input">
						<uni-data-checkbox v-model="invoiceFormData.invoice_type" :localdata="[{text:'普通发票',value:0},{text:'专用发票',value:1, disable: invoiceFormData.buyer_type == 0}]" />
					</view>
				</view>
				<view class="form-item">
					<text class="form-item-title require">开票名称：</text>
					<view class="form-item-input"><uni-easyinput v-model="invoiceFormData.title" :clearable="false" :style="invoiceInputStyle"></uni-easyinput></view>
				</view>
				<view class="form-item">
					<text class="form-item-title" :class="{require:invoiceFormData.buyer_type==1}">公司税号：</text>
					<view class="form-item-input"><uni-easyinput v-model="invoiceFormData.taxno" :clearable="false" :style="invoiceInputStyle"></uni-easyinput></view>
				</view>
				<view class="form-item">
					<text class="form-item-title">公司地址：</text>
					<view class="form-item-input"><uni-easyinput v-model="invoiceFormData.address" :clearable="false" :style="invoiceInputStyle"></uni-easyinput></view>
				</view>
				<view class="form-item">
					<text class="form-item-title">公司电话：</text>
					<view class="form-item-input"><uni-easyinput v-model="invoiceFormData.phone" :clearable="false" :style="invoiceInputStyle"></uni-easyinput></view>
				</view>
				<view class="form-item">
					<text class="form-item-title">开户银行：</text>
					<view class="form-item-input"><uni-easyinput v-model="invoiceFormData.bank_name" :clearable="false" :style="invoiceInputStyle"></uni-easyinput></view>
				</view>
				<view class="form-item">
					<text class="form-item-title">银行账号：</text>
					<view class="form-item-input"><uni-easyinput v-model="invoiceFormData.bank_account" :clearable="false" :style="invoiceInputStyle"></uni-easyinput></view>
				</view>
				<view class="form-item">
					<text class="form-item-title require">收票邮箱：</text>
					<view class="form-item-input"><uni-easyinput v-model="invoiceFormData.email" :clearable="false" :style="invoiceInputStyle"></uni-easyinput></view>
				</view>
				<view class="v-w100 v-flex-center" style="width: 100%;">
					<v-button-w80 title="确定提交" :style="{marginLeft:0,width:'100%'}" @action="submitApplyInvoice"></v-button-w80>
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script setup>
	import {onLoad} from '@dcloudio/uni-app'
	import {ref} from 'vue'
	const invoiceInputStyle = ref({width: "400rpx"})
	const orderTabs = ref()
	const orderStausIndexMap = {0:0,1:1,2:2,9:3}
	const group = ref([
		{
			loadStatus: "more",
			page: 1,
			total: 0,
			list: []
		}, {
			loadStatus: "more",
			page: 1,
			total: 0,
			list: []
		}, {
			loadStatus: "more",
			page: 1,
			total: 0,
			list: []
		}, {
			loadStatus: "more",
			page: 1,
			total: 0,
			list: []
		}]
	)
	const isHidePopup = ref(true)
	const current = ref(0)
	const isLoading = ref(false)
	
	const refundReasonPopup = ref()
	const currOrder = ref({})
	const finishQrcodePopup = ref()
	const finishQrcodeTip = ref("")
	const applyInvoiceFormPopup = ref()
	const applyInvoiceList = ref([])
	
	const invoiceFormData = ref({buyer_type:1,invoice_type:1})
	let formData = uni.$tool.cache("invoiceFormData")
	if(formData) invoiceFormData.value = formData
	
	onLoad((opts)=>{
		current.value = orderStausIndexMap[+opts.status||0]
		getTabsData()
		getList()
	})
	
	function selectInvoice(sn){
		let pos = applyInvoiceList.value.indexOf(sn);
		if(pos == -1){
			applyInvoiceList.value.push(sn);
		}else{
			applyInvoiceList.value.splice(pos, 1)
		}
	}
	
	// 显示申请开票
	function showInvoiceFormPopup(e){
		currOrder.value = e
		invoiceFormData.value.sns = applyInvoiceList.value
		applyInvoiceFormPopup.value.open()
	}
	function showRefundReasonPopup(e){
		currOrder.value = e
		refundReasonPopup.value.open()
	}
	function showFinishOrderPopup(e){
		currOrder.value = e
		finishQrcodePopup.value.open()
	}
	
	function buyerTypeChange({currentIndex}){
		invoiceFormData.value.buyer_type = currentIndex
		if(currentIndex == 0) invoiceFormData.value.invoice_type = 0
	}
	function submitApplyInvoice(){
		if(!validInvoiceForm()) return;
		let data = invoiceFormData.value;
		uni.$tool.cache("invoiceFormData", invoiceFormData.value)
		uni.$tool.confirm("确认所填写信息全部正确，并提交开票申请吗？", ()=>{
			uni.$http.post("v1/invoiceApply", invoiceFormData.value).then(res=>{
				uni.$tool.tip(res.info||"操作成功")
				applyInvoiceFormPopup.value.close()
				applyInvoiceList.value = [];
				reload()
			})
		})
	}
	
	function validInvoiceForm(){
		let formData = invoiceFormData.value
		if(applyInvoiceList.value.length == 0) return uni.$tool.tip("请选择要开票的订单")
		if(![0,1].includes(formData.buyer_type)) return uni.$tool.tip("请选择购买方类型")
		if(![0,1].includes(formData.invoice_type)) return uni.$tool.tip("请选择开票类型")
		if(formData.buyer_type == uni.$enum.invoice.BUYER_TYPE_P && formData.invoice_type == uni.$enum.invoice.INVOICE_TYPE_S) return uni.$tool.tip("个人不能开具专用发票")
		
		if(!formData.title) return uni.$tool.tip("请输入开票名称")
		if(formData.buyer_type == uni.$enum.invoice.BUYER_TYPE_E && !formData.taxno)  return uni.$tool.tip("请输入公司税号")
		if(!formData.email) return uni.$tool.tip("请输入收票邮箱")
		if(!uni.$tool.isEmail(formData.email)) return uni.$tool.tip("请输入正确的邮箱地址")
		
		return true;
	}
	
	function refundConfirm(reason){
		if(!reason) return uni.$tool.tip("请输入退款原因")
		doRefundApply(reason)
	}
	function doRefundApply(reason){
		uni.$http.post("v1/refundApply/"+currOrder.value.sn, {reason}).then(res=>{
			if(res.code == 1) uni.$tool.success(res.info, true, ()=>{
				refundApplySuccess()
			});
			else uni.$tool.tip(res.info || "申请退款失败");
		})
	}
	
	function takeSuccess(){
		reload()
	}
	function cancelSuccess(){
		reload()
	}
	function refundApplySuccess(){
		refundReasonPopup.value.close()
		reload()
		setTimeout(()=>{
			current.value = 3
			reload()
		}, 1000)
	}
	
	function getTabsData(){
		uni.$http.get("v1/getOrderTabsData").then(res=>{
			orderTabs.value = res.data.tabsData
			finishQrcodeTip.value = res.data.finishQrcodeTip
		})
	}
	function getList(type){
		let groupItem = group.value[current.value]
		
		if(groupItem.loadStatus != "more") return
		groupItem.loadStatus = "loading"
		
		let status = uni.$tool.invert(orderStausIndexMap)[current.value] || 0
		let params = {
			page: groupItem.page
		}
		uni.$http.get("v1/getOrderPageDataByStatus/"+status, params).then(res=>{
			if(res.code != 1) return uni.$tool.tip(res.info||"系统错误")
			let pageData = res.data.pageData
			if(pageData.total == 0){
				groupItem.loadStatus = "nomore"
				groupItem.list = []
				group.value[current.value] = groupItem
				return
			}
			if(pageData.data.length == 0){
				groupItem.loadStatus = "nomore"
				group.value[current.value] = groupItem
				return
			}
			if(type == "reset") groupItem.list = [...pageData.data]
			else groupItem.list = [...(groupItem.list), ...pageData.data]
			if(groupItem.total == 0) groupItem.total = pageData.total
			if(groupItem.list.length >= groupItem.total){
				groupItem.loadStatus = "nomore"
			}else{
				groupItem.loadStatus = "more"
				groupItem.page++
			}
			group.value[current.value] = groupItem
			isLoading.value = false
		})
	}
	
	function reset(){
		let groupItem = group.value[current.value]
		groupItem.loadStatus = "more"
		groupItem.page = 1
		groupItem.total = 0
		group.value[current.value] = groupItem
	}
	
	function reload(){
		if(isLoading.value) return
		isLoading.value = true
		setTimeout(() => isLoading.value = false , 500);
		reset()
		getList("reset")
	}
	
	function reloadStop() {
		isLoading.value = false
	}
	
	function select({currentIndex}){
		current.value = currentIndex
	}
	
	function swiperChange({detail}){
		applyInvoiceList.value = [];
		if(current.value != detail.current) current.value = detail.current
		if(group.value[current.value].total == 0){
			getList()
		}
	}
	
	function scrolltolower(){
		getList()
	}
	
	
</script>

<style lang="scss" scoped>
	$tabHeight: 80rpx;
	.order-list{
		.order-tabs{
			height: $tabHeight;
			position: fixed;
			width: 100%;
			align-items: center;
			justify-content: flex-start;
			color: $uni-base-color;
			font-size: 30rpx;
			z-index: 9;
			background-color: #fff;
		}
		.order-swiper{
			height: calc(100vh - $tabHeight - calc(44px + env(safe-area-inset-top)));
			margin-top: $tabHeight;
			.invoice-btn{
				display: flex;
				justify-content: flex-end;
				position: absolute;
				top: 0;
				right: 10rpx;
				z-index: 999;
				.v-btn{
					padding: 5rpx 10rpx;
					font-size: 0.9em;
				}
			}
			.order-item{
				&:first-child{
					margin-top: 40rpx;
				}
			}
		}
	}
	.popup-content{
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		padding: 15px;
		background-color: #fff;
		box-sizing: border-box;
		.popup-title{
			font-size: 30rpx;
			margin-bottom: 20rpx;
		}
		.finish-qrcode-tip{
			color: $uni-warning;
			margin-top: 10rpx;
		}
		.form-item{
			display: flex;
			justify-content: center;
			align-items: center;
			font-size: 24rpx;
			margin-bottom: 10rpx;
			.form-item-title{
				width: 150rpx;
				&.require::before{
					content: "*";
					color: red;
				}
			}
			.form-item-input{
				
			}
		}
	}
</style>
