<template>
	<view class="select-item" @click="action">
		<view class="select-item-left">
			<view class="row">名称：
				<text class="value"><text v-if="item.is_open != 1" class="not-open">(暂停营业)</text>{{item.title}}</text>
			</view>
			<view class="row" v-if="item.detail">地址：<text class="value">{{item.province?item.province+", ":''}}{{item.city?item.city+', ':''}} {{item.district?item.district+', ':''}} {{item.street?item.street+', ':''}} {{item.detail}}</text></view>
			<view class="row">
				<text v-if="item.link_name" class="v-mr-20">联系人：<text class="value">{{item.link_name}}</text></text> 
				<text v-if="item.link_phone">电话：<text class="value">{{item.link_phone}}</text></text>
			</view>
			<view class="row" v-if="item.open_time">营业时间：<text class="value">{{item.open_time}}</text></view>
			<view class="row" v-if="item.remark">其他说明：<text class="value">{{item.remark}}</text></view>
			<view class="row" v-if="item.distance">距离我：<text class="value">{{item.distance}} 米</text></view>
		
		</view>
		<uni-icons type="right" color="#999999"></uni-icons>
	</view>
</template>

<script setup>
	const props = defineProps({
		item: Object,
	})
	const emit = defineEmits(["action"])
	function action(){
		if(props.item.is_open != 1) return uni.$tool.tip("当前水站已暂停营业", false, null, 3)
		emit("action", props.item)
	}
</script>

<style lang="scss" scoped>
	.select-item{
		padding: 20rpx;
		width: 100%;
		display: flex;
		justify-content: space-between;
		align-items: center;
		box-sizing: border-box;
		border-bottom: 1px solid $uni-border-1;
		.select-item-left {
			width: 90%;
			box-sizing: border-box;
			display: flex;
			flex-direction: column;
			.row{
				.value{
					color: $uni-secondary-color;
					.not-open{
						font-size: 24rpx;
					}
				}
			}
		}
	}


</style>