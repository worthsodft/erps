<template>
	<view class="container-v pay-success">
		<uni-icons type="checkbox-filled" size="160" color="#5aa1d8"></uni-icons>
		<text class="v-font-s50 v-mt-20">支付成功</text>
		<view class="v-mt-20">
			<view>支付编号：<text class="color-desc">{{sn}}</text></view>
			<view>支付金额：<text class="color-desc">￥{{money}}</text></view>
		</view>
		<view class="v-mt-100 v-w100 v-text-right">
			<button @click="back" type="primary" style="background-color: #5aa1d8;color:#fff;">返回首页</button>
			<view class="color-green v-mt-10" @click="show">{{type=='order'?'查看订单列表':'查看余额记录'}}</view>
		</view>
	</view>
</template>

<script setup>
	import {onLoad} from '@dcloudio/uni-app'
	import {ref} from 'vue'
	
	const type = ref(""),money = ref("0.00"),sn = ref("")
	onLoad((opts)=>{
		type.value = opts.type || "order"
		sn.value = opts.sn || ''
		uni.$login.judgeLogin(()=>{
			getInitDataPaySuccess()
		})
	})
	async function getInitDataPaySuccess(){
		const res = await uni.$http.get(`v1/getInitDataPaySuccess/${type.value}/${sn.value}`)
		money.value = res.data.money
	}
	
	function back(){
		uni.$tool.index()
	}
	
	function show(){
		let url = "/pages/order/list?status=1"
		if(type.value == "recharge") url = "/pages/pay/money-log"
		uni.$tool.navto(url)
	}

	
	
</script>

<style lang="scss" scoped>
	.pay-success{
		padding: 0 80rpx;
		justify-content: center;
		align-items: center;
	}
</style>
