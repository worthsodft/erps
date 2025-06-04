<template>
	<view class="container-v order-finish-list">
		<view class="v-w100 v-mb-10">
			<uni-section title="发起时间：" type="line"></uni-section>
			<view style="width: 90%;margin-left: 5%;">
				<uni-datetime-picker v-model="createAtRange" type="daterange" @change="createAtRangeChange" rangeSeparator="至" />
			</view>
			<uni-section :title="'配货单数：'+ total" type="line"></uni-section>
		</view>
		<view class="order-finish-item">
			<view v-for="(vo,index) in list" :key="vo.id">
				<v-order-pick-item :item="vo" @pick="pick"/>
			</view>
			<uni-load-more :status="loadStatus" @click="getList"></uni-load-more>
		</view>
	</view>
</template>

<script setup>
	import {ref} from 'vue'
	import {onReachBottom} from '@dcloudio/uni-app'
	import vOrderPickItem from '../components/v-order-pick-item/v-order-pick-item.vue'
	let today = uni.$tool.today();
	const createAtRange = ref([today,today]);
	const loadStatus = ref("more");
	const list = ref([]);
	let total = ref(0);
	let page = 1;
	getList();
	
	// 订单配货
	function pick(item){
		uni.$tool.confirm("确认要配货吗？", ()=>{
			
			uni.$http.post("v1/orderPickConfirm/"+item.gid).then(res=>{
				uni.$tool.tip("配货成功")
				reset();
				getList("reset");
			})
		})
	}
	
	function createAtRangeChange(e){
		if(e.length == 0){
			createAtRange.value = [today,today];
		}else{
			createAtRange.value = e
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
		uni.$http.post("v1/getMyStationOrderPickPageData", {page,createAtRange:createAtRange.value}).then(res=>{
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