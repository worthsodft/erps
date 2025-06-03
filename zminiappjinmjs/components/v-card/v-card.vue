<template>
	<view class="v-card">
		<view class="content">
			<slot name="content"></slot>
		</view>
		<view class="hr"></view>
		<view class="btns">
			<slot name="btns"></slot>
		</view>
	</view>
</template>

<script>
</script>

<style lang="scss" scoped>
	.v-card{
		width: 100%;
		margin-bottom: 40rpx;
		background-color: #f5f5f5;
		padding: 20rpx 0;
		border-radius: 20rpx;
		box-shadow: 0 0 20rpx 2rpx #ccc;
		.content{
			padding: 0 20rpx;
		}
		.hr{
			height: 10px;
			border-top: solid #ccc 1px;
			width: 100%;
			margin-top: 10rpx;
		}
		.btns{
			padding: 0 20rpx;
			display: flex;
			justify-content: flex-end;
			align-items: center;
		}
	}
</style>