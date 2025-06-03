<template>
	<view class="container-v order-finish-list">
		<view class="v-w100 v-mb-10">
			<uni-section title="核销时间：" type="line"></uni-section>
			<view style="width: 90%;margin-left: 5%;">
				<uni-datetime-picker v-model="takeAtRange" type="daterange" @change="takeAtRangeChange" rangeSeparator="至" />
			</view>
			<uni-section :title="'总订单数：'+ total" type="line"></uni-section>
			<!-- <view class="v-ml-40 v-mt-10">核销总订单数：{{total}}</view> -->
		</view>
		<view class="order-finish-item">
			<view v-for="(vo,index) in list" :key="vo.id">
				<v-order-item2-show :item="vo"/>
			</view>
			<uni-load-more :status="loadStatus" @click="getList"></uni-load-more>
		</view>
	</view>
</template>

<script setup>
	import {ref} from 'vue'
	import {onReachBottom} from '@dcloudio/uni-app'
	import vOrderItem2Show from '../components/v-order-item2-show/v-order-item2-show.vue'
	
	let today = uni.$tool.today();
	const takeAtRange = ref([today,today]);
	const loadStatus = ref("more");
	const list = ref([]);
	let total = ref(0);
	let page = 1;
	getList();
	
	function takeAtRangeChange(e){
		if(e.length == 0){
			takeAtRange.value = [today,today];
		}else{
			takeAtRange.value = e
		}
		reset();
		getList("reset");
	}
	
	function reset(){
		loadStatus.value = "more"
		page = 1
		total.value = 0
	}
	
	function getList(type){
		if(loadStatus.value != "more") return
		loadStatus.value = "loading"
		uni.$http.post("v1/getMyFinishOrderPageData", {page,takeAtRange:takeAtRange.value}).then(res=>{
			if(res.code != 1) return uni.$tool.tip(res.info||"系统错误")
			let pageData = res.data.pageData
			total.value = pageData.total
			if(pageData.total == 0){
				loadStatus.value = "nomore"
				list.value = []
				return
			}
			if(pageData.data.length == 0){
				loadStatus.value = "nomore"
				return
			}
			if(type == "reset") list.value = [...pageData.data]
			else list.value = [...(list.value), ...pageData.data]
			total.value = pageData.total
			if(list.value.length >= total.value){
				loadStatus.value = "nomore"
			}else{
				loadStatus.value = "more"
				page++
			}
		})
	}
	onReachBottom(()=>{
		getList()
	})
	
</script>

<style lang="scss" scoped>
	.order-finish-list{
		width: 100%;
		justify-content: flex-start;
		align-items: center;
		.order-item{
			// margin: 0;
		}

	}
</style>