<template>
    <form :data-url="url" ref="form">
        <div class=" border-0">
            <div class="">
                <slot></slot>
                <div class="row">
                    <div class="col-12 mt-5 text-center">
                        <app-submit-button :loading="loading" @click="submitData"/>
                        <app-cancel-button @click="$emit('cancel')" btn-class="ml-2" />
                    </div>
                </div>
            </div>
        </div>
    </form>
</template>

<script>

    export default {
        name: "WebFormCreateEditForm",
        props: {
            url: {
                required: false
            },
            loading: {
                required: true
            },
            submitData: {
                required: true,
            }
        }
    }
</script>

<style scoped>

</style>
