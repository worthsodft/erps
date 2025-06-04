<template>
	<view class="container-v my">
		<v-user-banner ref="userBannerRef"></v-user-banner>
		<v-user-bar ref="userBarRef"></v-user-bar>
		<v-user-order-tabs></v-user-order-tabs>
		<v-user-service></v-user-service>
		<v-version></v-version>
	</view>
</template>

<script setup>
	import {onShow,onPullDownRefresh} from '@dcloudio/uni-app'
	import {ref} from 'vue'
	
	const userBannerRef = ref()
	const userBarRef = ref()
	onShow(()=>{
		uni.$api.getCartTotal()
	})
	function reload(){
		userBannerRef.value.reload()
		userBarRef.value.reload()
	}
	onPullDownRefresh(()=>{
		reload()
		setTimeout(()=>{
			uni.stopPullDownRefresh()
		}, 200)
	})
</script>

<style lang="scss" scoped>
	page{
		background-color: #f5f5f5;
	}
	.my{
		background-color: #f5f5f5;
		position: relative;
	}
</style>
