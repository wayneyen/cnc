<template>
    <aside class="sidebar p-3 min-vh-100">
        <div class="form-group">
            <label>類型：</label>
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="type_lathe" name="type" class="custom-control-input" value="1" v-model="type">
                <label class="custom-control-label" for="type_lathe">車床</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="type_milling" name="type" class="custom-control-input" value="2" v-model="type" disabled="disabled">
                <label class="custom-control-label" for="type_milling">銑床</label>
            </div>
        </div>

        <div class="form-group">
            <label>輸入：</label>
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="input_single" name="input" class="custom-control-input" value="1" v-model="input" disabled="disabled">
                <label class="custom-control-label" for="input_single">單行</label>
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
            <textarea rows="15"
                v-model="lines"
                class="form-control mb-3"
                name="command_multiple"
                id="command_multiple"
                spellcheck="false"
                placeholder="請輸入多行指令"></textarea>

            <button class="btn btn-warning btn-block my-2" @click="submit">送出</button>
        </div>
    </aside>
</template>

<script>
    export default {
        data: () => {
            return {
                input_singles: [],
                lines: null
            }
        },

        props: {
            type: Number, // 類型：預設車床
            input: Number // 輸入：預設多行,
        },

        methods: {
            addInputSingle (e) {

            },

            // 多行輸入繪圖
            submit (e) {
                this.$emit('submit', this.lines);
            }
        }
    }
</script>

<style lang="scss">
    .sidebar {
        background-color: #000131;
        color: #FFF;
        font-weight: 600;
    }
    .custom-control-input:checked~.custom-control-label::before {
        border-color: #F08200;
        background-color: #F08200;
    }

    #command_multiple {
        font-family: Consolas;
    }
</style>
