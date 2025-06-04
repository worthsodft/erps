<template>
	<view class="container-v demo">
		<view class="demo-item"><button @click="noAuthApi">无权限接口</button></view>
		<view class="demo-item"><button @click="authApi">有权限接口</button></view>
		<view class="demo-item"><button @click="logout">退出登录</button></view>
		<view class="demo-item"><button @click="temp">temp</button></view>
		<view class="demo-item"><button @click="openBusinessView">收货组件</button></view>
	</view>
</template>

<script setup>
	
	const openBusinessView = ()=>{
		uni.openBusinessView({
			businessType: "weappOrderConfirm",
			extraData: {
				transaction_id: "4200002420202408234193346519"
			}
		});
	}
	
	const temp = ()=>{
		uni.$http.get("v1/hello").then(res=>{
			console.log(res);
		})
	}
	
	const noAuthApi = uni.$tool.throttle(async ()=>{
		const res = await uni.$http.get("v1/getInitDataIndex")
		if(res.code != 1) return uni.$tool.tip(res.info|| "系统错误")
		console.log(res);
	})
	const authApi = uni.$tool.debounce(()=>{
		uni.$login.judgeLogin(async ()=>{
			const res = await uni.$http.get("v1/getUserInfo")
			if(res.code != 1) return uni.$tool.tip(res.info|| "系统错误")
			console.log(res);
		})
	})
	const logout = uni.$tool.debounce(()=>{
		uni.$login.logout()
		uni.$tool.tip("退出登录成功")
	})
</script>

<style lang="scss" scoped>
	.demo{
		width: 100%;
		justify-content: center;
		align-items: center;
		.demo-item{
			width: 80%;
			margin-bottom: 20rpx;
		}
	}
</style>