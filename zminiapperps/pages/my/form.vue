<template>
	<view class="container-v main">
		<form class="container-v form">
			<!-- #ifdef MP-WEIXIN -->
			<button class="v-reset-button choose" open-type="chooseAvatar" @chooseavatar="chooseavatar">
				<image class="avatar" :src="userInfo.avatar||defaultAvatar" mode="aspectFill"></image>
				<view class="avatar-tip" v-if="!userInfo.avatar"><text>点击获取</text></view>
			</button>
			<view class="form-item">
				<uni-easyinput type="nickname" v-model="userInfo.nickname" @blur="choosenickname" placeholder="请输入昵称"></uni-easyinput>
			</view>
			<view class="form-item">
				<uni-easyinput v-model="userInfo.realname" placeholder="请输入真实姓名"></uni-easyinput>
			</view>
			<view class="form-item">
				<button class="v-reset-button choose" open-type="getPhoneNumber" @getphonenumber="getphonenumber">
					<uni-easyinput disabled :value="userInfo.phone" placeholder="点击获取电话号码"></uni-easyinput>
				</button>
			</view>
			<!-- #endif -->
			
			
			<!-- #ifdef H5 -->
			<button class="v-reset-button choose" @click="chooseavatar">
				<image class="avatar" :src="userInfo.avatar||defaultAvatar" mode="aspectFill"></image>
				<view class="avatar-tip" v-if="!userInfo.avatar"><text>点击获取</text></view>
			</button>
			<view class="form-item">
				<uni-easyinput v-model="userInfo.nickname" placeholder="请输入昵称"></uni-easyinput>
			</view>
			<view class="form-item">
				<uni-easyinput v-model="userInfo.realname" placeholder="请输入真实姓名"></uni-easyinput>
			</view>
			<view class="form-item">
				<uni-easyinput v-model="userInfo.phone" placeholder="请输入电话号码"></uni-easyinput>
			</view>
			<!-- #endif -->
		</form>
		
		<v-button-w80 :style="{marginTop:'80rpx'}" @action="submit" title="保存提交"></v-button-w80>
	</view>
</template>

<script setup>
	import {onShow} from '@dcloudio/uni-app'
	import {ref} from 'vue'
	import config from '@/extend/config.js'
	const defaultAvatar = ref(config.defaultAvatar)
	const userInfo = ref({
		avatar: '',
		nickname: '',
		realname: '',
		phone: ''
	})
	
	uni.$api.getUserInfo(res=>{
		userInfo.value = res.data.userInfo
	})
	
	function getphonenumber({detail}){
		if(!detail.code) return
		uni.$http.post("v1/getPhoneNumberByCode", {code:detail.code}).then(res=>{
			userInfo.value.phone = res.data.phone
		})
	}
	
	function chooseavatar({detail}){
		// #ifdef MP-WEIXIN
		let tmp = detail.avatarUrl
		userInfo.value.avatar = tmp
		// #endif
		// #ifdef H5
		uni.chooseImage({
			success(res) {
				userInfo.value.avatar = res.tempFilePaths[0]
			}
		})
		// #endif
	}
	function choosenickname({detail}){
		if(!detail.value) return
		userInfo.value.nickname = detail.value
	}
	
	function submit(){
		if(!valid()) return
		if(!uni.$tool.isRemoteUrl(userInfo.value.avatar)){
			upload(()=>{
				updateUserInfo()
			})
		}else{
			updateUserInfo()
		}
	}
	const updateUserInfo = uni.$tool.throttle(()=>{
		let params = {
			avatar: userInfo.value.avatar,
			nickname: userInfo.value.nickname,
			realname: userInfo.value.realname,
			phone: userInfo.value.phone
		}
		uni.$http.post("v1/updateUserInfo", params).then(res=>{
			if(res.code == 1){
				uni.$login.updateTokenCache(res.data)
				uni.$tool.tip(res.info||"操作成功", true, ()=>{
					uni.$tool.back()
				})
			}else{
				uni.$tool.tip(res.info||"操作失败")
			}
		})
	})
	async function upload(cb){
		let params = {
			filePath: userInfo.value.avatar,
			name: 'file',
		}
		const res = await uni.$http.upload("v1/upload", params)
		userInfo.value.avatar = res.data.url
		cb && cb()
	}
	function valid(){
		if(!userInfo.value.avatar) return uni.$tool.tip("请选择头像")
		if(!userInfo.value.nickname) return uni.$tool.tip("请输入昵称")
		if(!userInfo.value.phone) return uni.$tool.tip("请选择电话号码")
		return true
	}
	
</script>

<style lang="scss" scoped>
	.main{
		justify-content: center;
		// align-items: center;
		width: 100%;
		.form{
			margin-top: 50rpx;
			margin-left: 10%;
			width: 80%;
			.choose{
				width: 100%;
				text-align: center;
				display: block;
				position: relative;
				.avatar{
					width: 100rpx;
					height: 100rpx;
					border-radius: 50%;
				}
				.avatar-tip{
					color: #666;
					position: absolute;
					width: 100%;
					left: 0;
					top: 38rpx;
					text-align: center;
					font-size: 22rpx;
				}
			}
			.form-item{
				margin-top: 40rpx;
			}
		}
		.test-login{
			color: #6abfff;
			width: 80%;
			margin-left: 10%;
			margin-top: 10rpx;
			text-align: right;
			
		}
	}
</style>
