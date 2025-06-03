<template>
	<view class="container">
		<v-swiper v-if="swiperList.length" :list="swiperList"></v-swiper>
		<v-notice v-if="noticeList.length" :list="noticeList"></v-notice>
		<v-coupon-h v-if="couponList.length" :list="couponList"></v-coupon-h>
		<v-hot2 v-if="hotGoodsList.length" :list="hotGoodsList"/>	
		<v-hot2 v-if="hotGiftCardList.length" title="热销实物卡" path="/pages/goods/gift_card_list" :list="hotGiftCardList"/>
		<v-about v-if="aboutContent" :content="aboutContent"></v-about>
	</view>
</template>

<script>
	import config from "@/extend/config.js" 
	export default {
		data() {
			return {
				swiperList: [],
				noticeList: [],
				couponList: [],
				hotGoodsList: [],
				hotGiftCardList: [],
				aboutContent: "",
			}
		},
		onLoad(opts){
			uni.$tool.checkUpdateApp()
		},
		onShow(){
			uni.$api.getCartTotal()
			this.getInitDataIndex()
		},
		methods: {
			getInitDataIndex(){
				uni.$http.get("v1/getInitDataIndex").then(res=>{
					if(res.code != 1) return uni.$tool.tip(res.info||"系统错误")
					this.swiperList = res.data.swiperList
					this.noticeList = res.data.noticeList
					this.couponList = res.data.couponList
					this.hotGoodsList = res.data.hotGoodsList
					this.hotGiftCardList = res.data.hotGiftCardList
					this.aboutContent = res.data.aboutContent
				})
			}
		},
		onPullDownRefresh() {
			this.getInitDataIndex()
			setTimeout(()=>{
				uni.stopPullDownRefresh()
			}, 200)
		},
		onShareAppMessage() {
			return getApp().globalData.shareData
		}
	}
</script>

<style lang="scss" scoped>

</style>