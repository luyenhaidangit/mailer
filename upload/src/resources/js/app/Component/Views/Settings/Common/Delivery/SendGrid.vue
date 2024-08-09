<template>
    <div>
        <app-form-group page="page" type="text" :label="$t('api_key')" v-model="delivery.api_key"
            :placeholder="$placeholder('api_key')" :error-message="$errorMessage(errors, 'api_key')"
            :disabled="alias === 'brand'" />
    </div>
</template>

<script>
import DeliveryMixin from "./DeliveryMixin";
export default {
    name: "SendGrid",
    mixins: [DeliveryMixin],
    data() {
        return {
            delivery: {

            }
        }
    }
}
</script>

<style scoped></style>