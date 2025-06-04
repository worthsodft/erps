<template>
	<swiper class="swiper" autoplay circular indicator-dots>
		<swiper-item class="item" v-for="item in list" :key="item.gid">
			<image class="img" :src="item.cover||''" mode="scaleToFill"/>
		</swiper-item>
	</swiper>
</template>

<script setup>
	const props = defineProps({
		list: Array
	})
</script>

<style lang="scss" scoped>
	.swiper{
		width: 100%;
		height: 400rpx;
		.item{
			.img{
				width: 100%;
				height: 100%;
			}
		}
	}
</style>