<template>
	<view class="container-v main">
		<view class="user-info" @click="form">
			<image class="avatar" :src="userInfo.avatar||defaultAvatar" mode="aspectFill"></image>
			<view class="v-ml-20">
				<view class="uni-h6 v-font-s30 v-ellip" style="width:400rpx;line-height: 40rpx;">{{userInfo.nickname||'点击登录'}}</view>
				<view class="uni-h6 v-font-s20" v-if="userInfo.realname">姓名：{{userInfo.realname}}</view>
					<!-- <text style="font-size: 20rpx;color: #ccc;">({{userInfo.id}})</text> -->
				
				<!-- <view class="v-font-s20" v-if="userInfo.money_expire_at">余额有效期：{{userInfo.money_expire_at}}</view> -->
				<view class="v-font-s20" v-if="isTest">测试版</view>
			</view>
		</view>

		<view class="set">
			<!-- #ifdef MP-WEIXIN -->
			<button class="v-reset-button" open-type="openSetting">
				<uni-icons type="gear" size="30" color="#fff" ></uni-icons>
			</button>
			<!-- #endif -->
			<!-- #ifdef H5 -->
			<view @click="set">
				<uni-icons type="gear" size="30" color="#fff" ></uni-icons>
			</view>
			<!-- #endif -->
		</view>
	</view>
</template>

<script setup>
	import {ref} from 'vue'
	import {onShow} from '@dcloudio/uni-app'
	import config from '../../extend/config.js'
	const defaultAvatar = ref(config.defaultAvatar)
	const userInfo = ref({})
	const isTest = ref() // 体验版
	isTest.value = config.domain.indexOf("erp.") == -1
	
	onShow(()=>{
		getUserInfo()
	})
	function getUserInfo(){
		uni.$api.getUserInfo(res=>{
			userInfo.value = res.data.userInfo
		})
	}
	function reload(){
		getUserInfo()
	}
	function form(){
		uni.$tool.navto("/pages/my/form")
	}
	function set(){
		uni.$tool.tip("H5不支持跳转设置")
	}
	
	defineExpose({
		reload
	}) 
</script>

<style lang="scss" scoped>
	@import '@/static/assets/all.scss';
	.main{
		width: 100%;
		height: 350rpx;
		background-color: $green;
		background: linear-gradient(to right top, $green2,  $green);
		color: #fff;
		position: relative;
		.set{
			position: absolute;
			top: 40rpx;
			right: 40rpx;
			padding: 20rpx;
		}
		.user-info{
			display: flex;
			align-items: center;
			margin-top: 40rpx;
			margin-left: 40rpx;
			.avatar{
				width: 120rpx;
				height: 120rpx;
				border-radius: 50%;
			}
		}
	}
</style>