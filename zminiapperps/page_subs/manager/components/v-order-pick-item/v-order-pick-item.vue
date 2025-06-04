<template>
	<view class="order-item">
		<view class="info">
			<view class="title v-mb-10"><text class="v-bold">配送水站：</text><text class="color-desc">{{item.station_title}}</text></view>
			<view class="title v-mb-10"><text class="v-bold">配送单号：</text><text class="color-desc">{{item.gid_}}</text></view>
			<view class=" v-mb-10">
				<text class="v-bold">发起人：</text>
				<view class="v-inline color-desc">{{item.username}}</view>
			</view>
			<view class=" v-mb-10">
				<text class="v-bold">发起时间：</text>
				<view class="v-inline color-desc">{{item.create_at}}</view>
			</view>
			<view class=" v-mb-10">
				<text class="v-bold">商品总件数：</text>
				<view class="v-inline color-desc v-bold">{{item.goods_total||''}} 件</view>
			</view>
			<view class="order-subs v-w100 v-mb-10">
				<view class="v-bold">商品列表：</view>
				<view class="order-sub" v-for="(sub,index) in item.goods_list" :key="sub.id">
					<view class="order-sub-item">
						<text>{{sub.goods_name}}</text>
						<view>
							<text class="v-bold v-mr-10">{{sub.goods_total}}</text>
							<text>{{sub.goods_unit}}</text>
						</view>
					</view>
				</view>
			</view>
			<view class="order-subs v-w100 v-mb-10" v-if="item.remark">
				<text class="v-bold">备注：</text>
				<view class="v-inline color-desc">{{item.remark}}</view>
			</view>
			<view class=" v-mb-10" v-if="item.confirm_by">
				<text class="v-bold">确认人：</text>
				<view class="v-inline color-desc">{{item.confirm_by_username}}</view>
			</view>
			<view class=" v-mb-10" v-if="item.confirm_at">
				<text class="v-bold">确认时间：</text>
				<view class="v-inline color-desc">{{item.confirm_at}}</view>
			</view>
		</view>
		<view class="operate">
			<view class="order-status">
				<text v-if="item.status == $enum.orderPick.STATUS_PICK_NOT" class="color-red">未配货</text>
				<text v-else-if="item.status == $enum.orderPick.STATUS_PICK_YES" class="color-green">已配货</text>
			</view>

			<view class="order-btns">
				<view class="btn-sm btn-green" @click.stop="pick" v-if="item.status == $enum.orderPick.STATUS_PICK_NOT">确认配货</view>
			</view>
		</view>
		

	</view>
</template>
<script setup>
	// 此组件仅用于各种列表显示，没有任何写操作
	import {ref} from "vue";
	
	const $enum = ref(uni.$enum)
	const emit = defineEmits(["pick"])
	
	const props = defineProps({
		item: Object,
		index: Number
	})
	
	function pick(){
		emit("pick", props.item)
	}
</script>

<style lang="scss" scoped>
	@import '@/static/assets/all.scss';
	$br: 20rpx;
	.order-item{
		width: calc(750rpx - 80rpx);
		background-color: $bg-gray;
		display: flex;
		// margin-left: 40rpx;
		margin-bottom: 40rpx;
		flex-shrink: 0;
		font-size: 26rpx;
		box-sizing: border-box;
		flex-direction: column;
		padding: 20rpx 0;
		border-radius: $br;
		box-shadow: 0 0 20rpx 2rpx #ccc;
		.info{
			width: 100%;
			// margin-left: 20rpx;
			padding: 0 40rpx;
			display: flex;
			flex-direction: column;
			// padding: 10rpx;
			box-sizing: border-box;
			.title{
				white-space: nowrap;
				position: relative;
				.take-type{
					position: absolute;
					top: 0;
					right: 0;
				}
			}
			.order-subs{
				.order-sub{
					width: 100%;
					.order-sub-item{
						display: flex;
						justify-content: space-between;
					}
				}
			}
			.info-item{
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

			
		}
		.operate{
			width: 100%;
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-top: 10rpx;
			padding: 20rpx 40rpx 0;
			border-top: 1px solid $uni-border-3;
			box-sizing: border-box;
			.order-status{
				font-size: 26rpx;
				&.v-deviler-select{
					padding: 10rpx 20rpx;
				}
			}
			.order-btns{
				display: flex;
				justify-content: space-between;
				align-items: center;
			}
		}
	}

</style>