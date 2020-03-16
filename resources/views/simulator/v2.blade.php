<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>CNC simulator</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/busy-load@0.1.2/dist/app.min.css">
    <style>
    .sidebar {
        background-color: #000131;
        color: #FFF;
        font-weight: 600;
    }
    .custom-control-input:checked~.custom-control-label::before {
        border-color: #F08200;
        background-color: #F08200;
    }

    .svg {
        position: relative;
    }

    #bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 800px;
        height: 800px;
        background-color: #EEEEEE;
        z-index: 1;
    }
    #result {
        position: absolute;
        top: 100px;
        left: 100px;
        width: 600px;
        height: 600px;
        background-color: transparent;
        z-index: 10;
    }

    .G00 {
        stroke: red;
        stroke-width: 1;
        stroke-dasharray: 5;
    }
    .G01 {
        stroke: red;
        stroke-width: 1
    }
    </style>
</head>

<body>
    <div id="app" class="d-flex">
        <aside class="sidebar vh-100 w-25 p-3">
            <div class="form-group">
                <label>類型：</label>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="type_lathe" name="type" class="custom-control-input" value="1" v-model="type">
                    <label class="custom-control-label" for="type_lathe">車床</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="type_milling" name="type" class="custom-control-input" value="2" v-model="type">
                    <label class="custom-control-label" for="type_milling">銑床</label>
                </div>
            </div>

            <div class="form-group">
                <label>輸入：</label>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="input_single" name="input" class="custom-control-input" value="1" v-model="input">
                    <label class="custom-control-label" for="input_single">逐行</label>
                </div>
                <div class="custom-control custom-radio custom-control-inline">
                    <input type="radio" id="input_multiple" name="input" class="custom-control-input" value="2" v-model="input">
                    <label class="custom-control-label" for="input_multiple">多行</label>
                </div>
            </div>

            <div class="form-group" v-show="input == 1">
                <label>單行指令 <small class="text-warning">Enter 輸入</small></label>
                <template v-for="(input_single, index) in input_singles">
                    <input type="text"
                        name="command_single"
                        v-model="input_singles[index]"
                        @keyup.enter="addInputSingle"
                        class="form-control mb-1"
                        :class="index < input_singles.length - 1 ? 'is-valid' : ''"
                        :id="'command_single_' + index"
                        :disabled="index < input_singles.length - 1"
                        placeholder="請輸入單行指令">
                </template>
            </div>

            <div class="form-group" v-show="input == 2">
                <label>多行指令</label>
                <textarea rows="10"
                    v-model="multirow"
                    class="form-control"
                    name="command_multiple"
                    id="command_multiple"
                    placeholder="請輸入多行指令"></textarea>
                <button class="btn btn-warning btn-block mt-2" @click="draws">送出</button>
            </div>
        </aside>

        <div class="p-5">
            {{-- <h4>指令分析：</h4>
            <h5 v-if="input_singles.length == 1">← 請先在單行指令下 Enter 輸入</h5>

            <ul v-else class="list-group">
                <li v-for="(input_single, index) in input_singles" v-if="input_single != ''" class="list-group-item">
                    <h5>原始指令：@{{ input_single }}</h5>
                    <div>
                        <label>分析結果：</label>
                        <div v-for="input_single_result in input_single_results[index]">
                            鍵：<span class="text-primary">@{{ input_single_result.key }}</span>
                            值：<span class="text-primary">@{{ input_single_result.value }}</span>
                        </div>

                        <div class="text-warning" v-if="!input_single_results[index]">請先Enter 執行指令</div>
                        <div class="text-danger" v-else-if="input_single_results[index].length == 0">不正確的指令</div>
                    </div>
                </li>
            </ul> --}}

            <div>
                <h4>結果預覽：</h4>

                <div class="svg">
                    <svg
                        id="bg"
                        version="1.1"
                        xmlns="http://www.w3.org/2000/svg"
                        xmlns:xlink="http://www.w3.org/1999/xlink">

                        {{-- 橫線 --}}
                        <line v-for="grid in grids"
                            x1="0" :y1="grid * 10"
                            x2="800" :y2="grid * 10"
                            style="stroke:#DDD; stroke-width: 1"/>

                        {{-- 直線 --}}
                        <line v-for="grid in grids"
                            :x1="grid * 10" y1="0"
                            :x2="grid * 10" y2="800"
                            style="stroke:#DDD; stroke-width: 1"/>

                        {{-- 座標軸圖例 X --}}
                        <line x1="730" y1="30" x2="730" y2="70" style="stroke: black; stroke-width: 1"/>
                        <line x1="730" y1="30" x2="725" y2="35" style="stroke: black; stroke-width: 1"/>
                        <line x1="730" y1="30" x2="735" y2="35" style="stroke: black; stroke-width: 1"/>
                        <text x="725" y="25" font-weight="bold" fill="black">X</text>

                        {{-- 座標軸圖例 Z --}}
                        <line x1="730" y1="70" x2="770" y2="70" style="stroke: black; stroke-width: 1"/>
                        <line x1="770" y1="70" x2="765" y2="65" style="stroke: black; stroke-width: 1"/>
                        <line x1="770" y1="70" x2="765" y2="75" style="stroke: black; stroke-width: 1"/>
                        <text x="775" y="75" font-weight="bold" fill="black">Z</text>

                        {{-- 機械原點 --}}
                        <line x1="670" y1="100" x2="730" y2="100" style="stroke: blue; stroke-width: 1"/>
                        <line x1="700" y1="70" x2="700" y2="130" style="stroke: blue; stroke-width: 1"/>
                        <circle cx="700" cy="100" r="20" style="fill: transparent; stroke: blue; stroke-width: 1" />
                        <circle cx="700" cy="100" r="15" style="fill: transparent; stroke: blue; stroke-width: 1" />
                        <text x="710" y="135" font-weight="bold" fill="blue">機械原點</text>
                        {{--
                            <line x1="0" y1="300" x2="600" y2="300" style="stroke:#AAA; stroke-width: 1"/>
                            <line x1="300" y1="0" x2="300" y2="600" style="stroke:#AAA; stroke-width: 1"/>
                        --}}
                    </svg>

                    <svg
                        id="result"
                        version="1.1"
                        xmlns="http://www.w3.org/2000/svg"
                        xmlns:xlink="http://www.w3.org/1999/xlink">
                    </svg>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.10/dist/vue.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.4.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.10.2/js/fontawesome.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@8.17.1/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/busy-load@0.1.2/dist/app.min.js"></script>

    <script>
    var app = new Vue({
        el: '#app',
        data: {
            type: 1, // 類型：預設車床
            input: 2, // 輸入：預設多行,
            // input_singles: ['G02 X0. Y0.5 I-0.5 J0. F2.5'], // 單行輸入指令陣列：預設先開一個
            input_singles: [
                'G90 G00 X200. Y500.'
            ], // 單行輸入指令陣列：預設先開一個
            input_single_results: [],
//             multirow: `G90 G00 X0 Y0
// G91 X60 Y40
// G01 X40 Y80
// X-60 Y-20
// G90 G00 X0 Y0`,
// 多行指令內容，不縮排
            multirow:
`G28U0W0
G97M3S1000
T0202M08
G00X0.Z3.
G01Z0.F0.07
X18.
X20.Z-1.
Z-12.
X26.
X28.W-1.
W-14.
X40.W-22.
Z-60.
X54.
G28U0W0`
,

            grids: [...Array(81).keys()],

            svgs: null
        },

        mounted () {
            document.getElementById('command_multiple').focus()
        },

        methods: {
            addInputSingle (e) {
                const _this = this
                if (e.target.value != '') {
                    _this.input_singles.push('')

                    $.post("/api/analytics",
                        {
                            '_token': "{{ csrf_token() }}",
                            raw: e.target.value
                        },
                        function (data, textStatus, jqXHR) {
                            _this.input_single_results.push(data)
                        },
                        "json"
                    );
                } else {
                    swal.fire({
                        type: 'error',
                        title: '指令錯誤',
                        text: '輸入內容不可以是空白',
                        confirmButtonText: '知道了'
                    })

                    return false
                }

                _this.$nextTick(function () {
                    last_input_single = 'command_single_' + (_this.input_singles.length - 1)
                    document.getElementById(last_input_single).focus()
                })
            },

            // 多行輸入繪圖
            draws (e) {
                $.busyLoadFull("show")
                const _this = this
                var lastX = 0
                var lastY = 0

                if (_this.multirow != '') {
                    $.post("/api/draws",
                        {
                            '_token': "{{ csrf_token() }}",
                            multirow: _this.multirow
                        },
                        function (data, textStatus, jqXHR) {
                            $('.draw').remove()
                            data.forEach((value, index, array) => {
                                if (value) {
                                    switch (value.type) {
                                        case 'line':
                                            const line = makeSVG('line', value)
                                            document.getElementById('result').appendChild(line)

                                            lastX = value.x2
                                            lastY = value.y2
                                            break

                                        default:
                                            break
                                    }
                                }
                            })

                            document.getElementsByClassName('draw')[0].x1.baseVal.value = 600
                            document.getElementsByClassName('draw')[0].y1.baseVal.value = 0

                            // 歸位
                            const line = makeSVG('line', {
                                class: "G00 draw",
                                type: "line",
                                x1: lastX,
                                x2: 600,
                                y1: lastY,
                                y2: 0
                            })
                            document.getElementById('result').appendChild(line)

                            $.busyLoadFull("hide")
                            // const drawLength = document.getElementsByClassName('draw').length
                            // document.getElementsByClassName('draw')[drawLength - 1].x2.baseVal.value = 600
                            // document.getElementsByClassName('draw')[drawLength - 1].y2.baseVal.value = 0
                        },
                        "json"
                    );
                } else {
                    swal.fire({
                        type: 'error',
                        title: '指令錯誤',
                        text: '輸入內容不可以是空白',
                        confirmButtonText: '知道了'
                    }).then(() => {
                        document.getElementById('command_multiple').focus()
                        $.busyLoadFull("hide")
                    })

                    return false
                }

                _this.$nextTick(function () {
                    last_input_single = 'command_single_' + (_this.input_singles.length - 1)
                    document.getElementById(last_input_single).focus()
                })
            }
        }
    })

    function makeSVG (tag, attrs) {
        var el= document.createElementNS('http://www.w3.org/2000/svg', tag)
        for (var k in attrs)
            el.setAttribute(k, attrs[k])
        return el
    }
    </script>
</body>

</html>
