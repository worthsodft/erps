<template>
	<view class="container-h content">
		<view class="icon"><uni-icons type="sound-filled" size="30" color="#5aa1d8"></uni-icons></view>
		<swiper class="swiper" autoplay circular :interval="interval">
			<swiper-item class="item" v-for="item in list" :key="item.gid">
				<view class="title v-ellip">{{item.title}}</view>
			</swiper-item>
		</swiper>
	</view>

</template>

<script setup>
	const props = defineProps({
		list: Array,
		interval:{
			type: Number,
			default: 3000
		}
	})
</script>

<style lang="scss" scoped>
	.content{
		background-color: #f5f5f5;
		width: 100%;
		.icon{
			width: 100rpx;
			left: 0;
			display: flex;
			align-items: center;
			justify-content: center;
			color: $uni-primary;
		}
		.swiper{
			width: 100%;
			height: 80rpx;
			color: black;
			.item{
				display: flex;
				align-items: center;
				.title{
					width: 100%;
					padding: 0 10rpx;
					white-space: nowrap;
					overflow: hidden;
				}
			}
		}
	}
</style>