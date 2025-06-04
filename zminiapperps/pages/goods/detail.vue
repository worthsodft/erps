<template>
	<view class="container-v detail">
		<swiper class="swiper" autoplay circular indicator-dots>
			<swiper-item class="item" v-for="(pic,index) in item.slider" :key="index">
				<image class="img" :src="pic||''" mode="scaleToFill"/>
			</swiper-item>
		</swiper>
		<view class="info">
			<view><text class="price-prefix">￥</text><text class="price">{{item.self_price}}</text> <text class="unit"> / {{item.unit}}</text> <text class="market" v-if="item.market_price > 0">￥{{item.market_price}}</text></view>
			<view class="title">{{item.name||''}}</view>
			<view class="item">
				<text class="v-desc">{{item.min_buy_number}}{{item.unit}}起送</text>
				<text class="v-desc" v-if="item.deliver_fee > 0">配送费{{parseInt(item.deliver_fee)}}元/{{item.unit}}</text>
			</view>

		</view>
		<view class="content">
			<view class="title color-desc">—— 商品详情 ——</view>
			<view class="desc"><uv-parse :content="item.desc"></uv-parse></view>
		</view>
		<view class="operate">
			<view class="cart" :class="cartTrans" @click.stop="toCart">
				<uni-icons type="cart" size="30" color="#000"></uni-icons>
				<view v-if="cartTotal > 0" class="v-cart-number">{{cartTotal}}</view>
			</view>
			<view class="btns">
				<view class="v-button btn-inner">
					<text class="add-cart" @click="add2cart">加入购物车</text>
					<text class="separate">|</text>
					<!-- <text class="buy-now" @click="buyNow">立即下单</text> -->
					<text class="buy-now" @click="toCart">去购物车</text>
				</view>
			</view>
<!-- 			<view class="btns">
				<view class="btn-item btn-cart" @click="add2cart">
					<view class="cart-icon" @click.stop="toCart">
						<uni-icons type="cart" size="20" color="#fff"></uni-icons>
					</view>
					<view class="cart-title">
						加入购物车
						<view class="v-cart-number">{{cartTotal}}</view>
					</view>
				</view>
				<view class="btn-item btn-buy" @click="buyNow">立即下单</view>
			</view> -->
		</view>

	</view>
</template>

<script setup>
	import {onLoad,onShareAppMessage} from '@dcloudio/uni-app'
	import {ref} from 'vue'
	
	const cartTrans = ref("")
	const item = ref({});
	const number = defineModel()
	const amount = ref(0)
	const cartTotal = ref(0)
	onLoad((opts)=>{
		let sn = opts.sn || "";
		if(!sn) return uni.$tool.tip("商品编号不存在")
		getDetail(sn)
		getCartTotal()
	})
	
	function getCartTotal(){
		uni.$api.getCartTotal((res)=>{
			cartTotal.value = res.data.total
		})
	}
	function cartIconAnimate(){
		cartTrans.value = "scale-down";
		setTimeout(()=>{
			cartTrans.value = "scale-up";
		},100);
	}
	function getDetail(sn){
		uni.$http.get("v1/goodsDetail/"+sn).then(res=>{
			item.value = res.data.goods
			number.value = 1
			amount.value = number.value * item.value.self_price
		})
	}
	function numberChnage(num){
		amount.value = num * item.value.self_price
	}
	function toCart(){
		uni.$tool.navto(`/pages/cart/cart`)
	}
	// 添加到购物车
	function add2cart(){
		cartIconAnimate();
		let params = {
			sn: item.value.sn,
			number: number.value,
		}
		uni.$login.judgeLogin(()=>{
			uni.$api.add2cart(params, res=>{
				cartTotal.value = res.data.total
			})
		})
	}
	// 立即购买
	function buyNow(){
		uni.$login.judgePhone(()=>{
			uni.$tool.navto(`/pages/order/confirm?from=goods&goods_sn=${item.value.sn}&goods_number=${number.value}`)
		})
	}
	
	onShareAppMessage(()=>{
		let shareData = getApp().globalData.shareData
		console.log(shareData);
		return shareData;
	})
	
	
</script>

<style lang="scss" scoped>
	.detail{
		.swiper{
			width: 100%;
			height: 750rpx;
			.item{
				font-size: 20rpx;
				.img{
					width: 100%;
					height: 100%;
				}
			}
		}
		.info{
			padding: 20rpx;
			.title{
				margin-bottom: 10rpx;
				font-size: 36rpx;
				font-weight: bold;
			}
			.item{
				font-size: 26rpx;
				color: $uni-info;
			}
			.price-prefix{
				color: $org;
				font-size: 30rpx;
			}
			.price{
				color: $org;
				font-size: 50rpx;
				font-weight: bold;
			}
			.unit{
				color: $uni-info;
				font-size: 30rpx;
			}
			.market{
				font-size: 30rpx;
				color: $uni-info;
				text-decoration: line-through;
			}
		}
		.content{
			width: 100%;
			margin-top: 20rpx;
			margin-bottom: 120rpx;
			.title{
				margin-bottom: 20rpx;
				width: 100%;
				text-align: center;
			}
			.desc{
				width: 100%;
				align-items: center;
				box-sizing: border-box;
			}
		}
		.operate{
			position: fixed;
			bottom: 0;
			left: 0;
			width: 100%;
			height: 100rpx;
			background-color: #f5f5f5;
			display: flex;
			align-items: center;
			padding: 0 20rpx;
			box-sizing: border-box;
			.cart{
				width: 20%;
				display: flex;
				align-items: center;
				justify-content: center;
				position: relative;
				transition: transform 0.3s ease;
				&.scale-down {
					transform: scale(0.5);
				}
				&.scale-up {
					transform: scale(1);
				}
				.v-cart-number{
					position: absolute;
					right: 20rpx;
					top: -10rpx;
					width: auto;
					min-width: 32rpx;
					height: 32rpx;
					padding: 0 4rpx;
					background-color: $org;
					color: #fff;
					border-radius: 32rpx;
					border: 2px solid #fff;
					text-align: center;
					font-size: 24rpx;
					white-space: nowrap;
				}
			}
			.btns{
				width: 80%;
				display: flex;
				align-items: center;
				justify-content: flex-end;
				.btn-inner{
					background: linear-gradient(to right, $green2, $green, $green2);
					display: flex;
					align-items: center;
				}
				.add-cart,.buy-now{
					padding: 0 20rpx;
					margin: 0;
				}
				.add-cart{
				}
				.separate{
					font-size: 20rpx;
					margin: 0 10rpx;
					color: #f5f5f5;
				}
				.buy-now{
					
				}
				
			}
		}
		/*
		.operate{
			position: fixed;
			bottom: 0;
			left: 0;
			width: 100%;
			.buy-info{
				height: 80rpx;
				background-color: $bg-gray;
				display: flex;
				justify-content: space-between;
				align-items: center;
				padding: 0 10rpx;
				.number{
					right: 20rpx;
					bottom: 20rpx;
				}
			}
			.btns{
				display: flex;
				justify-content: space-between;
				color: #fff;
				.btn-item{
					width: 50%;
					height: 100rpx;
					display: flex;
					align-items: center;
					justify-content: center;
					
					&.btn-cart{
						background-color: $uni-error;
						&:active{
							background-color: $uni-error-active;
						}
						.cart-icon{
							margin-right: 10rpx;
							padding: 10rpx;
							width: 32rpx;
							height: 32rpx;
							border: 1px solid #fff;
							border-radius: 50%;
							display: flex;
							align-items: center;
							justify-content: center;
						}
						.cart-title{
							position: relative;
							.v-cart-number{
								position: absolute;
								right: -50rpx;
								top: -10rpx;
								width: auto;
								min-width: 32rpx;
								height: 32rpx;
								padding: 0 4rpx;
								background-color: #fff;
								color: $red;
								border-radius: 32rpx;
								text-align: center;
								font-size: 24rpx;
								white-space: nowrap;
							}
						}
					}
					&.btn-buy{
						background-color: $uni-primary;
						&:active{
							background-color: $uni-primary-active;
						}
					}
				}
			}
		}
		*/

	}
</style>
