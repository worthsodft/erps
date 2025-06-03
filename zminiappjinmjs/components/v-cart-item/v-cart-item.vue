<template>
	<view class="cart-item">
		<image class="cover" @click="detail" :src="item.cover" mode="aspectFill"/>
		<view class="info">
			<view class="title v-ellip" @click="detail">{{item.name}}</view>
			<view class="info-item">规　格：<text class="item-value">{{item.unit}}</text></view>
			<view class="info-item">起订量：<text class="item-value">{{item.min_buy_number}} {{item.unit}}</text></view>
			<view class="info-item">促销价：<text class="item-value price">￥{{item.self_price}} / {{item.unit}}</text></view>
			<view class="info-item">市场价：<text class="item-value market">￥{{item.market_price}} / {{item.unit}}</text></view>
			<view class="operate">
				<view @click.stop="">
					<v-number-box v-model="item.number" @change="numbeChange" :min="1" :max="item.stock" :width="30" background="#5aa1d8" color="#fff" />
				</view>
				<view class="total">
					小计: <text>￥{{item.amount}}</text>
				</view>
			</view>
		</view>
		<view class="del-item" @click.stop="delItem"><uni-icons type="close" size="30" color="#e43d33"></uni-icons></view>
	</view>
</template>

<script setup>
	import {ref} from "vue";
	
	const cartTrans = ref("")
	
	const props = defineProps({
		item: Object,
		index: [String,Number]
	})
	
	const emit = defineEmits(['delItem', "numbeChange"])
	
	function numbeChange(number){
		emit("numbeChange", {index:props.index,number})
	}
	
	// 清除项目
	function delItem(){
		emit("delItem", props.index)
	}
	
	// 商品详情
	function detail(){
		uni.$tool.navto("/pages/goods/detail?sn="+props.item.sn)
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
		.cover{
			width: 260rpx;
			height: 260rpx;
			background-color: $bg-gray;
		}
		.info{
			width: 400rpx;
			margin-left: 20rpx;
			display: flex;
			flex-direction: column;
			padding: 10rpx;
			.title{
				width: 350rpx;
			}
			.info-item{
				font-size: 26rpx;
				.item-value{
					color: $font-gray;
				}
				.price{
					color: $red;
					font-weight: bold;
				}
				.market{
					text-decoration: line-through;
				}
			}
			.operate{
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-top: 10rpx;
				.total{
					font-size: 30rpx;
				}
			}
		}
		.del-item{
			position: absolute;
			right: 10rpx;
			top: 10rpx;
		}
	}
</style>