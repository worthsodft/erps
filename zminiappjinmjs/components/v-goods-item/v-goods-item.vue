<template>
	<view class="goods-item" @click="detail">
		<image class="cover" :src="item.cover" mode="aspectFill"/>
		<view class="info">
			<view class="title v-ellip">{{item.name}}</view>
			<view class="info-item">规　格：<text class="item-value">{{item.unit}}</text></view>
			<view class="info-item">起订量：<text class="item-value">{{item.min_buy_number}} {{item.unit}}</text></view>
			<view class="info-item">促销价：<text class="item-value price">￥{{item.self_price}} / {{item.unit}}</text></view>
			<view class="info-item">市场价：<text class="item-value market">￥{{item.market_price}} / {{item.unit}}</text></view>
			<view class="operate">
<!-- 				<view @click.stop="">
					<uni-number-box v-model="number" :min="item.min_buy_number" :width="30" background="#2979FF" color="#fff" />
				</view> -->
				<view class="cart" :class="cartTrans" @click.stop="add2cart">
					<uni-icons type="cart" :size="20" color="#f3a73f"></uni-icons>
				</view>
				<view>
					<view class="btn-sm btn-red" @click.stop="buyNow">立即购买</view>
				</view>
			</view>
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
	number.value = 1
	
	// 商品详情
	function detail(){
		uni.$tool.navto("/pages/goods/detail?sn="+props.item.sn)
	}
	
	// 添加到购物车
	function add2cart(){
		cartIconAnimate();
		let params = {
			sn: props.item.sn,
			number: number.value,
		}
		uni.$api.add2cart(params)
	}
	// 立即购买
	function buyNow(){
		// console.log({sn:props.item.sn,number:number.value});
		uni.$login.judgeLogin(()=>{
			uni.$tool.navto(`/pages/order/confirm?from=goods&goods_sn=${props.item.sn}&goods_number=${number.value}`)
		})
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
	.goods-item{
		width: 100%;
		background-color: $bg-gray;
		display: flex;
		margin-bottom: 20rpx;
		border: 1px solid $bg-gray;
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
				justify-content: flex-end;
				align-items: center;
				margin-top: 10rpx;
				.cart{
					width: 50rpx;
					height: 50rpx;
					margin-right: 40rpx;
					transition: transform 0.3s ease;
					display: flex;
					justify-content: center;
					align-items: center;
					border-radius: 50%;
					border: 1px solid $uni-warning;
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