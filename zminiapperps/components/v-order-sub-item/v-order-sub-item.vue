<template>
	<view class="order-sub-item" @click="detail">
		<image class="cover" :src="item.goods_cover" mode="aspectFill"/>
		<view class="info">
			<view class="title v-ellip">{{item.goods_name}}</view>
			<view class="info-item">规　格：<text class="item-value">{{item.goods_unit}}</text></view>
			<view class="info-item">起订量：<text class="item-value">{{item.goods_min_buy_number}} {{item.goods_unit}}</text></view>
			<view class="info-item">促销价：<text class="item-value">￥{{item.goods_self_price}} / {{item.goods_unit}}</text></view>
			<view class="info-item">市场价：<text class="item-value market">￥{{item.goods_market_price}} / {{item.goods_unit}}</text></view>
			<view class="info-item v-mt-30">销售数量：<text class="item-value">{{item.goods_number}}</text></view>
			<view class="info-item">小计金额：<text class="item-value price">￥{{item.goods_amount}}</text></view>
		</view>

	</view>
</template>

<script setup>
	import {ref} from "vue";
	
	const cartTrans = ref("")
	
	const props = defineProps({
		item: Object
	})
	const number = defineModel() 
	number.value = props.item.min_buy_number||1
	
	// 商品详情
	function detail(){
		uni.$tool.navto("/pages/goods/detail?sn="+props.item.goods_sn)
	}
	
	// 添加到购物车
	function add2cart(){
		cartIconAnimate();
		console.log({id:props.item.id,number:number.value});
	}
	// 立即购买
	function buyNow(){
		console.log({id:props.item.id,number:number.value});
		uni.$tool.navto("/pages/order/confirm")
	}
	function cartIconAnimate(){
		cartTrans.value = "scale-down";
		setTimeout(()=>{
			cartTrans.value = "scale-up";
		},100);
	}
	
	
</script>

<style lang="scss" scoped>
	@import '@/static/assets/all.scss';
	.order-sub-item{
		width: 100%;
		// background-color: $bg-gray;
		display: flex;
		margin-bottom: 20rpx;
		border-bottom: 1px solid $bg-gray;
		flex-shrink: 0;
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
				.cart{
					width: 30px;
					height: 30px;
					transition: transform 0.3s ease;
					&.scale-down {
						transform: scale(0.5);
					}
					&.scale-up {
						transform: scale(1);
					}
				}
			}
			
		}
	}
</style>