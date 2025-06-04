<template>
	<view>
		<uni-popup ref="couponPopup" background-color="#fff" @change="couponPopupChange">
			<view class="coupon-popup-main">
				<view class="coupon-popup-main-title">选择优惠券</view>
				<view class="coupon-popup-main-list">
					<v-coupon-item v-for="(item,index) in list" :key="item.id" :item="item" @select="select" type="select" mb fullWidth></v-coupon-item>
					<button @click="reset" style="background-color: #f3a73f; color: #fff; height: 60rpx;line-height: 60rpx; font-size: 30rpx;">不使用</button>
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script setup>
	import {ref} from 'vue'
	
	const props = defineProps({
		list: Array
	})
	
	const couponPopup = ref()
	
	const emit = defineEmits(['select','reset'])
	
	function open(){
		couponPopup.value.open("bottom")
	}
	function close(){
		couponPopup.value.close()
	}
	
	function select(e){
		emit("select", e)
	}
	// 不使用优惠券
	function reset(e){
		emit("reset")
	}
	function couponPopupChange(e){
		// console.log(e);
	}
	
	// 暴露函数，供外部调用
	defineExpose({
		open,close
	})
	
</script>

<style lang="scss" scoped>
	@import '@/static/assets/all.scss';
	.coupon-popup-main{
		width: 100%;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: flex-start;
		.coupon-popup-main-title{
			position: fixed;
			top: 0;
			width: 100%;
			height: 80rpx;
			line-height: 80rpx;
			text-align: center;
			background-color: $bg-gray;
			box-sizing: border-box;
		}
		.coupon-popup-main-list{
			width: 80%;
			height: 680rpx;
			margin: 100rpx 80rpx 20rpx;
			flex-shrink: 0;
			overflow-y: scroll;
		}
	}
</style>