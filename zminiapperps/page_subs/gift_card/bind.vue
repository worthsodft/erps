<template>
	<view class="container-v main">
		<view class="banner">
			<view class="card-info" v-if="card">
				<view class="card-info-item">卡号：<text>{{card.sn}}</text></view>
				<view class="card-info-item">绑定有效期：<text>{{card.bind_expire_at}}</text></view>
				<view class="card-info-item">使用有效期：<text>{{card.useful_days}}天（自绑定之日起）</text></view>
				<template v-if="card.use_type == 0">
					<view class="card-info-item">剩余金额：<text>￥{{card.has}} 元</text></view>
				</template>
				<template v-else>
					<view class="card-info-item">计次商品：<text>{{card.take_goods_sn_txt}}</text></view>
					<view class="card-info-item">剩余次数：<text>{{card.has}} 次</text></view>
				</template>
			</view>
			<swiper class="card-swiper" v-else indicator-dots autoplay circular indicator-active-color="#25639C">
				<swiper-item class="card-swiper-item" v-for="item,index in banners" :key="index">
					<image :src="item" mode="scaleToFill"></image>
				</swiper-item>
			</swiper>
		</view>
		<view class="container-h v-mb-20">
			<v-line-title title="卡号："></v-line-title>
		</view>
		<view class="container-h v-mb-20">
			<uni-easyinput v-model="formData.sn" suffixIcon="scan" trim @iconClick="getSnFromScan" placeholder="请扫描或输入卡号"></uni-easyinput>
		</view>
		<view class="container-h v-mb-20">
			<v-line-title title="密码："></v-line-title>
		</view>
		<view class="container-h v-mb-20">
			<uni-easyinput v-model="formData.code" suffixIcon="scan" trim @iconClick="getCodeFromScan" placeholder="请输入密码"></uni-easyinput>
		</view>
		<view class="container-h v-mb-20">
			<v-line-title title="验证码："></v-line-title>
		</view>
		<view class="container-h v-mb-20">
			<uni-easyinput v-model="formData.vcode" trim placeholder="请输入验证码"></uni-easyinput>
			<view class="captcha" @click="getCaptcha" style="margin-left: 10rpx;width: 180rpx; height: 80rpx;">
				<image :src="captcha.image||''" mode="widthFix" style="width: 100%;height:auto;"></image>
			</view>
		</view>
		<view class="v-mt-50 v-text-right">
			<button v-if="card" @click="bind" type="primary" style="background-color: #5aa1d8;color:#fff;">确认绑定</button>
			<button v-else @click="search" type="primary" style="background-color: #f5f5f5;color:#282828;">查 询</button>
			<view class="color-green v-mt-10">
				<text class="v-mr-40" @click="logs">卡使用记录</text>
				<text @click="list">我的实物卡</text>
			</view>
		</view>
		<block v-if="remarkList.length > 0">
			<v-line-title title="使用说明"></v-line-title>
			<view class="v-mt-20">
				<view class="v-ml-20 color-desc" style="margin-bottom: 10rpx;" v-for="(item,idx) in remarkList" :key="idx">
					{{idx+1}}. {{item}}
				</view>
			</view>
		</block>
	</view>
</template>

<script setup>
	import {ref} from 'vue'
	
	const captcha = ref({})
	const banner = ref()
	const banners = ref([])
	const card = ref()
	const formData = ref({sn:'',code:'',vcode:'',vuniqid:''})
	const remarkList = ref([])
	const captchaType = "GiftCardBindCaptcha";
	
	getCaptcha()
	getInitDataGiftCardBind()
	
	function getCaptcha(){
		uni.$http.post("v1/captcha", {type: captchaType}).then(res=>{
			captcha.value = res.data
			formData.value.vuniqid = res.data.uniqid
		})
	}
	
	// 获取页面初始化数据
	function getInitDataGiftCardBind(){
		uni.$http.get("v1/getInitDataGiftCardBind").then(res=>{
			banners.value = res.data.banners
			remarkList.value = res.data.remarks
		})
	}
	
	// 表单验证
	function valid(){
		if(!formData.value.sn) return uni.$tool.tip("请扫描或输入卡号")
		if(!formData.value.code) return uni.$tool.tip("请输入密码")
		if(!formData.value.vcode) return uni.$tool.tip("请输入验证码")
		if(!formData.value.vuniqid) return uni.$tool.tip("请输入验证码标识")
		
		return true;
	}
	
	// 查询
	function search(){
		if(!valid()) return;
		doSearch()
	}
	
	// 查询操作
	let doSearch = uni.$tool.debounce(()=>{
		formData.value.type = captchaType
		uni.$http.post("v1/searchGiftCardForMiniBind", formData.value).then(res=>{
			getCaptcha()
			formData.value.vcode = ''
			if(res.code == 1){
				card.value = res.data.card
				uni.$tool.alert("卡有效，请核对信息无误后，点击“确认绑定”按钮，进行绑卡操作")
			} 
		}).catch(err=>{
			getCaptcha()
		})
	})
	
	// 绑定
	function bind(){
		if(!valid()) return;
		doBind()
	}
	// 绑定操作
	let doBind = uni.$tool.debounce(()=>{
		formData.value.type = captchaType
		uni.$http.post("v1/bindGiftCardForMiniBind", formData.value).then(res=>{
			getCaptcha()
			if(res.code == 1) uni.$tool.tip(res.info||"操作成功")
			resetFormData()
		}).catch(err=>{
			getCaptcha()
		})
	})
	
	function resetFormData(){
		formData.value.sn = ""
		formData.value.code = ""
		formData.value.vcode = ""
		card.value = ""
	}
	
	
	// 扫描二维码，得到订单号
	function getSnFromScan(){
		// #ifdef MP-WEIXIN
		uni.scanCode({
			success({result}) {
				if(result) formData.value.sn = result;
				else console.log("扫码失败")
			},
			fail(err) {
				console.log(err);
			}
		})
		// #endif
		// #ifdef H5
		formData.value.sn = "549120919923068928"
		// #endif
	}
	// 扫描二维码，得到密码
	function getCodeFromScan(){
		// #ifdef MP-WEIXIN
		uni.scanCode({
			success({result}) {
				if(result) formData.value.code = result;
				else console.log("扫码失败")
			},
			fail(err) {
				console.log(err);
			}
		})
		// #endif
		// #ifdef H5
		formData.value.code = "8XMUWXDG53PKTG2Q"
		// #endif
	}

	// 我的实物卡
	function list(){
		uni.$tool.navto("/page_subs/gift_card/list")
	}
	// 卡使用记录
	function logs(){
		uni.$tool.navto("/page_subs/gift_card/logs")
	}
	
	
	
	
</script>

<style lang="scss" scoped>
	$width: 35%;
	.main{
		padding: 40rpx 60rpx;
		.banner{
			width: 100%;
			height: 400rpx;
			text-align: center;
			margin-bottom: 20rpx;
			.card-swiper,.card-info{
				box-shadow: 0 0 20rpx #999;
				border-radius: 10rpx;
				background-color: #f5f5f5;
				height: 100%;
				width: 100%;
			}
			.card-swiper{
				.card-swiper-item{
					image{
						border-radius: 10rpx;
						width: 100%;
						height: 100%;
					}
				}
			}

			.card-info{
				display: flex;
				flex-direction: column;
				align-items: start;
				width:100%;
				padding: 40rpx 60rpx;
				box-sizing: border-box;
				color: #fff;
				background: linear-gradient(to right top, $green2,  $green);
				.card-info-item{
					margin-bottom: 10rpx;
				}
			}
		}
		.way-list{
			display: flex;
			// justify-content: space-around;
			width: 100%;
			flex-wrap: wrap;
			.way-item{
				width: $width;
				border: 1px solid $green2;
				border-radius: 10rpx;
				display: flex;
				flex-direction: column;
				justify-content: space-around;
				align-items: center;
				margin-bottom: 40rpx;
				margin-left: calc((100% - $width * 2) / 3);
				padding: 20rpx 0;
				.give{
					color: $org;
				}
				&.curr{
					background-color: $green;
					color: #fff;
				}
			}
		}
	}
</style>
