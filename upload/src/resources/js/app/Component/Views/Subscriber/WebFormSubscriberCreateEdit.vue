<template>
    <div class="">
        <web-form-create-edit-form :loading="loading" :submit-data="submitData" @cancel="reloadCurrentWindow" v-if="!preloader">
            <h3 class="mb-3 text-center"> {{$t('subscribe_now') }}</h3>
            <div class="form-group">
                <div class="row justify-content-center">
                    <div class="col-xl-2 col-md-3 col-sm-12 col-12 pt-2">
                        <label>{{ $t('email') }}</label>
                    </div>
                    <div class="col-xl-7 col-md-9 col-sm-12 col-xs-12">
                        <app-input type="email" :placeholder="$placeholder('email')" v-model="formData.email"
                            :error-message="$errorMessage(errors, 'email')" />
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row justify-content-center">
                    <div class="col-xl-2 col-md-3 col-sm-12 col-12 pt-2">
                        <label>{{ $t('first_name') }}</label>
                    </div>
                    <div class="col-xl-7 col-md-9 col-sm-12 col-xs-12">
                        <app-input type="text" :placeholder="$placeholder('first_name')" v-model="formData.first_name"
                            :error-message="$errorMessage(errors, 'first_name')" />
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row justify-content-center">
                    <div class="col-xl-2 col-md-3 col-sm-12 col-12 pt-2">
                        <label>{{ $t('last_name') }}</label>
                    </div>
                    <div class="col-xl-7 col-md-9 col-sm-12 col-xs-12">
                        <app-input type="text" :placeholder="$placeholder('last_name')" v-model="formData.last_name"
                            :error-message="$errorMessage(errors, 'last_name')" />
                    </div>
                </div>
            </div>

            <div class="form-group" v-if="!loader" v-for="field in customFields">
                <div class="row justify-content-center">
                    <div class="col-xl-2 col-md-3 col-sm-12 col-12 pt-2">
                        <label>{{ field.name }}</label>
                    </div>

                    <div class="col-xl-7 col-md-9 col-sm-12 col-xs-12" v-if="['text', 'textarea'].includes(field.type)">
                        <app-input :type="field.type" v-model="customFieldValue[field.name]" />
                    </div>
                    <template v-else>
                        <div :class="`col-xl-7 col-md-9 col-sm-12 col-xs-12 ${field.type === 'radio' ? 'pt-2' : ''}`">
                            <app-input :radio-checkbox-name="field.name" :list="generateInputList(field)" :type="field.type"
                                v-model="customFieldValue[field.name]" />
                        </div>
                    </template>
                </div>
            </div>
            <div class="form-group" v-else>
                <app-pre-loader />
            </div>
            
            <app-confirmation-modal
                v-if="showInfoModal"
                :title="modalTitle"
                :message="modalMessage"
                icon="check-circle"
                hideSecondButton=true
                firstButtonName="Ok"
                modalClass="success"
                modal-id="app-confirmation-modal"
                @confirmed="infoModalClose"
            />
        </web-form-create-edit-form>
        <app-pre-loader class="p-primary" v-else />
    </div>
</template>

<script>
import FormHelperMixins from "../../../Mixins/Global/FormHelperMixins";
import WebFormCreateEditForm from "../../Helper/Card/WebFormCreateEditForm";
import { subscribers_front_end } from "../../../config/apiUrl";
import { mapGetters, mapState } from "vuex";
import Templates from "../Template/Templates";
import { isValidDate } from "../../../Helpers/helpers";
import { axiosGet, urlGenerator } from "../../../Helpers/AxiosHelper";
import AppFunction from '../../../../core/helpers/app/AppFunction'

export default {
    name: "WebFormSubscriberCreateEdit",
    components: { Templates, WebFormCreateEditForm },
    mixins: [FormHelperMixins],
    props:{
        apiKey:"",
        storeUrl:"",
        updateUrl:""
    },
    data() {
        return {
            modalTitle: "Success",
            modalMessage: "",
            showInfoModal:false,
            formData: {
            },
            customFieldValue: {},
            customFields: [],
            subscribers_front_end
        }
    },
    methods: {
        submitData() {
            this.loading = true;
            this.message = '';
            this.errors = [];
            const formData = { ...this.formData, custom_fields: { ...this.customFieldValue },api_key: this.apiKey}
            this.submitFromFixin(
                'post',
                this.selectedUrl ? this.getUpdateUrl : this.getStoreUrl,
                formData
            );
        },
        afterSuccess({ data }) {
            this.showInfoModal=true;
            this.modalMessage = data.message;
        },
        afterSuccessFromGetEditData(response) {
            this.preloader = false;
            this.formData = response.data;
            this.lists = this.collection(response.data.lists).pluck();
            response.data.custom_fields.forEach(field => {
                if (field.value) {
                    this.customFieldValue[field.custom_field.name] = isValidDate(field.value) ? new Date(field.value) : field.value;
                }
            })
        },
        infoModalClose(){
            if (!this.selectedUrl) {
                this.reloadCurrentWindow();
            }
        },
        reloadCurrentWindow() {
            window.location = window.location.href;
        },
        generateInputList({ meta }) {
            if (meta) {
                return Array.from(new Set(meta.split(','))).map(m => {
                    return { id: m, value: m }
                })
            }
        }
    },
    computed: {
        ...mapState({
            loader: state => state.loading,
        }),
        ...mapGetters([
            'getFormattedCustomFields'
        ]),
        getStoreUrl(){
            return  this.storeUrl.split(AppFunction.getBaseUrl())[1];
        },
        getUpdateUrl(){
            return  this.updateUrl.split(AppFunction.getBaseUrl())[1];
        },

    },
    created() {
        this.$store.dispatch('getWebFormSubscriberCustomFields');
    },
    watch: {
        getFormattedCustomFields: {
            handler: function (fields) {
                if (Object.keys(fields).length) {
                    this.customFields = [...fields]
                }
            },
            immediate: true
        },
        loader: {
            handler: function (loader) {
                this.preloader = loader;
            },
            immediate: true
        }
    }
}
</script>
