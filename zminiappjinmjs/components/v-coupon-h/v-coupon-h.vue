<template>
	<view class="container-v">
		<uni-section title="优惠券" type="line">
			<template v-slot:right>
				<view class="v-more" @click="more">
					<text>我的优惠券</text>
					<uni-icons type="right" color="#333"></uni-icons>
				</view>
			</template>
		</uni-section>
		<view class="container-h list">
			<view v-for="item in list" :key="item.gid">
				<v-coupon-item :item="item"></v-coupon-item>
			</view>
		</view>
	</view>
</template>

<script setup>
	const props = defineProps({
		list: Array
	});
	function more(){
		uni.$tool.navto("/pages/my/coupon")
	}

</script>

<style lang="scss" scoped>
	.list{
		width: 100%;
		overflow-x: scroll;
		padding: 0 20rpx;
		box-sizing: border-box;
		&:last-child{
			padding-right: 0;
		}
	}
</style>