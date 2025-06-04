<template>
	<view class="container-h main">
		<view class="item" v-for="(item,index) in tabs" :key="index" @click="orderList(item.status)">
			<uni-icons :type="item.icon" size="30" color="#6abfff"></uni-icons>
			<view class="title">{{item.title}}</view>
			<view class="count" v-if="item.count>0">{{item.count}}</view>
		</view>
	</view>
</template>

<script setup>
	import {onShow} from '@dcloudio/uni-app'
	import {ref} from 'vue'
	
	const tabs = ref()
	
	onShow(()=>{
		uni.$login.judgeLogin().then(()=>{
			getUserOrderTabsData()
		})
	})

	function getUserOrderTabsData(){
		uni.$http.get("v1/getUserOrderTabsData").then(res=>{
			tabs.value = res.data.tabsData
		})
	}
	function orderList(status){
		uni.$tool.navto("/pages/order/list?status="+status)
	}
	
</script>

<style lang="scss" scoped>
	@import '@/static/assets/all.scss';
	.main{
		width: 100%;
		align-items: center;
		justify-content: center;
		color: $uni-base-color;
		background-color: #ffffff;
		margin-top: 130rpx;
		padding: 40rpx 0;
		flex-shrink: 0;
		.item{
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			margin-right: 30rpx;
			width: 100rpx;
			position: relative;
			&:last-child{
				margin-right: 0;
			}
			.title{
				font-size: 30rpx;
			}
			.count{
				position: absolute;
				right: 5rpx;
				top: -10rpx;
				background-color: $org;
				width: 35rpx;
				height: 35rpx;
				border-radius: 20rpx;
				z-index: 9;
				color: #fff;
				font-size: 20rpx;
				display: flex;
				align-items: center;
				justify-content: center;
			}
		}
	}
</style>