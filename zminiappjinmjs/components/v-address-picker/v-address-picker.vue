<template>
	<uni-data-picker :localdata="cities" :map="{value:'name', text:'name'}" :value="value" placeholder="请选择区域" popup-title="请选择地址"
		@change="change">
	</uni-data-picker>
</template>

<script setup>
	import {ref} from 'vue'
	const props = defineProps({
		value: String,
		cities: Array
	})
	const emit = defineEmits(['change'])
	
	function change({detail}){
		emit("change", detail.value)
		
	}

</script>

<style lang="scss" scoped>
	@import '@/static/assets/all.scss';
</style>