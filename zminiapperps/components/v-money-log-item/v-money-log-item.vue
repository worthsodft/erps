<template>
	<view class="money-log-item">
		<view class="info">
			<!-- <view class="title v-ellip">{{item.title}}</view> -->
			<view class="info-item">当前余额：<text>￥{{item.before}}</text></view>
			<view class="info-item" v-if="item.delta<0">发生金额：<text class="color-red">-￥{{0-item.delta}}</text></view>
			<view class="info-item" v-else>发生金额：<text>+￥{{item.delta}}</text></view>
			<view class="info-item" v-if="item.target_gid">订单编号：<text>{{item.target_gid}}</text></view>
			<view class="info-item">发生日期：<text>{{item.create_at}}</text></view>
			<view class="log-type">
				<uni-tag v-if="item.log_type == 'order'" :text="item.log_type_txt" type="primary" />
				<uni-tag v-else-if="item.log_type == 'recharge'" :text="item.log_type_txt" type="success" />
				<uni-tag v-else-if="item.log_type == 'give'" :text="item.log_type_txt" type="warning" />
				<uni-tag v-else-if="item.log_type == 'refund'" :text="item.log_type_txt" type="error" />
				<uni-tag v-else-if="item.log_type == 'import'" :text="item.log_type_txt" type="success" />
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