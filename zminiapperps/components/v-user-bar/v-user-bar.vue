<template>
	<view class="container-h bar">
		<view class="item" @click="coupon">
			<view class="number">{{couponCount}}</view>
			<view class="title">优惠券</view>
		</view>
		<view class="item" @click="moneyLog">
			<view class="number">{{balance}}</view>
			<view class="title">余 额</view>
		</view>
	</view>
</template>

<script setup>
	import {onShow} from '@dcloudio/uni-app'
	import {ref} from 'vue'
	const couponCount = ref(0)
	const balance = ref(0)
	const expire_at = ref()
	
	onShow(()=>{
		uni.$login.judgeLogin().then(()=>{
			getUserBarData()
		})
	})
	
	async function getUserBarData(){
		const res = await uni.$http.get("v1/getUserBarData");
		if(res.code != 1) return uni.$tool.tip(res.info||'系统错误')
		couponCount.value = res.data.couponCount
		balance.value = res.data.balance
		expire_at.value = res.data.expire_at
	}
	function reload(){
		getUserBarData()
	}
	function coupon(){
		uni.$tool.navto("/pages/my/coupon")
	}
	function moneyLog(){
		uni.$tool.navto("/pages/pay/money-log")
	}
	defineExpose({
		reload
	})
</script>

<style lang="scss" scoped>
	@import '@/static/assets/all.scss';
	$ml: calc(750rpx - 80%);
	.bar{
		width: 80%;
		margin-left: calc($ml / 2);
		align-items: center;
		justify-content: space-around;
		background-color: #ffffff;
		border-radius: 20rpx;
		padding: 30rpx 0;
		top: calc(350rpx - 100rpx);
		position: absolute;
		z-index: 99;
		.item{
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			width: 100%;
			.number{
				color: $uni-main-color;
				font-size: 60rpx;
			}
			.title{
				color: $uni-base-color;
			}
		}
	}
</style>