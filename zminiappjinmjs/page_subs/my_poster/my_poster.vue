<template>
	<view class="my_poster">
		<view class="content">
			<poster-swiper @change="change" :imgList="list"></poster-swiper>
			<view class="no-data" v-if="!list.length">暂无海报模板</view>
		</view>
		<view class="btns">
			<button @click="save" type="primary" style="background-color: #5aa1d8;color:#fff;">长按图片 保存至相册</button>
		</view>
	</view>
</template>


<script setup>
	import { ref } from 'vue';
	import posterSwiper from './components/poster-swiper.vue'
	const list = ref([]);
	const currIndex = ref(0);
	const getMyPosterList = res => {
		uni.$tool.showLoading("海报生成中...");
		uni.$http.addon.get("userspread/v1/getMyPosterList").then(res=>{
			uni.$tool.hideLoading();
			list.value = res.data.list
		});
	}
	getMyPosterList()
	
	const change = (index) => {
		currIndex.value = index
	}
	const save = () => {
		// console.log(currIndex.value);
	}
</script>

<style lang="scss" scoped>
	.my_poster{
		display: flex;
		justify-content: center;
		flex-direction: column;
		align-items: center;
		background-color: #6abfff20;
		/* #ifdef H5 */
		height: calc(100vh - 44px);
		/* #endif */
		/* #ifndef H5 */
		height: 100vh;
		/* #endif */
		.content{
			width: 100%;
			height: 80vh;
			margin-top: 20rpx;
		}
		.btns{
			margin-top: 20rpx;
			width: 80%;
		}
	}
</style>