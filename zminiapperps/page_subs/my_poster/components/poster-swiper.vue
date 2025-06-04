<template>
	<view style="text-align: center;">
		<!-- <image class="bg-set" v-if="imgList[currentIndex]" :src="imgList[currentIndex]"></image> -->
		<swiper :current="currentIndex" class="swiper"
		indicator-dots
		@change="swiperTab"
		:interval="3000"
		:duration="1000" :circular="true"
		:previous-margin="previous_next"
		:next-margin="previous_next">
			<swiper-item v-for="(item,index) in imgList" :key="index">
				<view class="swiper-item"><image :src="item" show-menu-by-longpress @click="preview" lazy-load class="swiper-itemImage" :class="currentIndex === index ? 'swiperItemActive' : ''"></image></view>
			</swiper-item>
		</swiper>
	</view>
</template>

<script>
	export default {
		props: {
			// 轮播图
			imgList: {
				type: Array,
				required: true
			}
		},
		data() {
			return {
				currentIndex: 0,		// 当前显示图片
				previous_next: "80rpx",	// 前后边距
			}
		},
		methods:{
			swiperTab (e) {
				this.currentIndex = e.detail.current;
				this.$emit("change", this.currentIndex);
			},
			preview(){
				uni.previewImage({
					current: this.currentIndex,
					urls: this.imgList,
				})
			}
		}
	}
</script>

<style scoped>
	.swiper{
		width: 100%;
		height: calc(100vh - 400rpx);
		position: absolute;
	}
	.swiper-itemImage{
		position: relative;
		top: 0;
		margin-top: 34rpx;
		width: calc(60rpx * 9);
		height: calc(65rpx * 16);
		border-radius: 24rpx;
	}
	.swiperItemActive{
		position: relative;
		transition: top 0.5s linear, height 0.5s linear;
		top: -34rpx;
		width: calc(65rpx * 9);
		height: calc(65rpx * 16);
	}
	.bg-set{
		position: fixed;
		width: 100%;
		height: 100%;
		top: 0;
		left: 0;
		z-index: -1;
		filter: blur(20rpx)
	}
</style>