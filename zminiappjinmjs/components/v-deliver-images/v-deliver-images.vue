<template>
	<view>
		<uni-popup ref="itemPopup" background-color="#fff" @change="popupChange">
			<view class="item-popup-main">
				<view class="item-popup-main-title">上传配送照片</view>
				<view class="item-popup-main-list">
					<view class="image-list">
						<uni-file-picker limit="3" title="最多选择3张图片" @select="selectAfter" @delete="deleteAfter"></uni-file-picker>
						<view class="deliver-remark">配送说明<text class="tip"> (最多可输入200字)</text></view>
						<view class="deliver-textarea">
							<uni-easyinput type="textarea" maxlength="200" v-model="deliverRemark" placeholder="请输入配送说明"></uni-easyinput>
						</view>
					</view>
				</view>
			</view>
			<v-button-w80 @action="submit" title="确定提交"></v-button-w80>
			<view style="weight: 100%;height: 40rpx;"></view>
		</uni-popup>
	</view>
</template>

<script setup>
	import {ref} from 'vue'
	
	const props = defineProps({
		images: Array,
		sn: String
	})
	
	const itemPopup = ref()
	const deliverRemark = ref("")
	let urls = [];
	let temps = [];
	
	const emit = defineEmits(['success'])
	
	let submit = uni.$tool.throttle(()=>{
		if(temps.length == 0) return uni.$tool.tip("请上传配送照片");
		uni.$tool.confirm("是否确认已送达？", ()=>{
			doSubmit()
		})
	}, 1000);
	
	function doSubmit(){
		uni.$tool.showLoading("上传中...");
		uploads(()=>{
			uni.$tool.hideLoading();
			let params = {
				sn:props.sn,
				urls,
				remark: deliverRemark.value
			}
			uni.$http.post("v1/deliverOrder", params).then(res=>{
				if(res.code == 1){
					uni.$tool.tip(res.info||'操作成功');
					close();
					emit("success")
				}else uni.$tool.tip(res.info||"系统错误");
		
			})
		})
	}

	
	function selectAfter({tempFilePaths}){
		temps = [...temps, ...tempFilePaths];
	}
	function deleteAfter({index}){
		temps.splice(index, 1);
	}
	
	async function uploads(cb){
		urls = [];
		for(let i in temps){
			let params = {
				filePath: temps[i],
				name: 'file',
			}
			const res = await uni.$http.upload("v1/upload", params)
			urls.push(res.data.url);
		}
		cb && cb();
	}
	
	
	function open(){
		itemPopup.value.open("bottom")
	}
	function close(){
		itemPopup.value.close()
	}
	
	// function selectItem(item){
		// emit("select", item)
	// }
	function popupChange(e){
		// console.log(e);
	}
	
	// 暴露函数，供外部调用
	defineExpose({
		open,close
	})
	
</script>

<style lang="scss" scoped>
	@import '@/static/assets/all.scss';
	.item-popup-main{
		width: 100%;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: flex-start;
		.item-popup-main-title{
			position: fixed;
			top: 0;
			width: 100%;
			height: 80rpx;
			line-height: 80rpx;
			text-align: center;
			background-color: $bg-gray;
			box-sizing: border-box;
		}
		.item-popup-main-list{
			width: 100%;
			height: 680rpx;
			margin-top: 80rpx;
			flex-shrink: 0;
			overflow-y: scroll;
			box-sizing: border-box;
			.image-list{
				margin: 20rpx;
				display: flex;
				flex-direction: column;
				.deliver-remark{
					margin: 20rpx 0 10rpx;
					.tip{
						color: #999;
						font-size: 22rpx;
					}
				}
			}

		}
	}
	.main{
		padding: 0 20rpx;
		form{
			.form-item{
				width: 100%;
				padding: 20rpx 0;
				// border-bottom: 1px solid $uni-border-1;
			}
		}
	
	}
</style>