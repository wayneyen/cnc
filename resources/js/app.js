// import './bootstrap'

import jQuery from 'jquery'
import swal from 'sweetalert2'
import Vue from 'vue'
import 'busy-load'

window.$ = window.jQuery = jQuery
window.swal = swal
window.Vue = Vue

// Register vue components
Vue.component(
    'control-panel',
    require('./components/ControlPanel.vue').default
)

Vue.component(
    'header-panel',
    require('./components/HeaderPanel.vue').default
)

Vue.component(
    'grid-layout',
    require('./components/GridLayout.vue').default
)

Vue.component(
    'simulator-result',
    require('./components/SimulatorResult.vue').default
)

var app = new Vue({
    el: '#app',
    data: {
        runtime: 0, // 耗時
        type: 1, // 類型：預設車床
        input: 2, // 輸入：預設多行,
        input_single_results: [],
        multirow: null,

        result: {}
    },

    mounted () {
        document.getElementById('command_multiple').focus()
    },

    methods: {
        // 多行繪圖
        submit (lines) {
            const _this = this
            const t0 = performance.now()

            if (lines && lines != '') {
                $.busyLoadFull("show")

                $.ajax({
                    type: "post",
                    url: "/api/paths",
                    data: { lines: lines },
                    dataType: "json",
                }).done(function(msg) {
                    _this.result = msg
                }).fail(function(msg) {
                    alert(msg.responseJSON.message)
                }).always(function() {
                    $.busyLoadFull("hide")
                    const t1 = performance.now()
                    _this.runtime = parseFloat(((t1 - t0) / 1000).toFixed(5))
                })
            } else {
                swal.fire({
                    type: 'error',
                    title: '指令錯誤',
                    text: '輸入內容不可以是空白',
                    confirmButtonText: '知道了'
                }).then(() => {
                    document.getElementById('command_multiple').focus()
                })

                return false
            }
        }
    }
})

window.app = app
