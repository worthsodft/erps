<template>
	<view class="container-v main">
		<v-address-edit v-for="(item,index) in list" :key="item.id" :item="item" :index="index"
		 @delItem="delItem" :source="source"></v-address-edit>
		<uni-load-more v-if="list.length < 1" status="nomore"></uni-load-more>
		<v-button-w80 @action="add" title="添加地址"></v-button-w80>
		<v-button-w80 @action="back" type="normal" title="返回我的"></v-button-w80>
	</view>
</template>

<script setup>
	import {onLoad} from '@dcloudio/uni-app'
	import {ref} from 'vue'
	
	const list = ref([])
	const loadStatus = ref("more")
	const source = ref("")
	let sourceParams = ""
	
	onLoad(opts=>{
		source.value = opts.source || ""
		getList()
	})
	
	async function getList(){
		const res = await uni.$http.get("v1/getAddressList");
		list.value = res.data.list
	}
	function back(){
		uni.$tool.navto("/pages/my/my")
	}
	function add(){
		uni.$tool.navto("/pages/my/address-edit")
	}
	function delItem(index){
		uni.$tool.confirm("确认要删除此地址吗？", async ()=>{
			let gid = list.value[index].gid
			const res = await uni.$http.post("v1/delAddress/"+gid);
			if(res.code == 1) list.value.splice(index, 1);
		})
	}
	
</script>

<style lang="scss" scoped>
	.main{
		padding: 0 20rpx;
	}
</style>
