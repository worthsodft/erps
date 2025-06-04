<template>
	<view>
		<uni-popup ref="itemPopup" background-color="#fff" @change="popupChange">
			<view class="item-popup-main">
				<view class="item-popup-main-title">选择水站
					<view class="station-map">
						<view class="map-tag" @click="map">
							<uni-icons type="location" color="#5aa1d8"></uni-icons>地图模式
						</view>
					</view>
				</view>
				<view class="item-popup-main-list">
					<v-station-item v-for="(item,index) in list" :key="item.id" :item="item" @action="selectItem(item)"></v-station-item>
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script setup>
	import {ref} from 'vue'
	
	const props = defineProps({
		list: Array
	})
	
	const itemPopup = ref()
	
	const emit = defineEmits(['select'])
	
	const map = ()=>{
		uni.$tool.tip("水站地图功能开发中，敬请期待...", false, null, 3)
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
			text-align: center;
			display: flex;
			justify-content: center;
			align-items: center;
			background-color: $bg-gray;
			box-sizing: border-box;
			.station-map{
				position: absolute;
				right: 20rpx;
				top: 0;
				height: 100%;
				display: flex;
				align-items: center;
				.map-tag{
					border-radius: 24rpx;
					border: 1px solid $green;
					color: $green;
					padding: 2rpx 10rpx;
					display: flex;
					align-items: center;
				}

			}
		}
		.item-popup-main-list{
			width: 100%;
			height: 680rpx;
			margin: 100rpx 40rpx 20rpx;
			flex-shrink: 0;
			overflow-y: scroll;
		}
	}
</style>