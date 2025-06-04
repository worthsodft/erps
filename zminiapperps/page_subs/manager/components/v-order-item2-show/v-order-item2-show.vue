<template>
	<view class="order-item">
		<view class="info">
			<view class="title v-mb-10" @click="confirm"><text class="v-bold">订单编号：</text><text class="color-desc">{{item.sn}}</text>
				<view class="take-type">
					<text class="v-tag" v-if="item.take_type == $enum.order.TAKE_TYPE_SELF">自提</text>
					<text class="v-tag" v-else-if="item.take_type == $enum.order.TAKE_TYPE_DELIVER">配送</text>
				</view>
			</view>
			<view class="address v-mb-10">
				<view v-if="item.take_type == $enum.order.TAKE_TYPE_SELF">
					<text class="v-bold">自提水站：</text>
					<view class="v-inline color-desc">{{item.station_title}}，{{item.station_address}}<text>{{'\n'}}</text>
						{{item.station_link_name}}，<text @click="callPhone(item.station_link_phone)" class="color-blue">{{item.station_link_phone}}</text>
					</view>
				</view>
				<view v-if="item.take_type == $enum.order.TAKE_TYPE_DELIVER">
					<text class="v-bold">收货地址：</text>
					<view class="v-inline color-desc">{{item.take_address}}<text>{{'\n'}}</text>
						{{item.take_name}}，<text @click="callPhone(item.take_phone)" class="color-blue">{{item.take_phone}}</text>
					</view>
				</view>
			</view>
			<view class=" v-mb-10">
				<text class="v-bold">支付时间：</text>
				<view class="v-inline color-desc">{{item.pay_at}}</view>
			</view>
			<view class="order-subs v-w100 v-mb-10" @click="confirm">
				<view class="v-bold">商品列表：</view>
				<view class="order-sub" v-for="(sub,index) in item.subs" :key="sub.id">
					<view>{{sub.goods_name}} {{sub.goods_number}} {{sub.goods_unit}}</view>
				</view>
			</view>
			<view class="order-subs v-w100 v-mb-10" @click="confirm">
				<text class="v-bold">订单备注：</text>
				<view class="v-inline color-desc">{{item.remark || '无'}}</view>
			</view>
			<view class="order-subs v-w100 v-mb-10" v-if="item.refund_status != 0" @click="confirm">
				<text class="v-bold">退款状态：</text>
				<view class="v-inline color-red">{{item.refund_status_txt || '无'}}</view>
			</view>
			<view class="order-subs v-w100 v-mb-10 v-nowrap" v-if="item.pick_gid_">
				<text class="v-bold">配货单号：</text>
				<view class="v-inline color-blue" @click.stop="showOrderPickPopup">{{item.pick_gid_}}</view>
			</view>
			<view class="order-subs v-w100 v-mb-10 v-nowrap" v-if="item.pick_by_txt">
				<text class="v-bold">配送员：</text>
				<view class="v-inline">{{item.pick_by_txt}}</view>
				<view class="v-inline color-blue v-ml-20 v-flex-center" v-if="item.pick_by_phone">
					<uni-icons type="phone-filled" color="#8f939c" size="15"></uni-icons>
					<text @click="callPhone(item.pick_by_phone)">{{item.pick_by_phone}}</text>
				</view>
			</view>
			<view class="order-subs v-w100 v-mb-10 v-nowrap" v-if="item.pick_at">
				<text class="v-bold">发起配货：</text>
				<view class="v-inline">{{item.pick_at}}</view>
			</view>
<!-- 			<view class="info-item">订单金额：<text>￥{{item.pay_amount}}</text></view>
			<view class="info-item">下单时间：<text>{{item.create_at}}</text></view> -->
<!-- 			<view class="info-item" v-if="item.pay_at">支付时间：<text>{{item.pay_at}}</text></view>
			<view class="info-item" v-if="item.take_at">核销时间：<text>{{item.take_at}}</text></view>
			<view class="info-item" v-if="item.take_district">配送区域：<text>{{item.take_district}}</text></view> -->
		</view>
		<view class="operate">
			<view class="order-status v-deviler-select" @click="select" v-if="item.take_type == $enum.order.TAKE_TYPE_DELIVER && item.status == $enum.order.STATUS_DELIVERING && item.deliver_status == $enum.order.DELIVER_STATUS_NOT">
				<view v-if="item.checked" class="color-red" style="display: flex;align-items: center;">
					<uni-icons type="checkbox-filled" size="20" color="#e43d33"></uni-icons>选择配货
				</view>
				<view v-else style="display: flex;align-items: center;">
					<uni-icons type="checkbox" size="20"></uni-icons>选择配货
				</view>
			</view>
			<!--if="item.take_type == $enum.order.TAKE_TYPE_SELF"-->
			<view class="order-status" v-else :class="{vW80: item.status == $enum.order.STATUS_REFUND}">
				<text v-if="item.status == $enum.order.STATUS_UNPAY" class="color-red">待支付</text>
				<text v-else-if="item.status == $enum.order.STATUS_DELIVERING && item.deliver_status == $enum.order.DELIVER_STATUS_NOT" class="color-org">待配送</text>
				<text v-else-if="item.status == $enum.order.STATUS_DELIVERING && item.deliver_status == $enum.order.DELIVER_STATUS_ING" class="color-org">配送中</text>
				<text v-else-if="item.status == $enum.order.STATUS_FINISHED" class="color-green">已完成</text>
				<view v-else-if="item.status == $enum.order.STATUS_REFUND" class="color-desc">
					退 款 ({{item.refund_status_txt}})
					<text class="v-ml-10" v-if="item.refund_feedback_msg">审核反馈：{{item.refund_feedback_msg}}</text>
				</view>
			</view>

			<view class="order-btns">
				<view class="btn-sm btn-green" @click.stop="confirm">订单详情</view>
			</view>
		</view>
		

	</view>
</template>
<script setup>
	// 此组件仅用于各种列表显示，没有任何写操作
	import {ref} from "vue";
	
	const $enum = ref(uni.$enum)
	const emit = defineEmits(["select"])
	
	const props = defineProps({
		item: Object,
		index: Number
	})
	
	function showOrderPickPopup(){
		if(!props.item.pick_gid) return uni.$tool.tip("配货单号不能为空")
		// console.log(props.item.pick_gid);
	}
	
	function select(){
		emit("select", props.index)
	}
	
	function callPhone(phone){
		if(!phone) return;
		uni.makePhoneCall({
			phoneNumber: phone
		})
	}
	
	// 订单支付
	function confirm(){
		uni.$tool.navto("/pages/order/detail?sn="+props.item.sn)
	}
</script>

<style lang="scss" scoped>
	@import '@/static/assets/all.scss';
	$br: 20rpx;
	.order-item{
		width: calc(750rpx - 80rpx);
		background-color: $bg-gray;
		display: flex;
		// margin-left: 40rpx;
		margin-bottom: 40rpx;
		flex-shrink: 0;
		font-size: 26rpx;
		box-sizing: border-box;
		flex-direction: column;
		padding: 20rpx 0;
		border-radius: $br;
		box-shadow: 0 0 20rpx 2rpx #ccc;
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
				position: relative;
				.take-type{
					position: absolute;
					top: 0;
					right: 0;
				}
			}
			.order-subs{
				.order-sub{
					width: 100%;
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
				&.v-deviler-select{
					padding: 10rpx 20rpx;
				}
			}
			.order-btns{
				display: flex;
				justify-content: space-between;
				align-items: center;
			}
		}
	}

</style>