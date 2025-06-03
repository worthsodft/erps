<template>
	<view class="container-v main">
		<v-user-service-item class="v-w100" v-for="(item,index) in list" :key="item.id" :index="index" :item="item" @doService="doService"></v-user-service-item>
	</view>
</template>

<script setup>
	import {ref} from 'vue'
	
	const servicePhone = ref(getApp().globalData.servicePhone);
	const list = ref([]);
	let currItem = null;
	
	getServiceMenu()
	
	function getServiceMenu(){
		uni.$http.get("v1/getServiceMenu").then(res=>{
			list.value = res.data.list
		})
	}
	
	function doService(item){
		currItem = item;
		let type = currItem.type;
		if(type == 'path') path();
		else if(type == 'phonecall') phonecall();
		else if(type == 'set') set();
		else if(type == 'logout') logout();
		else if(type == 'order_finish') orderFinish();
		else if(type == 'order_deliver') orderDeliver();
		else if(type == 'update') uni.$tool.checkUpdateApp(false);
		else path();
	}
	
	function orderFinish(){
		uni.$login.judgePhone(async()=>{
			const res = await uni.$http.post("v1/isAuthFinishOrder");
			if (res.code != 1) return uni.$tool.tip(res.info||"系统错误")
			
			let url = "/page_subs/manager/order_finish/order_finish_welcome"
			uni.$tool.navto(url)
		})
	}

	async function orderDeliver(){
		uni.$login.judgePhone(async()=>{
			const res = await uni.$http.post("v1/isAuthDeliverOrder");
			if (res.code != 1) return uni.$tool.tip(res.info||"系统错误")
			
			let url = "/page_subs/manager/order_deliver/order_deliver_list"
			uni.$tool.navto(url);
		})
	}
	
	function path(){
		if(!currItem.url) return uni.$tool.tip("暂无跳转链接")
		uni.$tool.navto(currItem.url, err=>{
			uni.$tool.tip("暂缓开放")
		})
	}
	function phonecall(){
		// #ifdef MP-WEIXIN
		uni.makePhoneCall({
			phoneNumber: servicePhone.value,
			fail(err) {
				console.log(err);
			}
		})
		// #endif
		// #ifdef H5
		uni.$tool.tip("系统拨号")
		// #endif
	}
	function set(){
		// #ifdef MP-WEIXIN
		
		// #endif
		// #ifdef H5
		uni.$tool.tip("H5不支持跳转设置")
		// #endif
	}
	function logout(){
		uni.$tool.confirm("确定要清除用户缓存吗？", ()=>{
			uni.$login.logout()
			uni.$tool.tip("已清除用户缓存", true, ()=>{
				uni.$tool.index()
			})
		})
	}
	
	
	
</script>

<style lang="scss" scoped>
	@import '@/static/assets/all.scss';
	.main{
		width: 100%;
		align-items: center;
		justify-content: center;
		color: $uni-base-color;
		font-size: 30rpx;
		margin-top: 40rpx;
	}
</style>