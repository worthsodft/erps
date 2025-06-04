<template>
	<view class="order-item">
		<view class="info">
			<view class="title" @click="confirm">订单编号：<text class="color-desc">{{item.sn}}</text>
<!-- 				<view class="take-type">
					<uni-tag size="mini" type="success" v-if="item.take_type == $enum.order.TAKE_TYPE_SELF" text="自提"></uni-tag>
					<uni-tag size="mini" type="primary" v-if="item.take_type == $enum.order.TAKE_TYPE_DELIVER" text="配送"></uni-tag>
				</view> -->
			</view>
			<view class="order-subs" @click="confirm">
				<view class="order-sub" v-for="(sub,index) in item.subs" :key="sub.id">
					<image class="pic" :src="sub.goods_cover" mode="aspectFill"></image>
				</view>
			</view>
			<view class="info-item">订单金额：<text>￥{{item.pay_amount}}</text></view>
			<view class="info-item">下单时间：<text>{{item.create_at}}</text></view>
			<view class="info-item">支付时间：<text>{{item.pay_at}}</text></view>
			<view class="info-item">配送方式：
				<text v-if="item.take_type == $enum.order.TAKE_TYPE_SELF">自提</text>
				<text v-if="item.take_type == $enum.order.TAKE_TYPE_DELIVER">配送</text>
			</view>
		</view>
		<view class="operate">
			<view class="order-status" :class="{vW80: item.status == $enum.order.STATUS_REFUND}">
				<text v-if="item.status == $enum.order.STATUS_UNPAY" class="color-red">待支付</text>
				<text v-else-if="item.status == $enum.order.STATUS_DELIVERING" class="color-org">配送中</text>
				<text v-else-if="item.status == $enum.order.STATUS_FINISHED" class="color-blue">已完成</text>
				<view v-else-if="item.status == $enum.order.STATUS_REFUND" class="color-desc">
					退 款 ({{item.refund_status_txt}})
					<text class="v-ml-10" v-if="item.refund_feedback_msg">审核反馈：{{item.refund_feedback_msg}}</text>
				</view>
			</view>
			<view class="order-btns">
				<!-- 未支付 -->
				<block v-if="item.status == $enum.order.STATUS_UNPAY">
					<view @click.stop="cancel" class="btn-sm btn-normal v-mr-10">取消订单</view>
					<view @click.stop="confirm" class="btn-sm btn-red">立即支付</view>
				</block>
				<!-- 配送中 -->
				<block v-else-if="item.status == $enum.order.STATUS_DELIVERING">
					<view @click.stop="showQrcode" v-if="item.take_type == $enum.order.TAKE_TYPE_SELF" class="btn-sm btn-normal v-mr-10">核销二维码</view>
					<view @click.stop="refund" class="btn-sm btn-normal v-mr-10">申请退款</view>
					<view @click.stop="take" class="btn-sm btn-org">确认收货</view>
				</block>
				<!-- 已完成 -->
				<block v-else-if="item.status == $enum.order.STATUS_FINISHED">
					<view @click.stop="refund" class="btn-sm btn-normal v-mr-10">申请退款</view>
					<view class="btn-sm btn-blue" @click.stop="confirm">查看详情</view>
				</block>
				<!-- 退款 -->
				<view v-else-if="item.status == $enum.order.STATUS_REFUND" class="btn-sm btn-normal" @click.stop="confirm">订单详情</view>
			</view>
		</view>


	</view>
</template>

<script setup>
	import {ref} from "vue";
	
	const $enum = ref(uni.$enum)
	
	const cartTrans = ref("")
	
	const props = defineProps({
		item: Object
	})
	const number = defineModel() 
	number.value = props.item.min_buy_number||1
	
	const refundReasonPopup = ref()
	const qrcode = ref()
	
	const emit = defineEmits(["takeSuccess","cancelSuccess","refundApplySuccess","showFinishOrderPopup","showRefundReasonPopup"])
	
	async function showQrcode(){
		let qrcode = await getQrcode()
		emit("showFinishOrderPopup", {qrcode, sn:props.item.sn})
	}
	
	async function getQrcode(){
		const res = await uni.$http.post("v1/getFinishOrderQrcode/"+props.item.sn)
		return res.data.url
	}
	
	// 订单详情
	function detail(){
		uni.$tool.navto("/pages/order/detail?sn="+props.item.sn)
	}
	
	// 订单支付
	function confirm(){
		uni.$tool.navto("/pages/order/confirm?sn="+props.item.sn)
	}
	
	function refund(){
		emit("showRefundReasonPopup", {sn:props.item.sn})
	}
	
	function cancel(){
		uni.$tool.confirm("确定要取消订单吗？", ()=>{
			doCancelOrder()
		})
	}
	function doCancelOrder(){
		uni.$http.post("v1/cancelOrder/"+props.item.sn).then(res=>{
			if(res.code == 1) uni.$tool.success(res.info, true, ()=>{
				emit("cancelSuccess", res.data.order)
			});
			else uni.$tool.tip(res.info || "取消失败");
		})
	}
	function take(){
		uni.$tool.confirm("确定要进行收货操作吗？", ()=>{
			doTakeOrder()
		})
	}
	
	function doTakeOrder(){
		uni.$http.post("v1/takeOrder/"+props.item.sn).then(res=>{
			if(res.code == 1) uni.$tool.success(res.info, true, ()=>{
				emit("takeSuccess", res.data.order)
			});
			else uni.$tool.tip(res.info || "收货失败");
		})
	}

	
	
</script>

<style lang="scss" scoped>
	@import '@/static/assets/all.scss';
	.order-item{
		width: 100%;
		background-color: $bg-gray;
		display: flex;
		margin-bottom: 20rpx;
		flex-shrink: 0;
		font-size: 26rpx;
		box-sizing: border-box;
		flex-direction: column;
		padding: 20rpx 0;
		.info{
			width: 100%;
			// margin-left: 20rpx;
			padding: 0 40rpx;
			display: flex;
			flex-direction: column;
			// padding: 10rpx;
			box-sizing: border-box;
			.title{
				white-space: nowrap;
				.take-type{
					margin-left: 30rpx;
					display: inline;
				}
			}
			.order-subs{
				display: flex;
				margin: 10rpx 0;
				.order-sub{
					width: 100rpx;
					height: 100rpx;
					margin-right: 10rpx;
					.pic{
						width: 100%;
						height: 100%;
					}
				}
			}
			.info-item{
				text{
					color: $font-gray;
				}
				.price{
					color: $red;
					font-weight: bold;
				}
				.market{
					text-decoration: line-through;
				}
			}

			
		}
		.operate{
			width: 100%;
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-top: 10rpx;
			padding: 20rpx 40rpx 0;
			border-top: 1px solid $uni-border-3;
			box-sizing: border-box;
			.order-status{
				font-size: 26rpx;
			}
			.order-btns{
				display: flex;
				justify-content: space-between;
				align-items: center;
			}
		}
	}

</style>