<template>
	<view class="cart-item">
		<view class="info">
			<view class="info-item">{{infoStr}}</view>
			<view class="info-item">{{item.detail}}</view>
			<view class="operate">
<!-- 				<view style="font-size: 24rpx;">
					默认:<switch :checked="item.is_default==1" @change="changeDefault" color="#2979ff" style="transform:scale(0.5)" />
				</view> -->
				<uni-tag v-if="item.is_default==1" type="warning" text="默认"></uni-tag>
				<view v-else></view>
				<view class="btns">
					<view v-if="source == 'order'" class="btn-sm btn-red v-mr-20" @click="select">选 择</view>
					<view class="btn-sm btn-green2" @click="edit">编 辑</view>
				</view>
			</view>
		</view>
		<view class="del-item" @click.stop="delItem">
			<!-- <uni-icons type="close" size="30" color="#e43d33"></uni-icons> -->
			<text>删除</text>
		</view>
	</view>
</template>

<script setup>
	import {ref} from "vue";
	const props = defineProps({
		item: Object,
		index: [String,Number],
		source: String,
	})
	
	const info = props.item.province + ", " +  props.item.city + ", " +  props.item.district + ", " +  props.item.street;
	const infoStr = ref(info);
	
	const emit = defineEmits(['delItem'])
	
	function changeDefault({detail}){
		// console.log("changeDefault", detail.value);
	}
	
	// 清除项目
	function delItem(){
		emit("delItem", props.index)
	}
	
	// 选择
	function select(){
		uni.$tool.navto("/pages/order/confirm?agid="+props.item.gid)
	}
	// 编辑
	function edit(){
		uni.$tool.navto("/pages/my/address-edit?gid="+props.item.gid)
	}
	
	
</script>

<style lang="scss" scoped>
	@import '@/static/assets/all.scss';
	.cart-item{
		width: 100%;
		background-color: $bg-gray;
		display: flex;
		margin-bottom: 20rpx;
		border: 1px solid $bg-gray;
		flex-shrink: 0;
		position: relative;
		.info{
			width: 600rpx;
			margin-left: 20rpx;
			display: flex;
			flex-direction: column;
			padding: 10rpx;
			.info-item{
				font-size: 30rpx;
			}
			.operate{
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-top: 10rpx;
				.btns{
					display: flex;
					justify-content: space-around;
					align-items: center;
				}
			}
		}
		.del-item{
			position: absolute;
			right: 10rpx;
			top: 10rpx;
			padding: 10rpx 20rpx;
			color: #999;
		}
	}
</style>