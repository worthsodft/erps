<template>
	<view class="container-v">
		<uni-section title="关于我们" type="line"></uni-section>
		<view class="container-h content">
			<!-- <uv-parse :content="content"></uv-parse> -->
			<rich-text :nodes="content"></rich-text>
		</view>
	</view>
</template>

<script setup>
	const props = defineProps({
		content: String
	});
</script>

<style lang="scss" scoped>
	.content{
		width: 100%;
		padding: 0 20rpx;
		align-items: center;
		box-sizing: border-box;
	}
</style>