<template>
	<view>
		<uni-popup ref="popup" background-color="#fff">
			<view class="popup-main">
				<view class="popup-main-title">选择支付方式</view>
				<view class="popup-main-list">
					<template v-for="(item,index) in list" :key="item.id">
						<view class="select-item-column" v-if="index == 'giftcard'">
							<view class="select-item-title">{{item}}</view>
							<view class="v-mt-20 v-w100" v-if="giftCardList.length > 0">
								<v-pay-type-gift-card v-for="(card,i) in giftCardList" :key="card.sn" :card="card" :index="i" @action="giftCardChange"></v-pay-type-gift-card>
								<view class="btn-sm btn-green" v-if="isGiftCardChecked" @click="useGiftCard(index)">确认使用</view>
								<view class="btn-sm btn-disable" v-else>确认使用</view>
							</view>
							<view class="color-desc v-ml-20 v-font-s20" v-else>暂无实物卡</view>
							<!-- <view class="select-item-icon"><uni-icons type="right" color="#999"></uni-icons></view> -->
						</view>
						<view class="select-item" @click="select(index)" v-else>
							<view class="select-item-title">{{item}} <text class="color-desc v-ml-20" v-if="index=='yue'">￥{{money}}</text></view>
							<view class="select-item-icon"><uni-icons type="right" color="#999"></uni-icons></view>
						</view>	
					</template>
					<!-- <button @click="reset" style="background-color: #f3a73f; color: #fff; height: 60rpx;line-height: 60rpx; font-size: 30rpx;">不使用</button> -->
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script setup>
	import {ref} from 'vue'
	
	const props = defineProps({
		list: Object
	})
	
	const money = ref(0)
	const popup = ref()
	const giftCardList = ref([])
	const emit = defineEmits(['select','reset','useGiftCard'])
	const isGiftCardChecked = ref(false) // 实物卡是否已选中
	
	uni.$api.getUserInfo(res=>{
		money.value = res.data.userInfo.money
	})
	getGiftCardList()
	
	// 我的实物卡列表
	function getGiftCardList(){
		uni.$http.get("v1/getMyUsableGiftCardList").then(res=>{
			giftCardList.value = res.data.giftCardList;
		})
	}
	
	function giftCardChange(i){
		giftCardList.value[i].checked = !giftCardList.value[i].checked;
		isGiftCardChecked.value = hasGiftCardChecked();
		
	}
	
	function useGiftCard(payType){
		let selectedSn = [];
		let cardHasMoney = 0;
		for(let i=0,len=giftCardList.value.length; i<len; i++){
			let item = giftCardList.value[i];
			if(item.checked) {
				cardHasMoney += +item.has;
				selectedSn.push(item.sn)
			}
		}
		emit("select", {payType,selectedSn,cardHasMoney})
	}
	
	// 是否有实物卡被选中
	function hasGiftCardChecked(){
		let isChecked = false;
		for(let i=0,len=giftCardList.value.length; i<len; i++){
			if(giftCardList.value[i].checked) {
				isChecked = true;
				break;
			}
		}
		return isChecked;
	}
	
	function open(){
		popup.value.open("bottom")
	}
	function close(){
		popup.value.close()
	}
	
	function select(payType){
		for(let i=0,len=giftCardList.value.length; i<len; i++){
			if(giftCardList.value[i].checked) {
				giftCardList.value[i].checked = false;
			}
		}
		isGiftCardChecked.value = false;
		emit("select", {payType})
	}

	// 不选择
	// function reset(e){
	// 	emit("reset")
	// }
	// function change(e){
	// 	console.log(e);
	// }
	
	// 暴露函数，供外部调用
	defineExpose({
		open,close
	})
	
</script>

<style lang="scss" scoped>
	@import '@/static/assets/all.scss';
	.popup-main{
		width: 100%;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: flex-start;
		.popup-main-title{
			position: fixed;
			top: 0;
			width: 100%;
			height: 80rpx;
			line-height: 80rpx;
			text-align: center;
			background-color: $bg-gray;
			box-sizing: border-box;
		}
		.popup-main-list{
			width: 100%;
			height: 680rpx;
			margin: 100rpx 0 20rpx;
			flex-shrink: 0;
			overflow-y: scroll;
			.select-item{
				display: flex;
				align-items: center;
				width: 100%;
				box-sizing: border-box;
				justify-content: space-between;
				padding: 20rpx 80rpx;
				border-bottom: 1px solid #f5f5f5;
			}
			.select-item-column{
				display: flex;
				flex-direction: column;
				width: 100%;
				box-sizing: border-box;
				align-items: start;
				justify-content: space-between;
				padding: 20rpx 80rpx;
				border-bottom: 1px solid #f5f5f5;
			}
		}
	}
</style>