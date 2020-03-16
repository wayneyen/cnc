<template>
    <nav class="navbar navbar-dark bg-dark">
        <div class="navbar-brand">
            結果預覽：( 耗時 {{ runtime }} 秒 )
        </div>
    </nav>
</template>

<script>
export default {
    props: {
        runtime: Number
    }
}
</script>
