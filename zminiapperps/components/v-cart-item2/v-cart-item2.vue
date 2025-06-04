<template>
	<view class="cart-item">
		<view class="item-check" @click="check">
			<uni-icons v-if="item.is_checked" type="checkbox-filled" size="20" color="#5aa1d8"></uni-icons>
			<uni-icons v-else type="checkbox"  size="20" color="#ccc"></uni-icons>
		</view>
		<image class="cover" @click="detail" :src="item.cover" mode="aspectFill"/>
		<view class="info">
			<view class="title v-ellip" @click="detail">{{item.name}}</view>
			<view class="info-item">
				<text class="v-tag">{{item.min_buy_number}}{{item.unit}}起送</text>
				<text class="v-tag v-ml-10">运费{{parseFloat(item.deliver_fee)}}元/{{item.unit}}</text>
			</view>
			<view class="info-item"><text class="item-value price">￥{{item.self_price}}</text> <text class="item-value">/ {{item.unit}}</text></view>
			<view class="operate">
				<view class="total">
					小计: <text>￥{{item.amount}}</text>
				</view>
				<view @click.stop="">
					<v-number-box v-model="item.number" @change="numbeChange" :min="1" :max="item.stock" :width="30" background="#5aa1d830" color="#8A8A8A" />
				</view>
			</view>
		</view>
		<view class="del-item" @click.stop="delItem"><text class="cart-del">删除</text></view>
	</view>
</template>

<script setup>
	import {ref} from "vue";
	
	const cartTrans = ref("")
	
	const props = defineProps({
		item: Object,
		index: [String,Number]
	})
	
	const emit = defineEmits(['delItem', "numbeChange", "check"])
	
	const check = ()=>{
		emit("check", {index:props.index,is_checked:!props.item.is_checked})
	}
	
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
	$coverW: 200rpx;
	$br: 20rpx;
	.cart-item{
		width: 100%;
		background-color: $bg-gray;
		display: flex;
		align-items: center;
		margin-bottom: 20rpx;
		border-radius: $br;
		border: 1px solid $bg-gray;
		flex-shrink: 0;
		position: relative;
		padding: 20rpx;
		box-sizing: border-box;
		.item-check{
			padding: 30rpx 10rpx;
		}
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
			margin-left: 10rpx;
			.title{
				width: 350rpx;
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
				// .total{
				// 	font-size: 30rpx;
				// }
			}
		}
		.del-item{
			position: absolute;
			right: 10rpx;
			top: 10rpx;
			.cart-del{
				color: #999;
				padding: 20rpx 10rpx 20rpx 20rpx;
			}
		}
	}
</style>