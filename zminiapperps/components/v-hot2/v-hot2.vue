<template>
	<view class="container-v">
		<uni-section :title="title" type="line">
			<template v-slot:right>
				<view class="v-more" @click="more">
					<text>更多</text>
					<uni-icons type="right" color="#333"></uni-icons>
				</view>
			</template>
		</uni-section>
		<view class="list">
			<view class="flow-item" v-for="item in list" :key="item.id" >
				<v-goods-item3 :item="item"/>
			</view>

		</view>
	</view>
</template>

<script setup>
	import {ref} from "vue";
	
	const props = defineProps({
		list: Array,
		title: {
			type: String,
			default: '热销商品'
		},
		path: {
			type: String,
			default: '/pages/goods/list'
		}
	});
	

	function more(){
		uni.$tool.navto(props.path);
	}
</script>

<style lang="scss" scoped>
	.list{
		width: 100%;
		padding: 0 20rpx;
		box-sizing: border-box;
		display: flex;
		justify-content: flex-start;
		flex-wrap: wrap;
		.flow-item{
			width: calc((100% - 20rpx) / 2);
			&:not(:nth-child(2n)){
				margin-right: 20rpx;
			}
			
		}
	}
</style>