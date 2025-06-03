<template>
	<view class="coupon-item" :class="{mb}" :style="fullWidth?'width: 100%;':'width: 400rpx;'">
		<view class="wrapper" :class="'jb-'+status">
			<view class="content">
				<view class="money" v-if="item.money > 0">
					<text class="v-font-s20">¥</text>{{item.money}}<text class="v-font-s20"> 元</text>
				</view>
				<view v-else>
					<text class="money">{{item.discount}}</text><text class="money-sm"> % OFF</text>
				</view>
				<view class="coupon-title">— {{item.title}} —</view>
				<view class="time" v-if="item.expire_at">有效期：<text></text>{{item.expire_at}}</view>
				<view class="time" v-else>有效期：<text></text>{{item.expire_days}} 天</view>
			</view>
			<view class="split-line"></view>
			<view class="tip" :class="{fullWidth}">
				<view class="conditions">
					<text v-if="item.min_use_money == 0">任意金额</text>
					<text v-else>满 {{item.min_use_money}} 元</text>
					<text>\n可用</text>
				</view>
				<view v-if="type=='fetch'" class="usenow" :class="'usenow-color-'+status" @click="fetch">{{status == 0 ? '已 领 完' : '立即领取'}}</view>
				<view v-else-if="type == 'my'" class="usenow" :class="'usenow-color-'+1" @click="use">立即使用</view>
				<view v-else-if="type == 'select'" class="usenow" :class="'usenow-color-'+1" @click="select">选择使用</view>
				<!-- <button class="useNow" :class="'usenow-color-'+1" v-if="true">立即使用</button> -->
				<!-- <button class="useNow is-used" v-else-if="true">已核销</button> -->
				<!-- <button class="useNow is-used" v-else>已过期</button> -->
			</view>
		</view>
	</view>
</template>

<script setup>
	import {ref,watchEffect} from 'vue'
	const props = defineProps({
		item: Object,
		type: {
			type: String,
			default: "fetch"
		},
		mb: {
			type: Boolean,
			default: false
		},
		// 是否宽度占100%，用于竖向列表，false: 固定宽度400rpx，用于首页横向列表
		fullWidth: {
			type: Boolean,
			default: false
		}
	})
	
	const emit = defineEmits(['select'])
	
	const status = ref(props.type == 'my' ? 1 : 0)
	
	watchEffect(() => {
		setCouponStatus()
	})
	
	function setCouponStatus(){
		if(props.type == 'select') status.value = 1;
		else if(props.type != 'my') status.value = props.item.has_count > 0 ? 1 : 0
	}
	
	const fetch = uni.$tool.throttle(()=>{
		uni.$login.judgePhone(()=>{
			doFetch()
		})

	})
	
	const doFetch = ()=>{
		uni.$http.post("v1/fetchCoupon/"+props.item.gid).then(res=>{
			if(res.code != 1) return uni.$tool.tip(res.info||"系统错误")
			uni.$tool.tip(res.info || "操作成功")
		})
	}
	
	function use(){
		uni.$tool.navto("/pages/goods/list")
	}
	function select(){
		emit("select", props.item)
	}
	
</script>

<style lang="scss" scoped>
	.coupon-item {
	  margin-right: 20rpx;
	  box-sizing: border-box;
	  
	  &.mb{
		  margin-bottom: 20rpx;
	  }
	}
	.wrapper {
	  margin: 0 auto;
	  width: 100%;
	  min-height: 164rpx;
	  display: flex;
	}
	/*实现颜色渐变 */
	.jb-0{background:linear-gradient(90deg,#838383,#c1c1c1);}
	.jb-1{background:linear-gradient(90deg,#da4754,#fa7264);}
	.jb-2{background:linear-gradient(90deg,#fe9a28,#fdbd3f);}
	.jb-3{background:linear-gradient(90deg,#4175be,#5c98de);}
	.jb-4{background:linear-gradient(90deg,#4175be,#5c98de);}
	.usenow {
	  font-size: 20rpx;
	  border-radius: 25rpx;
	  padding: 5rpx 10rpx;
	  background-color: #fff;
	  display: flex;
	  justify-content: center;
	  align-items: center;
	  &.usenow-color-0{color: #948484;}
	  &.usenow-color-1{color: #de3c3a;}
	  &:active{
		  background-color: #ccc;
	  }
	}
	
	/* 前半部分 */
	.content {
	  position: relative;
	  width: 60%;
	  padding: 10rpx;
	  text-align: center;
	  display: flex;
	  justify-content: center;
	  align-items: center;
	  flex-direction: column;
	}
	/*后半部分样式*/
	.tip {
		width: 140rpx;
	  position: relative;
	  text-align: center;
	  display: flex;
	  flex-direction: column;
	  justify-content: center;
	  align-items: center;
	  &.fullWidth{
		  width: 40%;
	  }
	  .conditions {
		color: #eee;
		font-size: 20rpx;
		padding: 10rpx;
	  }
	  &:before, &:after{
	      content: '';
	      position: absolute;
	      width: 16rpx;
	      height: 8rpx;
	      background:#fff;
	      right: -8rpx;
	  }
	}
	/* 中间虚线 */
	.split-line {
	    position: relative;
	    flex: 0 0 0;
	    border-left: 2rpx dashed #eee;  
	}

	/* 顶部圆弧*/
	.content:before, .tip:before, .split-line:before{
	    border-radius: 0 0 8rpx 8rpx;
	    top: 0;
	}
	/* 底部圆弧 */
	.content:after, .tip:after, .split-line:after{
	    border-radius: 8rpx 8rpx 0 0;
	    bottom: 0;
	}
	.content:before, .content:after, .split-line:before, .split-line:after{
	    content: '';
	    position: absolute;
	    width: 16rpx;
	    height: 8rpx;
	    background: #fff;
	    left: -8rpx;
	}
	.money {
	  font-size: 40rpx;
	  color: #eee;
	}
	.money-sm{
		color: #FFB400;
		font-size: 20rpx;
	}
	.time{
		margin-top: 5rpx;
		color: #eee;
		font-size: 20rpx;
	}
	.coupon-title{
		margin-top: 5rpx;
		color: #eee;
		font-size: 20rpx;
	}

</style>