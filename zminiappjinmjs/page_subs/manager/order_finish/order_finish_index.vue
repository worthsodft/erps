<template>
	<view class="container-v order-finish-index">
		<block v-if="orderInfo.station_title">
			<uni-section type="line" titleFontSize="32rpx" title="水站信息"></uni-section>
			<view class="order-group">
				<view class="order-group-item">
					<view class="item-label">水站名称</view>
					<view class="item-value color-green v-bold">{{orderInfo.station_title}}</view>
				</view>
				<view class="order-group-item">
					<view class="item-label">水站联系人</view>
					<view class="item-value">{{orderInfo.station_link_name}}</view>
				</view>
				<view class="order-group-item">
					<view class="item-label">水站电话</view>
					<view class="item-value">{{orderInfo.station_link_phone}}</view>
				</view>
				<view class="order-group-item">
					<view class="item-label">水站地址</view>
					<view class="item-value">{{orderInfo.station_address}}</view>
				</view>
			</view>
		</block>
		<block v-if="orderInfo.take_name">
			<uni-section type="line" titleFontSize="32rpx" title="收货信息"></uni-section>
			<view class="order-group">
				<view class="order-group-item">
					<view class="item-label">收货人</view>
					<view class="item-value">{{orderInfo.take_name}}</view>
				</view>
				<view class="order-group-item">
					<view class="item-label">收货电话</view>
					<view class="item-value">{{orderInfo.take_phone}}</view>
				</view>
				<view class="order-group-item">
					<view class="item-label">收货地址</view>
					<view class="item-value">{{orderInfo.take_address}}</view>
				</view>
			</view>
		</block>
		<uni-section type="line" titleFontSize="32rpx" title="订单信息"></uni-section>
		<view class="order-group" v-if="orderInfo.id">
			<view class="order-group-item">
				<view class="item-label">订单编号</view>
				<view class="item-value">{{orderInfo.sn}}</view>
			</view>
			<view class="order-group-item">
				<view class="item-label">商品金额</view>
				<view class="item-value">{{orderInfo.goods_amount}}</view>
			</view>
			<view class="order-group-item">
				<view class="item-label">优惠金额</view>
				<view class="item-value color-red">-{{orderInfo.discount_amount||"0.00"}}</view>
			</view>
			<view class="order-group-item">
				<view class="item-label">商品实付</view>
				<view class="item-value">{{orderInfo.real_amount||"0.00"}}</view>
			</view>
			<view class="order-group-item">
				<view class="item-label">运费金额</view>
				<view class="item-value">{{orderInfo.deliver_fee||"0.00"}}</view>
			</view>
			<view class="order-group-item">
				<view class="item-label">支付方式</view>
				<view class="item-value">{{orderInfo.pay_type_txt}}</view>
			</view>
			<view class="order-group-item">
				<view class="item-label">优惠券</view>
				<view class="item-value">{{orderInfo.coupon?.title || ""}}</view>
			</view>
			<view class="order-group-item">
				<view class="item-label">取货方式</view>
				<view class="item-value color-red">{{orderInfo.take_type_txt}}</view>
			</view>
			<view class="order-group-item">
				<view class="item-label">订单状态</view>
				<view class="item-value color-green">
					<text class="v-tag" v-if="orderInfo.status == 1">{{orderInfo.status_txt}}</text>
					<text class="v-tag v-tag-black" v-else>{{orderInfo.status_txt}}</text>
				</view>
			</view>
		</view>
		<view class="order-group" v-else>
			<view class="color-desc">订单不存在</view>
		</view>
		<uni-section type="line" titleFontSize="32rpx" title="订单明细"></uni-section>
		<view class="order-group">
			<view class="order-group-item" v-for="(sub, index) in orderInfo.subs" :key="index">
				<view class="item-label">{{sub.goods_name}}</view>
				<view class="item-value">{{sub.goods_number}} ({{sub.goods_unit}}) × {{sub.goods_self_price}} = {{sub.goods_amount}}</view>
			</view>
		</view>
		
		<block v-if="orderInfo.remark">
			<uni-section type="line" titleFontSize="32rpx" title="订单备注"></uni-section>
			<view class="color-desc">{{orderInfo.remark}}</view>
		</block>
		<view>
			<v-button-w80 v-if="orderInfo.status == $enum.order.STATUS_DELIVERING" @action="finish" title="确认核销"></v-button-w80>
			<v-button-w80 v-else disabled title="确认核销"></v-button-w80>
		</view>
	</view>
</template>

<script setup>
	import {onLoad} from '@dcloudio/uni-app'
	import {ref} from 'vue'
	const $enum = ref(uni.$enum)
	const orderInfo = ref({})
	
	let sn = "";
	onLoad((opts)=>{
		sn = opts.sn||""
		if(!sn) return uni.$tool.tip("订单编号不存在", true, ()=>uni.$tool.navto("/page_subs/manager/order_finish/order_finish_welcome"))
		getDetail()
	})
	
	
	function getDetail(){
		uni.$http.post(`v1/getOrderDetailForFinish/${sn}`).then(res=>{
			orderInfo.value = res.data.order
		})
	}
	
	async function doFinish(){
		uni.$tool.showLoading()
		const res = await uni.$http.post("v1/finishOrder/"+sn)
		uni.$tool.hideLoading()
		if(res.code != 1) return uni.$tool.tip(res.info||"系统错误");
		uni.$tool.alert(res.info||"操作成功", ()=>{
			orderInfo.value = res.data.order
		});
	}
	
	const finish = uni.$tool.throttle(()=>{
		uni.$tool.confirm("确定要核销吗？", ()=>{
			doFinish()
		})
	})
	
</script>

<style lang="scss" scoped>
	.order-finish-index{
		padding: 0 20rpx;
		.order-group{
			.order-group-item{
				display: flex;
				justify-content: space-between;
				margin-bottom: 10rpx;
				.item-label{
					width: 400rpx;
					color: $font-gray;
				}
				.item-value{
					color: $font-gray;
				}
			}
		}
	}
</style>