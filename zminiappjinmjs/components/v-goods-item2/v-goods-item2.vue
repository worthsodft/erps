<template>
	<view class="goods-item" @click="detail">
		<image class="cover" :src="item.cover" mode="aspectFill"/>
		<view class="info">
			<view class="title v-ellip">{{item.name}}</view>
			<view class="info-item">
				<text class="v-tag">{{item.min_buy_number}}{{item.unit}}起送</text>
				<text class="v-tag v-ml-10" v-if="item.deliver_fee > 0">配送费{{parseInt(item.deliver_fee)}}元 / {{item.unit}}</text>
			</view>
			<view class="info-item">促销价：<text class="item-value price">￥{{item.self_price}}</text><text class="item-value"> / {{item.unit}}</text></view>
			<view class="info-item cart-number" v-if="parseFloat(item.market_price)!=0">
				<view style="display: flex;">
					市场价：
					<text class="item-value market">￥{{item.market_price}}</text><text class="item-value"> / {{item.unit}}</text>
				</view>
				<view @click.stop="">
					<v-number-box :value="number" :min="0" :width="20" @change="update2cart" background="#5aa1d830" color="#5aa1d8" />
				</view>
			</view>
			<view class="operate">

<!-- 				<view class="cart" :class="cartTrans" @click.stop="add2cart">
					<uni-icons type="cart" :size="20" color="#f3a73f"></uni-icons>
				</view> -->
<!-- 				<view>
					<view class="btn-sm btn-red" @click.stop="buyNow">立即购买</view>
				</view> -->
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
	number.value = props.item.number||0
	
	// 商品详情
	function detail(){
		uni.$tool.navto("/pages/goods/detail?sn="+props.item.sn)
	}
	
	// 更新到购物车
	function update2cart(e){
		// cartIconAnimate();
		number.value = e
		let params = {
			sn: props.item.sn,
			number: number.value,
		}
		uni.$api.update2cart(params)
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
	$coverW: 150rpx;
	$br: 20rpx;
	.goods-item{
		width: 100%;
		background-color: $bg-gray;
		display: flex;
		margin-bottom: 20rpx;
		border: 1px solid $bg-gray;
		flex-shrink: 0;
		border-radius: $br;
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
			// margin-left: 20rpx;
			display: flex;
			flex-direction: column;
			padding: 10rpx;
			.title{
				margin-bottom: 10rpx;
			}
			.info-item{
				font-size: 26rpx;
				white-space: nowrap;
				margin-bottom: 10rpx;
				.item-value{
					color: $font-gray;
					white-space: nowrap;
				}
				.price{
					color: $org;
					font-weight: bold;
				}
				.market{
					text-decoration: line-through;
				}
				&.cart-number{
					display: flex;
					justify-content: space-between;
					flex-shrink: 0;
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