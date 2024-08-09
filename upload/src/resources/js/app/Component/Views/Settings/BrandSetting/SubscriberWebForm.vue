<template>
    <div v-if="!loading">
        <note :title="$fieldTitle('Web', 'form', true)" :description="webFormMessage" />
        <div class="row mt-primary">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-xl-2 col-md-3 col-sm-12 col-12 pt-2">
                        <label>{{ $t('embed') }}</label>
                    </div>
                    <div class="col-xl-7 col-md-9 col-sm-12 col-xs-12">
                        <app-input
                            type="textarea"
                            v-model="embededCode"
                        />
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-xl-2 col-md-3 col-sm-12 col-12 pt-2">
                        <label>{{ $t('direct_url') }}</label>
                    </div>
                    <div class="col-xl-7 col-md-9 col-sm-12 col-xs-12">
                        <app-input
                            type="text"
                            v-model="directUrl"
                        />
                    </div>
                </div>
            </div>

        </div>

    </div>
    <app-pre-loader v-else />
</template>

<script>
import FormHelperMixins from "../../../../Mixins/Global/FormHelperMixins";
import AppFunction from "../../../../../core/helpers/app/AppFunction";
import Note from "../../../Helper/Note/Note";
import { axiosGet } from "../../../../Helpers/AxiosHelper";
import { subscriber_api_url, web_form_subscriber_create } from '../../../../config/apiUrl'
import {FormMixin} from "../../../../../core/mixins/form/FormMixin";
export default {
    name: "SubscriberWebForm",
    components: { Note },
    mixins:[FormHelperMixins, FormMixin],
    data() {
        return {
            api: {
                store: '',
                update: '',
                custom_fields: ''
            },
            loading: true,
            showConfirmation: false
        }
    },
    methods: {
        getSubscriberUrl(regenerate = false) {
            const url = regenerate ? `${subscriber_api_url}/1` : subscriber_api_url;
            axiosGet(url).then(({ data }) => {
                this.api = data;
            }).finally(() => {
                this.loading = false;
            })
        }
    },
    computed: {
        embededCode(){
            return `<iframe src="${ AppFunction.getBaseUrl()}/${web_form_subscriber_create}" frameborder="0" allowfullscreen width="100%" height="500" ></iframe>`;
        },
        directUrl(){
            return `${ AppFunction.getBaseUrl()}/${web_form_subscriber_create}`;
        },
        webFormMessage(){
            return `<ol><li>${this.$t('subscriber_web_form_warning')}</li><li>${this.$t('subscriber_web_form_message')} Or you can <a href="${this.directUrl}" target="_blank">click here</a>.</li></ol>`;
        },

    },
    created() {
        this.getSubscriberUrl();
    }
}
</script>

<style scoped>
.feather-16 {
    width: 16px;
    height: 16px;
    cursor: pointer;
}
</style>
