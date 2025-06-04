<template>
	<view class="container-h line-title">
		<view class="line"></view>
		<view class="v-inline">{{title}}</view>
	</view>
</template>

<script setup>
	import {ref} from 'vue'

	const props = defineProps({
		title: String,
		inline: Boolean
	})
	
</script>

<style lang="scss" scoped>
	@import '@/static/assets/all.scss';
	.line-title{
		align-items: center;
		.line{
			width: 4px;
			height: 12px;
			border-radius: 20rpx;
			background-color: $blue;
			margin-right: 12rpx;
		}
	}
</style>