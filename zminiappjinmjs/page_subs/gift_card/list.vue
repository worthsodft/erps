<template>
	<view class="container-v gift_card_list">
		<v-gift-card-item v-for="(item,index) in list" :key="item.id" :item="item"></v-gift-card-item>
	</view>
	<uni-load-more :status="loadStatus" @click="getList"></uni-load-more>
</template>

<script setup>
	import {onReachBottom} from '@dcloudio/uni-app'
	import {ref} from 'vue'
	import vGiftCardItem from './components/v-gift-card-item/v-gift-card-item.vue'
	
	const list = ref([])
	const loadStatus = ref("more")
	let page = 1
	let total = 0
	getList();
	async function getList(){
		if(loadStatus.value != "more") return
		loadStatus.value = "loading"
		const res = await uni.$http.get("v1/getMyGiftCardPageData?page="+page)
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
	.gift_card_list{
		padding: 0 20rpx;
	}
</style>