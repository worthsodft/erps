<template>
	<view>
		<uni-popup ref="itemPopup" background-color="#fff">
			<view class="item-popup-main">
				<view class="item-popup-main-title">当前配货商品
<!-- 					<view class="station-map">
						<view class="map-tag" @click="map">
							<uni-icons type="location" color="#5aa1d8"></uni-icons>地图模式
						</view>
					</view> -->
				</view>
				<view class="station-list">
					<text>选择水站：</text>
					<view style="width: 450rpx;">
						<uni-data-select v-model="station_gid" :localdata="stationList" @change="stationChange"></uni-data-select>
					</view>
				</view>
				<view class="item-popup-main-list">
					<view class="select-item" v-for="(item,index) in list" :key="item.goods_sn" @click="preView(item.goods_cover)">
						<view class="select-item-left">
							<view class="row">
								<image class="goods-cover" :src="item.goods_cover||''" mode="aspectFill"></image>
								<text class="value">{{item.goods_name}}</text>
								<text class="value v-font-s30 v-ml-40">{{item.goods_number}} {{item.goods_unit}}</text>
							</view>
						</view>
						<!-- <uni-icons type="right" color="#999999"></uni-icons> -->
					</view>

					<view class="select-item v-no-border">
						<button @click="confirm" type="primary" style="width: 80%;background-color: #5aa1d8;color:#fff;">确认配货</button>
					</view>
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script setup>
	import {ref} from 'vue'
	
	const props = defineProps({
		list: Object,
		stationList: Array
	})
	
	const itemPopup = ref()
	const station_gid = ref("")
	
	const emit = defineEmits(['confirm'])
	
	function stationChange(gid){
		// console.log("水站gid: ", gid);
	}
	
	function preView(img){
		uni.previewImage({
			urls:[img]
		})
	}
	
	function confirm(item){
		if(!station_gid.value) return uni.$tool.tip("请选择水站信息");
		uni.$tool.confirm("确认需要配送的商品无误吗？", ()=>{
			emit("confirm", station_gid.value)
		})
	}
	
	function open(){
		itemPopup.value.open("bottom")
	}
	function close(){
		itemPopup.value.close()
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
		.station-list{
			margin-top: 100rpx;
			display: flex;
			align-items: center;
		}
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
			height: 720rpx;
			margin: 20rpx 40rpx 20rpx;
			flex-shrink: 0;
			overflow-y: scroll;
		}
		.select-item{
			padding: 20rpx;
			width: 100%;
			display: flex;
			justify-content: space-between;
			align-items: center;
			box-sizing: border-box;
			border-bottom: 1px solid $uni-border-1;
			&:first-child{
				border-top: 1px solid $uni-border-1;
			}
			.select-item-left {
				width: 90%;
				box-sizing: border-box;
				display: flex;
				flex-direction: column;
				.row{
					display: flex;
					align-items: center;
					.goods-cover{
						width: 80rpx;height: 80rpx;
						margin-right: 20rpx;
						border-radius: 10rpx;
					}
					.value{
						.not-open{
							font-size: 24rpx;
						}
					}
				}
			}
		}
		.v-no-border{
			border: none;
		}
	}
</style>