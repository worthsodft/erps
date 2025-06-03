<template>
	<view class="gift_card_bind">
		<view class="banner">
			<image :src="banner" mode="widthFix"></image>
		</view>
		<form class="form">
			
		</form>
		<view class="btns">
			<v-button-w80 @action="bind" title="绑 定"></v-button-w80>
		</view>
		<view class="remark">
			
		</view>
	</view>
</template>

<script setup>
	import {ref} from 'vue'
	const banner = ref("https://dev.abc.com/upload/c7/49762b8b9fd4aeb6157bcc6e78f28f.png")
	
	function bind(){
		console.log("bind");
	}
	
</script>

<style lang="scss" scoped>
	.gift_card_bind{
		display: flex;
		flex-direction: column;
		width: 100%;
		.banner{
			width: 100%;
			image{
				width: 100%;
			}
		}
		
		
		
	}
</style>