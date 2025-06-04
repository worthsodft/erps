<template>
	<view class="select-item" @click="action">
		<view class="select-item-left">
			<view class="row">地址：<text class="value">{{item.province?item.province+", ":''}}{{item.city?item.city+', ':''}} {{item.district?item.district+', ':''}} {{item.street?item.street+', ':''}} {{item.detail}}</text></view>
			<view class="row">联系人：<text class="value v-mr-20">{{item.link_name}}</text> 电话：<text class="value">{{item.link_phone}}</text></view>
			<view class="row v-mt-10" v-if="item.is_default"><uni-tag text="默 认" type="primary" size="mini"></uni-tag></view>
		
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
		emit("action", props.item.value)
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
				}
			}
		}
	}


</style>