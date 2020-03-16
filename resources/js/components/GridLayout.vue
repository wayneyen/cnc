<template>
    <svg
        id="bg"
        version="1.1"
        xmlns="http://www.w3.org/2000/svg"
        xmlns:xlink="http://www.w3.org/1999/xlink">

        <g class="grid">
            <!-- 橫線 -->
            <line v-for="(grid, index) in grids"
                x1="0" :y1="grid * 10"
                x2="800" :y2="grid * 10" />

            <!-- 直線 -->
            <line v-for="(grid, index) in grids"
                :x1="grid * 10" y1="0"
                :x2="grid * 10" y2="800" />
        </g>

        <g class="legend">
            <!-- 座標軸圖例 X -->
            <line x1="730" y1="30" x2="730" y2="70" />
            <line x1="730" y1="30" x2="725" y2="35" />
            <line x1="730" y1="30" x2="735" y2="35" />
            <text x="725" y="25" font-weight="bold" stroke-width="0">X</text>

            <!-- 座標軸圖例 Z -->
            <line x1="730" y1="70" x2="770" y2="70" />
            <line x1="770" y1="70" x2="765" y2="65" />
            <line x1="770" y1="70" x2="765" y2="75" />
            <text x="775" y="75" font-weight="bold" stroke-width="0">Z</text>
        </g>

        <g class="mechanical-origin">
            <!-- 機械原點 -->
            <line x1="670" y1="100" x2="730" y2="100" />
            <line x1="700" y1="70" x2="700" y2="130" />
            <circle cx="700" cy="100" r="20" />
            <circle cx="700" cy="100" r="15" />
            <text x="710" y="135" font-weight="bold" fill="blue" stroke-width="0">機械原點</text>
        </g>

        <g class="axis">
            <!-- 程式座標軸 -->
            <line x1="0" y1="600" x2="800" y2="600" />
            <line x1="600" y1="0" x2="600" y2="800" />
        </g>
    </svg>
</template>

<script>
    export default {
        data() {
            return {
                grids: [...Array(81).keys()]
            }
        }
    }
</script>

<style lang="scss">
#bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 800px;
    height: 800px;
    background-color: #EEEEEE;
    z-index: 1;

    .grid {
        stroke: #DDD;
        stroke-width: 1;
    }

    .legend {
        stroke: #000;
        stroke-width: 1;
    }

    .mechanical-origin {
        stroke: #0000FF;
        stroke-width: 1;
        fill: transparent;
    }

    .axis {
        stroke: #AAA;
        stroke-width: 1;
    }
}
</style>
