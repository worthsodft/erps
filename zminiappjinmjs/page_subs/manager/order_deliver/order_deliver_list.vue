<template>
	<view class="container-v order-finish-list">
		<view class="v-w100 v-mb-10" style="text-align: center;">
			<view style="width: 90%;margin-left: 5%;">
				<uni-segmented-control activeColor="#6abfff" :current="current" :values="['待配货','配送中','已配送']" @clickItem="currentChange" styleType="button"></uni-segmented-control>
			</view>
		</view>
		<view class="v-w100 v-mb-10">
			<uni-section :title="dateTitle+'：'" type="line"></uni-section>
			<view style="width: 90%;margin-left: 5%;">
				<uni-datetime-picker v-model="takeAtRange" type="datetimerange" @change="takeAtRangeChange" rangeSeparator="至" />
			</view>
			<uni-section title="配送区域：" type="line"></uni-section>
			<view style="width: 90%;margin-left: 5%;">
				<uni-data-checkbox v-model="district" multiple :localdata="districts" @change="districtChange"/>
				<!-- <uni-data-select v-model="district" :localdata="districts" @change="districtChange" placeholder="请选择配送区域"></uni-data-select> -->
			</view>
			<uni-section :title="'总订单数：' + total" type="line"></uni-section>
			<uni-section :title="'配货商品总件数：' + goodsTotal + ' 件'" type="line" v-if="current == 0">
				<template v-slot:right>
					<view class="btn-sm btn-red v-mr-20" @click="showGoodsListPopup">确定配货</view>
				</template>
			</uni-section>
		</view>
		<view class="order-finish-item">
			<view v-for="(vo,index) in list" :key="vo.id">
				<v-order-item2-show :item="vo" :index="index" @select="selectItem"/>
			</view>
			<uni-load-more :status="loadStatus" @click="getList"></uni-load-more>
		</view>
		<v-goods-list ref="goodsListRef" :list="goodsList" :stationList="stationList" @confirm="pick"></v-goods-list>
	</view>
</template>

<script setup>
	import {ref} from 'vue'
	import {onReachBottom,onPullDownRefresh} from '@dcloudio/uni-app'
	import vOrderItem2Show from '../components/v-order-item2-show/v-order-item2-show.vue'
	import vGoodsList from '../components/v-goods-list/v-goods-list.vue'
	const current = ref(0);
	const dateTitle = ref("创建时间");
	const district = ref([]);
	const districts = ref([]);
	// const districtTotal = ref({});
	
	let today = uni.$tool.today();
	console.log(today);
	let lastday = uni.$tool.oneMonthAgo();
	const takeAtRange = ref([lastday+" 00:00:00",today+" 23:59:59"]);
	const loadStatus = ref("more");
	const list = ref([]);
	const selected = ref({});
	const total = ref(0);
	let page = 1;
	const goodsList = ref({});
	const goodsTotal = ref(0);
	const goodsListRef = ref();
	const stationList = ref([]);
	
	getUserInfoDistricts();
	getList();
	getStationOpeningList();
	
	// 更改配货状态
	function pick(station_gid){
		let sns = [];
		list.value.forEach(item=>{
			if(item.checked) sns.push(item.sn)
		});
		
		if(!station_gid) return uni.$tool.tip("请选择水站信息");
		uni.$http.post("v1/orderPick", {sns, station_gid}).then(res=>{
			if(res.code != 1) return uni.$tool.tip(res.info||"系统错误");
			uni.$tool.tip(res.info||"操作成功");
			reset();
			getList("reset");
			goodsListRef.value.close();
		})
	}
	
	function showGoodsListPopup(){
		if(!goodsList.value || Object.keys(goodsList.value).length == 0) return uni.$tool.tip("请选择配货商品");
		goodsListRef.value.open();
	}
	
	function selectItem(index){
		let order = list.value[index];
		order.checked = !order.checked;
		if(order.checked){
			for(let i in order.subs){
				let sub = order.subs[i];
				if(goodsList.value[sub.goods_sn]) goodsList.value[sub.goods_sn].goods_number += sub.goods_number;
				else goodsList.value[sub.goods_sn] = {
					goods_sn: sub.goods_sn,
					goods_cover: sub.goods_cover,
					goods_name: sub.goods_name,
					goods_number: sub.goods_number,
					goods_unit: sub.goods_unit,
				};
				goodsTotal.value += sub.goods_number
			}
			uni.$tool.tip("配货商品已添加");
		}else{
			for(let i in order.subs){
				let sub = order.subs[i];
				let number = goodsList.value[sub.goods_sn].goods_number - sub.goods_number;
				if(number <= 0) delete goodsList.value[sub.goods_sn];
				else goodsList.value[sub.goods_sn].goods_number = number;
				
				goodsTotal.value -= sub.goods_number
			}
			uni.$tool.tip("配货商品已去除");
		}
		list.value[index] = order;
	}
	
	function currentChange({currentIndex}){
		current.value = currentIndex;
		if(currentIndex == 2) dateTitle.value = "配送时间";
		else dateTitle.value = "创建时间";
		
		goodsList.value = {};
		goodsTotal.value = 0;
		reset();
		list.value = [];
		getList("reset");
	}
	
	function takeAtRangeChange(e){
		if(e.length == 0){
			takeAtRange.value = [lastday+" 00:00:00",today+" 23:59:59"];
		}else{
			takeAtRange.value = e
		}
		reset();
		getList("reset");
	}
	
	function districtChange({detail}){
		reset();
		getList("reset");
	}
	
	function reset(){
		loadStatus.value = "more"
		page = 1
	}
	
	function getStationOpeningList(){
		uni.$http.post("v1/getStationOpeningList").then(res=>{
			stationList.value = res.data.stationList;
		});
	}
	
	function getList(type){
		if(loadStatus.value != "more") return
		loadStatus.value = "loading"
		let params = {
			page,
			takeAtRange: takeAtRange.value,
			current: current.value,
			district: district.value
		};
		uni.$http.post("v1/getMyDeliverOrderPageData", params).then(res=>{
			if(res.code != 1) return uni.$tool.tip(res.info||"系统错误")
			let pageData = res.data.pageData
			total.value = pageData.total
			if(pageData.total == 0){
				loadStatus.value = "nomore"
				list.value = []
				goodsTotal.value = 0;
				return
			}
			if(pageData.data.length == 0){
				loadStatus.value = "nomore"
				return
			}
			if(type == "reset") list.value = [...pageData.data]
			else list.value = [...(list.value), ...pageData.data]
			// districtTotal.value = res.data.districtTotal;
			// for(let i in districts.value){
			// 	let dis = districts.value[i];
			// 	if(districtTotal.value[dis.value]) dis.text += `(${districtTotal.value[dis.value]})`;
			// 	else dis.text += `(0)`;
			// 	districts.value[i] = dis;
			// }
			if(list.value.length >= total.value){
				loadStatus.value = "nomore"
			}else{
				loadStatus.value = "more"
				page++
			}
		})
	}
	function getUserInfoDistricts(){
		uni.$http.post("v1/getUserInfoDistricts").then(res=>{
			districts.value = res.data.districts;
		});
	}
	onReachBottom(()=>{
		getList()
	})
	onPullDownRefresh(()=>{
		reset();
		getList("reset");
		setTimeout(()=>{
			uni.stopPullDownRefresh()
		}, 200)
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