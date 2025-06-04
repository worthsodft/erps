<template>
	<view class="container-v list">
		<v-goods-item2 v-for="(item,index) in list" :key="item.id" :item="item"></v-goods-item2>
	</view>
	<uni-load-more :status="loadStatus" @click="getList"></uni-load-more>
</template>

<script setup>
	import {onReachBottom,onShow,onPullDownRefresh,onShareAppMessage} from '@dcloudio/uni-app'
	import {ref} from 'vue'
	
	const list = ref([])
	const loadStatus = ref("more")
	let page = 1
	let total = 0
	let title = "商品列表";
	
	onShow(()=>{
		reset()
		getList()
		uni.$api.getCartTotal()
	})
	async function getList(){
		if(loadStatus.value != "more") return
		loadStatus.value = "loading"
		const res = await uni.$http.get("v1/getGoodsPageData?page="+page)
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
	function reset(){
		list.value = []
		loadStatus.value = "more"
		page = 1
		total = 0
	}
	onReachBottom(()=>{
		getList()
	})
	onPullDownRefresh(()=>{
		reset()
		getList()
		setTimeout(()=>{
			uni.stopPullDownRefresh()
		}, 200)
	})
	onShareAppMessage(()=>{
		return getApp().globalData.shareData
	}) 
</script>
<style lang="scss" scoped>
	.list{
		padding: 0 20rpx;
	}
</style>
