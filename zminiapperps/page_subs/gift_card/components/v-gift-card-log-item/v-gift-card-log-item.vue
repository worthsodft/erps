<template>
	<view class="money-log-item">
		<view class="info">
			<view class="info-item">卡号：<text>{{item.card_sn}}</text></view>
			<template v-if="item.use_type == 0">
				<view class="info-item">消费金额：<text class="color-red">￥{{item.delta}}</text></view>
				<view class="info-item">剩余金额：<text>￥{{item.has}}</text></view>
			</template>
			<template v-else-if="item.use_type == 1">
				<view class="info-item">消费次数：<text class="color-red">{{item.delta}} 次</text></view>
				<view class="info-item">剩余次数：<text>{{item.has}} 次</text></view>
				<view class="info-item">计次商品：<text>{{item.take_goods_sn_txt}}</text></view>
			</template>
			
			<view class="info-item" v-if="item.order_sn">订单编号：<text>{{item.order_sn}}</text></view>
			<view class="info-item">消费日期：<text>{{item.create_at}}</text></view>
			<view class="info-item">核销人：<text>{{item.consume_openid_txt}}</text></view>
			<view class="log-type">
				<uni-tag v-if="item.use_type == 0" :text="item.use_type_txt" type="primary" />
				<uni-tag v-else-if="item.use_type == 1" :text="item.use_type_txt" type="success" />
				<uni-tag v-else text="未知" type="disabled" />
			</view>
		</view>
	</view>
</template>

<script setup>
	import {ref} from "vue";
	
	
	const props = defineProps({
		item: Object
	})
	
</script>

<style lang="scss" scoped>
	@import '@/static/assets/all.scss';
	.money-log-item{
		width: 100%;
		background-color: $bg-gray;
		display: flex;
		margin-bottom: 20rpx;
		border: 1px solid $bg-gray;
		border-radius: 10rpx;
		flex-shrink: 0;
		position: relative;
		.info{
			width: 400rpx;
			margin-left: 20rpx;
			display: flex;
			flex-direction: column;
			padding: 20rpx;
			.log-type{
				position:absolute;
				top: 20rpx;
				right: 20rpx;
			}
			.info-item{
				font-size: 26rpx;
				white-space: nowrap;
				margin-bottom: 10rpx;
				text{
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