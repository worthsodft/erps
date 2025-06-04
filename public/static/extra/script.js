$(function () {
    window.$body = $('body');

    /*! 初始化异步加载的内容扩展动作 */
    // $body.on('reInit', function (evt, $dom) {
    //     console.log('Event.reInit', $dom);
    // });

    /*! 追加 require 配置参数
    /*! 加载的文件不能与主配置重复 */
    // require.config({
    //     paths: {
    //         'xmSelect': ['/static/plugs/layui_exts/xmSelect'],
    //     },
        // shim: {
        //     'vue': ['json']
        // },
    // });
    // // 基于 Require 加载测试
    // require(['vue', 'md5'], function (vue, md5) {
    //     console.log(vue)
    //     console.log(md5.hash('content'))
    // });

    layui.config({
        base: '/static/plugs/layui_exts/'
    }).extend({
        xmSelect: 'xmSelect'
    });

    $.fn.headerAll=function(key=""){
        //  监听原 layui.table 的 toolbar 事件
        $(this).trigger('toolbar', function (item) {
            var btnElem = $(this);
            // 对应 layui.table 写法 layui.table.on('toolbar(tableData)',function(){ })
            if (item.event === 'LAYTABLE_COLS') {
                var panelElem = btnElem.find('.layui-table-tool-panel');
                var checkboxElem = panelElem.find('[lay-filter="LAY_TABLE_TOOL_COLS"]');
                var checkboxCheckedElem = panelElem.find('[lay-filter="LAY_TABLE_TOOL_COLS"]:checked');
                $('<li class="layui-form" lay-filter="LAY_TABLE_TOOL_COLS_FORM">' +
                    '<input type="checkbox" lay-skin="primary" lay-filter="LAY_TABLE_TOOL_COLS_ALL" '
                    + ((checkboxElem.length === checkboxCheckedElem.length) ? 'checked' : '') + ' title="全选">' +
                    '<span class="LAY_TABLE_TOOL_COLS_Invert_Selection">反选</span></li>')
                    .insertBefore(panelElem.find('li').first())
                    .on('click', '.LAY_TABLE_TOOL_COLS_Invert_Selection', function (event) {
                        layui.stope(event);
                        // 反选逻辑
                        panelElem.find('[lay-filter="LAY_TABLE_TOOL_COLS"]+').click();
                    });
                form.render('checkbox', 'LAY_TABLE_TOOL_COLS_FORM');
            }
        })
        // 监听筛选列panel中的全选
        form.on('checkbox(LAY_TABLE_TOOL_COLS_ALL)', function (obj) {
            $(obj.elem).closest('ul')
                .find('[lay-filter="LAY_TABLE_TOOL_COLS"]' + (obj.elem.checked ? ':not(:checked)' : ':checked') + '+').click();
            console.log(obj.elem.checked);
            let items = $('input[lay-filter="LAY_TABLE_TOOL_COLS"]');
            for(var i = 0; i < items.length; i++){
                layui.data(key, {
                    key: items[i].name
                    ,value: !obj.elem.checked
                })
            }
        });

        // 监听筛选列panel中的单个记录的change
        $(document).on('change', 'input[lay-filter="LAY_TABLE_TOOL_COLS"]', function (event) {
            var elemCurr = $(this);
            // 筛选列单个点击的时候同步全选的状态
            $('input[lay-filter="LAY_TABLE_TOOL_COLS_ALL"]')
                .prop('checked',
                    elemCurr.prop('checked') ? (!$('input[lay-filter="LAY_TABLE_TOOL_COLS"]').not(':checked').length) : false);
            form.render('checkbox', 'LAY_TABLE_TOOL_COLS_FORM');
        });
    }


    /*! 其他 javascript 脚本代码 */

    // 显示表格图片
    window.showTableImage = function (image, circle, size, title) {
        if (typeof image !== 'string' || image.length < 5) {
            return '<span class="color-desc">-</span>' + (title ? laytpl('<span class="margin-left-5">{{d.title}}</span>').render({title: title}) : '');
        }
        return laytpl('<div class="headimg {{d.class}} headimg-{{d.size}}" data-tips-image data-tips-hover data-lazy-src="{{d.image}}" style="{{d.style}}"></div>').render({
            size: size || 'ss', class: circle ? 'shadow-inset' : 'headimg-no', image: image, style: 'background-image:url(' + image + ');margin-right:0'
        }) + (title ? laytpl('<span class="margin-left-5">{{d.title}}</span>').render({title: title}) : '');
    };

    /**
     * 数字转大写字母
     * @param num
     * @returns {string}
     */
    window.num2A = function(num){
        return String.fromCharCode(65 + parseInt(num));
    }

    $.vandles = {
        /**
         *
         * @param url
         * @param opts object {
         *     data: '请求参数',
         *     title: '标题',
         *     area: '窗口大小',
         *     width: '宽度',
         *     offset: '偏移量',
         *     full: '全屏',
         *     maxmin: '最大化'
         * }
         * @param cb function(type)
         */
        modal(url, opts, cb){
            let defer = $.form.modal(url, opts.data||{}, opts.title|| '编辑', undefined, undefined, undefined, opts.area || opts.width || '800px', opts.offset || 'auto', opts.full !== undefined, opts.maxmin || false);
            defer.progress((type) => {
                // type === 'modal.close' && dset.closeRefresh && $.layTable.reload(dset.closeRefresh)
                cb && cb(type);
            });
        },
        iframe(url, opts, cb){
            $.form.iframe(url, opts.title|| 'IFRAME 窗口', opts.area || [opts.width || '800px', opts.height || '580px'], opts.offset || 'auto', ()=>{
                typeof opts.refresh !== 'undefined' && $.layTable.reload(opts.tableId || true);
            }, ()=>{
                cb && cb();
            }, opts.full !== undefined, opts.maxmin || false);
        },

        /**
         * 带样式导出
         * @param columns
         * @param title
         */
        onExportWithStyle(columns, title){
            require(['excel'], function (excel) {
                excel.bind(function (data) {
                    // 1. 处理表头
                    let titles = []
                    let colWidth = [];
                    columns.forEach((column)=>{
                        titles.push(column[0])
                        colWidth.push(column[2]||100)
                    })

                    // 2. 处理表数据
                    let items = [];
                    data.forEach(function (item) {
                        let row = [];
                        columns.forEach((column)=>{
                            let val = item[column[1]];
                            if(typeof column[3] == 'function'){
                                val = column[3](val);
                            }
                            row.push(val);
                        })
                        items.push(row);
                    });
                    items.unshift(titles);

                    // 3. 带样式导出
                    return this.withStyleBorder(items, $.vandles.nums2A(colWidth));
                }, `${title}_${layui.util.toDateString(Date.now(), '_yyyyMMdd_HHmmss')}`);
            });
        },

        /**
         * 导出excel文件
         * @param items
         * @param colWidth
         * @param title
         * @param titleRow 标题所在行
         */
        exportExcle({items,colWidth}, title, titleRow=1){

            // 自动计算列序号
            let lastCol = num2A(items[0].length-1);

            // 边框样式
            const borderStyle = {
                top: {style: 'thin', color: {rgb: '000000'}},
                bottom: {style: 'thin', color: {rgb: '000000'}},
                left: {style: 'thin', color: {rgb: '000000'}},
                right: {style: 'thin', color: {rgb: '000000'}}
            };

            // 可选，设置表头样式
            layui.excel.setExportCellStyle(items, `A${titleRow}:` + lastCol + `${titleRow}`, {
                s: {
                    font: {sz: 12, bold: true, color: {rgb: "FFFFFF"}, shadow: true},
                    fill: {bgColor: {indexed: 64}, fgColor: {rgb: "5FB878"}},
                    alignment: {vertical: 'center', horizontal: 'center'},
                    border: borderStyle
                }
            });

            // 可选，设置内容样式
            const alignmentStyle = {vertical: 'center', horizontal: 'left'};
            var style1 = {
                fill: {bgColor: {indexed: 64}, fgColor: {rgb: "EAEAEA"}},
                alignment: alignmentStyle,
                border: borderStyle

            }, style2 = {
                fill: {bgColor: {indexed: 64}, fgColor: {rgb: "FFFFFF"}},
                alignment: alignmentStyle,
                border: borderStyle
            };
            layui.excel.setExportCellStyle(items, 'A2:' + lastCol + items.length, {
                s: style1
            }, function (rawCell, newCell, row, config, currentRow, currentCol, fieldKey) {
                typeof rawCell !== 'object' && (rawCell = {v: rawCell});
                rawCell.s = Object.assign({}, style2, rawCell.s || {});
                return (currentRow % 2 === 0) ? newCell : rawCell;
            });

            // 可选，设置表格行宽高，需要设置最后的行或列宽高
            // rowsC[items.length] = 40;

            let colsC = {A:100};
            for (let i = 0; i < colWidth.length; i++) {
                colsC[num2A(i)] = colWidth[i] || 100;
            }
            colsC[lastCol] = 100;

            let options = {
                extend:{
                    // '!rows': layui.excel.makeRowConfig(rowsC, 30), // 设置每行高度，默认 33
                    '!cols': layui.excel.makeColConfig(colsC, 100) // 设置每行宽度，默认 160
                }
            };

            layui.excel.exportExcel(items, `${title}.xlsx`, 'xlsx', options);
        },
        nums2A(colWidth){
            let colsC = {};
            for (let i = 0; i < colWidth.length; i++) {
                colsC[num2A(i)] = colWidth[i] || 100;
            }
            return colsC;
        },
        strStar(input, s=4, e=4, len=3) {
            // 检查输入字符串长度是否足够
            if (input.length <= (s+e)) {
                return input; // 如果字符串长度小于等于8，则直接返回原字符串
            }

            // 获取前4位和后4位
            const start = input.substring(0, s);
            const end = input.substring(input.length - e);

            if (len < 0) len = 3;
            return start + '*'.repeat(len) + end;
        },
        round(num, scale=2) {
            if (scale < 0) scale = 2;
            if (scale == 0) return Math.round(num);
            let bit = Math.pow(10, scale);
            return Math.round(num * bit) / bit;
        }
    };
});