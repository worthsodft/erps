<template>
	<view>
		<uni-popup ref="itemPopup" background-color="#fff" @change="popupChange">
			<view class="item-popup-main">
				<view class="item-popup-main-title">选择收货地址</view>
				<view class="item-popup-main-list">
					<v-address-item v-for="(item,index) in list" :key="item.id" :item="item" @action="selectItem(item)"></v-address-item>
				</view>
			</view>
			<v-button-w80 @action="add" title="添加地址"></v-button-w80>
			<view style="weight: 100%;height: 40rpx;"></view>
		</uni-popup>
		<v-address-form ref="addressFormRef" @saveSuccess="saveSuccess"></v-address-form>		
	</view>
</template>

<script setup>
	import {ref} from 'vue'
	
	const props = defineProps({
		list: Array
	})
	
	const itemPopup = ref()
	const addressFormRef = ref()
	
	const emit = defineEmits(['select',"reload"])
	
	function add(){
		addressFormRef.value.getCities()
		addressFormRef.value.reset()
		addressFormRef.value.open()
		// uni.$tool.navto("/pages/my/address")
	}
	
	function saveSuccess(item){
		selectItem(item)
		emit("reload")
		addressFormRef.value.close()
	}
	
	
	function open(){
		itemPopup.value.open("bottom")
	}
	function close(){
		itemPopup.value.close()
	}
	
	function selectItem(item){
		emit("select", item)
	}
	function popupChange(e){
		// console.log(e);
	}
	
	// 暴露函数，供外部调用
	defineExpose({
		open,close
	})
	
</script>

<style lang="scss" scoped>
	@import '@/static/assets/all.scss';
	.item-popup-main{
		width: 100%;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: flex-start;
		.item-popup-main-title{
			position: fixed;
			top: 0;
			width: 100%;
			height: 80rpx;
			line-height: 80rpx;
			text-align: center;
			background-color: $bg-gray;
			box-sizing: border-box;
		}
		.item-popup-main-list{
			width: 100%;
			height: 680rpx;
			margin: 100rpx 40rpx 20rpx;
			flex-shrink: 0;
			overflow-y: scroll;
		}
	}
	.main{
		padding: 0 20rpx;
		form{
			.form-item{
				width: 100%;
				padding: 20rpx 0;
				// border-bottom: 1px solid $uni-border-1;
			}
		}
	
	}
</style>