<script>
	import config from '/extend/config'
	export default {
		onLaunch: async function(opts) {
			const res = await uni.$api.getAppConfig()
			const sysConfig = res.data.config
			this.globalData.shareData = sysConfig.shareData
			this.globalData.servicePhone = sysConfig.servicePhone
			
			let pid = opts?.query?.scene;
			uni.$login.do(pid, res=>{
				// console.log(res);
			});


		},
		onShow(opts) {
			// 微信确认收货组件的回调，在这里处理用户确认收货后的操作
			if(opts.referrerInfo.appId=="wx1183b055aeec94d1" && opts.referrerInfo.extraData.req_extradata){
				console.log('App Show', opts.referrerInfo.extraData.req_extradata)
			}
		},
		onHide() {
			// console.log('App Hide')
		},
		globalData:{
			shareData:{},
			servicePhone: ''
		}
		
	}
</script>

<style lang="scss">
	@import '@/uni_modules/uni-scss/index.scss';
	@import '@/static/iconfont/iconfont.css';
	// @import '@/static/assets/all.scss';
	/* #ifdef H5 */
	uni-page-head{
		display: none;
	}
	/* #endif */
	page{
		font-size: 28rpx;
		color: $uni-main-color;
	}

</style>
