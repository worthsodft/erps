<template>
	<view class="order-sub-item" @click="detail">
		<image class="cover" :src="item.goods_cover" mode="aspectFill"/>
		<view class="info">
			<view class="title v-ellip">{{item.goods_name}}</view>
			<view class="info-item">
				<text class="v-tag">{{item.goods_min_buy_number}}{{item.goods_unit}}起送</text>
				<text class="v-tag v-ml-10" v-if="item.goods_deliver_fee">运费{{parseFloat(item.goods_deliver_fee)}}元/{{item.goods_unit}}</text>
			</view>
			<view class="info-item"><text class="item-value price">￥{{item.goods_self_price}}</text><text class="color-desc"> × {{item.goods_number}} ({{item.goods_unit}})</text></view>
			<!-- <view class="info-item">金额：<text class="item-value">￥{{item.goods_amount}}</text></view> -->
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
	$coverW: 120rpx;
	.order-sub-item{
		width: 100%;
		// background-color: $bg-gray;
		display: flex;
		border-bottom: 1px solid $bg-gray;
		flex-shrink: 0;
		padding: 20rpx;
		box-sizing: border-box;
		.cover{
			width: $coverW;
			height: $coverW;
			background-color: $bg-gray;
			border-radius: 10rpx;
		}
		.info{
			width: calc(100% - $coverW);
			display: flex;
			flex-direction: column;
			margin-left: 20rpx;
			.title{
				margin-bottom: 5rpx;
			}
			.info-item{
				font-size: 26rpx;
				margin-bottom: 5rpx;
				.item-value{
					color: $font-gray;
				}
				.price{
					color: $org;
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