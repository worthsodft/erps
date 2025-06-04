<template>
	<view class="order-info-item" @click="action">
		<view class="info-item-title">{{title}}<text class="title2" :style="{color:title2color||'#999'}">{{title2||''}}</text></view>
		<view v-if="triggers[type]" class="info-item-value" :class="{'v-bold':bold}" :style="{color}">
			{{value}}
			<uni-icons v-if="showRightIcon" type="right" :color="color"></uni-icons>
		</view>
		<view v-else class="info-item-value" :class="{'v-bold':bold}" :style="{color}">{{value}}</view>
	</view>
</template>

<script setup>
	import {ref} from "vue";
	
	const props = defineProps({
		type: String,
		title: String,
		title2: String,
		title2color: String,
		value: [String,Number],
		color: "color",
		bold: Boolean,
		showRightIcon:{
			type: Boolean,
			default: true
		}
	})
	const triggers = ref({coupon:1,pay_type:1})
	
	const emit = defineEmits(['action'])
	
	function action(){
		if(triggers.value[props.type]){
			emit("action")
		}
	}
	
</script>

<style lang="scss" scoped>
	@import '@/static/assets/all.scss';
	.order-info-item{
		width: 100%;
		display: flex;
		justify-content: space-between;
		border-bottom: 1px solid $uni-border-1;
		padding: 10px 20px;
		box-sizing: border-box;
		.info-item-title{
			.title2{
				font-size: 24rpx;
				color: #999
			}
		}
	}
</style>