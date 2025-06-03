<template>
	<view class="order-confirm-bar">
		<view class="buy-info">
			<view >实付款：<text class="color-org v-bold v-font-s40">￥{{money}}</text></view>
		</view>
		<view class="btns">
			<view class="btn-sm btn-green" v-if="action == 'pay'" @click="payOrder">立即支付</view>
			<view class="btn-sm btn-green" v-else-if="action == 'create'" @click="createOrder">确认提交</view>
			<view class="btn-sm btn-green" v-else-if="action == 'navto'" @click="navto">返 回</view>
			<view class="btn-sm btn-green" v-else-if="action == 'deliver'" @click="deliver">确认送达</view>
			<!-- <view class="btn-item btn-buy" v-if="action == 'pay'" @click="payOrder">立即支付</view> -->
			<!-- <view class="btn-item btn-buy" v-else-if="action == 'create'" @click="createOrder">立即下单</view> -->
			<!-- <view class="btn-item btn-buy" v-else-if="action == 'navto'" @click="navto">返 回</view> -->
		</view>
	</view>
</template>

<script setup>
	import {ref} from "vue";
	
	const props = defineProps({
		action: String,
		money: [String,Number],
		number: [String,Number],
		sn: String,
	})
	
	const emit = defineEmits(["createOrder", "payOrder", "navto", "deliver"])
	
	function createOrder(){
		emit("createOrder")
	}
	function payOrder(){
		emit("payOrder")
	}
	function navto(){
		emit("navto")
	}
	function deliver(){
		emit("deliver")
	}
	
	
</script>

<style lang="scss" scoped>
	@import '@/static/assets/all.scss';
		.order-confirm-bar{
			position: fixed;
			bottom: 0;
			left: 0;
			width: 100%;
			z-index: 99;
			background-color: #fff;
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 10rpx 30rpx;
			box-sizing: border-box;
			.buy-info{
				// background-color: $bg-gray;
				display: flex;
				align-items: center;
				justify-content: space-between;
				height: 80rpx;
				.number{
					right: 20rpx;
					bottom: 20rpx;
				}
			}
			.btns{
				// display: flex;
				// justify-content: space-between;
				// color: #fff;
				// .btn-item{
				// 	width: 100%;
				// 	height: 80rpx;
				// 	display: flex;
				// 	align-items: center;
				// 	justify-content: center;
				// 	&.btn-buy{
				// 		background-color: $uni-primary;
				// 		&:active{
				// 			background-color: $uni-primary-active;
				// 		}
				// 	}
				// }
			}
		}
</style>