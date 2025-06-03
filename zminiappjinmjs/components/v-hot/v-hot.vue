<template>
	<view class="container-v">
		<uni-section title="热销商品" type="line">
			<template v-slot:right>
				<view class="v-more" @click="more">
					<text>更多</text>
					<uni-icons type="right" color="#333"></uni-icons>
				</view>
			</template>
		</uni-section>
		<view class="container-v list">
			<v-goods-item2 v-for="item in list" :key="item.id" :item="item"></v-goods-item2>
		</view>
	</view>
</template>

<script setup>
	const props = defineProps({
		list: Array
	});
	
	function more(){
		uni.$tool.navto("/pages/goods/list")
	}
</script>

<style lang="scss" scoped>
	.list{
		width: 100%;
		padding: 0 20rpx;
		// align-items: center;
		box-sizing: border-box;
		&:last-child{
		}
	}
</style>