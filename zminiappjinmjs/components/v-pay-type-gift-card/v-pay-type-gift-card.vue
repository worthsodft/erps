<template>
	<view class="gift-card-item" @click="select(index)" :class="{'color-green':card.checked}">
		<view class="select-item-icon">
			<uni-icons v-if="card.checked" type="checkbox-filled" color="#5aa1d8" size="24"></uni-icons>
			<uni-icons v-else type="checkbox" color="#999" size="24"></uni-icons>
		</view>
		<view class="item-line">余额：<text :class="card.checked?'color-green':'color-desc'">{{card.has||''}}</text></view>
		<view class="item-line">有效期：<text :class="card.checked?'color-green':'color-desc'">{{card.useful_expire_at||''}}</text></view>
		<view class="select-item-icon"><uni-icons type="right" color="#fff"></uni-icons></view>
	</view>
</template>

<script setup>
	const props = defineProps({
		index: Number,
		card: Object,
	})
	const emit = defineEmits(["action"])
	
	function select(index){
		emit("action", index)
	}
	
</script>

<style lang="scss" scoped>
	.gift-card-item{
		display: flex;
		justify-content: space-between;
		width: 100%;
		margin: 10rpx 0;
		align-items: center;
		box-sizing: border-box;
		.item-line{
			font-size: 22rpx;
		}
	}


</style>