<template>
	<view class="container-v main">
		<view class="container-h v-mb-20">
			<v-line-title title="当前余额："></v-line-title>
			<text class="color-desc v-ml-10">￥{{money}}</text>
		</view>
<!-- 		<view class="container-h v-mb-20">
			<v-line-title title="余额有效期："></v-line-title>
			<text class="color-desc v-ml-10">{{expire_at}}</text>
		</view> -->
		<v-line-title title="充值金额"></v-line-title>
		<view class="way-list v-mt-20">
			<view class="way-item" @click="current=index" :class="{curr:current == index}" v-for="(item,index) in list" :key="item.id">
				<view class="">充 {{item.money}} 元</view>
				<view class="give">赠送 {{item.give_money}} 元</view>
			</view>
		</view>
		<block v-if="remarkList.length > 0">
			<v-line-title title="充值说明"></v-line-title>
			<view class="v-mt-20">
				<view class="v-ml-20 color-desc" v-for="(item,idx) in remarkList" :key="idx">
					{{idx+1}}. {{item}}
				</view>
			</view>
		</block>
		<view class="v-mt-50 v-text-right">
			<button @click="submit" type="primary" style="background-color: #5aa1d8;color:#fff;">确定充值</button>
			<view class="color-green v-mt-10" @click="show">查看余额记录</view>
		</view>
	</view>
</template>

<script setup>
	import {onLoad} from '@dcloudio/uni-app'
	import {ref} from 'vue'
	
	const list = ref([])
	const remarkList = ref([])
	const money = ref(0)
	const current = ref(0)
	
	let expire_at = ""
	
	getList()
	getUserInfo()
	
	
	function getList(){
		uni.$http.get("v1/rechargeWayList").then(res=>{
			list.value = res.data.list
			remarkList.value = res.data.remarkList
		})
	}
	function getUserInfo(){
		uni.$api.getUserInfo(res=>{
			money.value = res.data.userInfo.money
			expire_at = res.data.userInfo.money_expire_at
			uni.$login.updateTokenCache(res.data)
		})
	}
	function show(){
		uni.$tool.navto("/pages/pay/money-log")
	}
	function submit(){
		uni.$login.judgePhone(()=>{
			doSubmit()
		})
	}
	async function doSubmit(){
		let item = list.value[current.value]
		if(!item.gid) return uni.$tool.tip("充值项目不存在")
		
		const res = await uni.$api.getPayInfo("recharge", item.gid)
		if(res.code != 1) return uni.$tool.tip(res.info||"系统错误")
		
		uni.$tool.payment(res.data.payInfo, ()=>{
			getUserInfo()
		})
	}
	
	
</script>

<style lang="scss" scoped>
	$width: 35%;
	.main{
		padding: 40rpx 60rpx;
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
