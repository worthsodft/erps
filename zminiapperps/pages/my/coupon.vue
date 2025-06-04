<template>
	<view class="container-v coupon">
		<v-coupon-item v-for="(item,index) in list" :key="item.id" :item="item" type="my" mb fullWidth></v-coupon-item>
	</view>
	<uni-load-more :status="loadStatus" @click="getList"></uni-load-more>
</template>

<script setup>
	import {onReachBottom} from '@dcloudio/uni-app'
	import {ref} from 'vue'
	
	const list = ref([])
	const loadStatus = ref("more")
	let page = 1
	let total = 0
	getList()
	
	async function getList(){
		if(loadStatus.value != "more") return
		loadStatus.value = "loading"
		const res = await uni.$http.get("v1/getMyCouponPageData?page="+page)
		if(res.code != 1) return uni.$tool.tip(res.info||"系统错误")
		let pageData = res.data.pageData
		if(pageData.data.length == 0){
			loadStatus.value = "nomore"
			return
		}
		list.value = [...(list.value), ...pageData.data]
		total = pageData.total
		if(list.value.length >= total){
			loadStatus.value = "nomore"
		}else{
			loadStatus.value = "more"
			page++
		}
	}
	
	onReachBottom(()=>{
		getList()
	})
	
	
</script>

<style lang="scss" scoped>
	.coupon{
		padding: 0 20rpx;
		.v-coupon-item{
			margin-bottom: 20rpx;
		}
	}
</style>
