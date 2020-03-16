<template>
    <svg
        id="result"
        version="1.1"
        xmlns="http://www.w3.org/2000/svg"
        xmlns:xlink="http://www.w3.org/1999/xlink">
        <g id="n-paths">
            <template v-for="(path, index) in result.paths">
                    <path
                        v-if="path && path.strokeDasharray == 0"
                        :d="path.d"
                        :block="path.block"
                        :stroke-dasharray="path.strokeDasharray"
                        fill="none"
                        class="draw"
                        stroke-width="1"
                        stroke-linecap="round"
                    />
            </template>
        </g>

        <use xlink:href="#n-paths"
            id="base"
            v-if="result.loop"
            transform="translate(0, 0)"
            />

        <use xlink:href="#n-paths"
            id="loop"
            v-if="result.loop"
            :transform="`translate(${result.loop.r * 4}, -${result.loop.r * 4})`"
        />

        <!-- 循環基準軸 -->
        <path
            v-if="result.loop"
            :d="result.loop.base"
            stroke-dasharray="0"
            fill="none"
            class="draw"
            stroke="orange"
            stroke-width="1"
            stroke-linecap="round"
        />
    </svg>
</template>

<script>
    export default {
        props: {
            result: Object
        }
    }
</script>

<style lang="scss">
#result {
    position: absolute;
    top: 100px;
    left: 100px;
    width: 600px;
    height: 600px;
    background-color: transparent;
    z-index: 10;
}
#base {
    stroke: red;

}
#loop {
    stroke: orange;
}
</style>
