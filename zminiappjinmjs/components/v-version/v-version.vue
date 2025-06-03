<template>
	<view class="container-h main">
		v{{version}}
	</view>
</template>

<script setup>
	import {ref} from 'vue'
	import config from "@/extend/config.js"
	const version = ref(config.version)
</script>

<style lang="scss" scoped>
	@import '@/static/assets/all.scss';
	.main{
		width: 100%;
		align-items: center;
		justify-content: center;
		color: #ccc;
		font-size: 30rpx;
		margin: 40rpx 0;
	}
</style>