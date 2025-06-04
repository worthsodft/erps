export default {
	order:{
		STATUS_UNPAY: 0,
		STATUS_DELIVERING: 1,
		STATUS_FINISHED: 2,
		STATUS_REFUND: 9,
		
		DELIVER_STATUS_NOT: 0, // 待配送
		DELIVER_STATUS_ING: 1, // 配送中
		
		TAKE_TYPE_SELF: 0,
		TAKE_TYPE_DELIVER: 1,
	},
	
	invoice:{
		BUYER_TYPE_P: 0, // 自然人
		BUYER_TYPE_E: 1, // 公司
		
		INVOICE_TYPE_N: 0, // 普通
		INVOICE_TYPE_S: 1, // 专用
	},
	
	// 配货单状态
	orderPick:{
		STATUS_PICK_NOT: 0, // 未配货
		STATUS_PICK_YES: 1 // 已配货
	}
}