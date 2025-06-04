<template>
	<view style="width: 80%;margin-left: 10%;margin-top: 40rpx;" :style="style">
		<button v-if="disabled" style="background-color: #999; color: #fff;border-color: #999;">{{title}}</button>
		<button v-else-if="type == 'normal'" @click="action" style="background-color: #fff; color: #000;">{{title}}</button>
		<button v-else @click="action" style="background-color: #6abfff; color: #fff;">{{title}}</button>
	</view>
</template>

<script setup>
	const props = defineProps({
		type: String,
		disabled: Boolean,
		title: {
			type: String,
			default: "确 定"
		},
		style:{
			type: Object,
			default: {}
		}
	})
	const emit = defineEmits(["action"])
	function action(){
		emit("action")
	}
</script>

<style>
</style>