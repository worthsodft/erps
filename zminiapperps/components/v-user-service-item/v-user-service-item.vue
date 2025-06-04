<template>
	<view class="container-h service-item">
		<button  v-if="item.type == 'set'" class="v-reset-button open-button" open-type="openSetting" >
			<view class="left">
				<uni-icons :type="item.icon" color="#6a6a6a" size="20"></uni-icons>
				{{item.title||""}}
			</view>
			<view class="right">
				<uni-icons type="right" color="#6a6a6a" size="20"></uni-icons>
			</view>
		</button>
		<view class="menu-item" v-else @click="doService">
			<view class="left">
				<uni-icons :type="item.icon" color="#6a6a6a" size="20"></uni-icons>
				<view class="title">{{item.title||""}}</view>
				<text class="desc" v-if="item.desc">{{item.desc}}</text>
			</view>
			<view class="right">
				<uni-icons type="right" color="#6a6a6a" size="20"></uni-icons>
			</view>
		</view>
	</view>
</template>

<script setup>
	import {ref} from 'vue'
	const props = defineProps({
		item: Object,
		index: [String, Number]
	})
	const emit = defineEmits(['doService'])
	function doService(){
		emit("doService", props.item)
	}
</script>

<style lang="scss" scoped>
	@import '@/static/assets/all.scss';
	.service-item{
		width: 100%;
		height: 120rpx;
		border-bottom: 1px solid $uni-border-1;
		background-color: #fff;
		display: flex;
		justify-content: space-between;
		align-items: center;
		box-sizing: border-box;
		.left{
			display: flex;
			align-items: center;
			.title{
				margin-left: 10rpx;
				white-space: nowrap;
			}
			.desc{
				color: #999;
				font-size: 24rpx;
				width: 70%;
				margin-left: 20rpx;
			}
		}
		// 普通菜单， 微信小程序开放功能
		.menu-item, .open-button{
			width: 100%;
			display: flex;
			justify-content: space-between;
			padding: 40rpx 20rpx;
		}
	}
</style>