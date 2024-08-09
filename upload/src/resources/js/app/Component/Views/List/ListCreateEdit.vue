<template>
    <div class="content-wrapper">
        <app-page-top-section
            :title="$fieldTitle(getActionType, 'list', true)"
            :directory="`${$t('lists')} | <a href='${urlGenerator(lists_front_end)}'>${$t('back_to',
            {destination: $allLabel('lists')} )}</a>`"
            icon="menu"
        />
        <div class="card border-0 card-with-shadow" v-if="!preloader">
            <div class="card-body">
                <form ref="form" :data-url='this.selectedUrl ? `${url.lists}/${formData.id}` : url.lists'>
                    <div class="form-group row">
                        <label class="col-xl-2 pt-2 col-md-3 col-12">{{ $fieldLabel('list') }}</label>
                        <div class="col-xl-7 col-md-9 col-12">
                            <app-input
                                type="text"
                                :placeholder="$placeholder('list', 'name')"
                                v-model="formData.name"
                                :error-message="$errorMessage(errors, 'name')"
                            />
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-xl-2 pt-2 col-md-3 col-12">{{ $t('description') }}</label>
                        <div class="col-xl-7 col-md-9 col-12">
                            <app-input
                                type="textarea"
                                :placeholder="$placeholder('description', '')"
                                v-model="formData.description"
                                :error-message="$errorMessage(errors, 'description')"
                            />
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-xl-2 pt-2 col-md-3 col-12">{{ $t('type') }}</label>
                        <div class="col-xl-7 col-md-9 col-12 pt-2">
                            <app-input
                                type="radio"
                                :list="[
                                    {id:'imported', value: $t('imported')},
                                    {id:'dynamic', value:  $t('dynamic')}]"
                                v-model="formData.type"
                                :error-message="$errorMessage(errors, 'type')"
                            />
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-xl-2 pt-2 col-md-3 col-12">{{ $fieldTitle('select', 'subscriber') }}</label>
                        <div class="col-xl-7 col-md-9 col-12">
                            <app-input
                                :key="render"
                                type="search-and-select"
                                :placeholder=" selected || $t('select_subscribers')"
                                v-model="formData.subscribers"
                                :options="subscriberOptions"
                                :error-message="$errorMessage(errors, 'subscribers')"
                                :is-animated-dropdown="false"
                                :disabled="allSubscribers"
                            />
                            <app-input
                                class="mt-3"
                                type="checkbox"
                                :list="[{id:'true', value:  $t('select_all_subscribers', {selected:this.selectedSubscriberCount})}]"
                                v-model="select_all_subscribers"
                                :error-message="$errorMessage(errors, 'all_subscribers')"
                            />
                            <app-note
                                v-if="allSubscribers && subscribersCount > 20000"
                                class="mb-primary"
                                :show-title="false"
                                :notes="$t('list_create_note_with_many_subscribers', {number: subscribersCount})"
                            />
                        </div>
                    </div>

                    <div class="form-group row mb-5">
                        <label class="col-xl-2 pt-2 col-md-3 col-12">{{ $fieldTitle('select', 'segment') }}</label>
                        <div class="col-xl-7 col-md-9 col-12">
                            <app-input
                                type="multi-select"
                                :list="segmentList"
                                list-value-field="name"
                                v-model="formData.segments"
                                :error-message="$errorMessage(errors, 'segments')"
                                :is-animated-dropdown="false"
                            />
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <app-submit-button
                            @click="submitData"
                            :loading="loading"
                        />
                        <app-cancel-button @click="redirectToList" btn-class="ml-2"/>
                    </div>
                </form>
            </div>
        </div>
        <app-pre-loader class="p-primary" v-else />
    </div>

</template>

<script>
    import {FormMixin} from "../../../../core/mixins/form/FormMixin.js";
    import {lists, lists_front_end, select_subscribers_api, subscribers_count, select_segments_api} from "../../../config/apiUrl";
    import FormHelperMixins from "../../../Mixins/Global/FormHelperMixins";
    import {urlGenerator} from "../../../Helpers/AxiosHelper";


    export default {
        props:['selectedUrl', 'action'],
        name: "ListCreateEdit",
        mixins: [FormMixin, FormHelperMixins],
        data() {
            return {
                url: {
                    lists
                },
                lists_front_end,
                select_all_subscribers:[],
                subscriberList: [],
                subscribersCount:0,
                segmentList: [],
                formData: {
                    subscribers: [],
                    all_subscribers: false,
                    segments: [],
                    type: 'imported'
                },
                render: 0,
                selected: '',
                subscriberOptions: {
                    url: urlGenerator(select_subscribers_api),
                    modifire: (v) => {
                        return { id: v.id, value: v.full_name + ' (' + v.email+ ')' }
                    },
                    multiple: true,
                    per_page: 10,
                    // queryName: 'last_name',
                    loader: 'app-pre-loader',
                    // params: {
                    //     'type': 'type1',
                    //     'isWanted': true
                    // }
                }
            }
        },
        methods: {
            submitData(){
                this.loading = true;
                const payload = {...this.formData};
                payload.all_subscribers = this.allSubscribers;
                if (this.action === 'copy'){
                    return  this.submitFromFixin('post', this.url.lists, payload)
                }
                return this.save(payload);
            },

            afterSuccess({data}) {
                this.toastAndReload(data.message)
                this.select_all_subscribers = [];
                if (!this.selectedUrl)  {
                    this.formData.name = '';
                    this.formData.subscribers = [];
                    this.formData.segments = [];
                    this.render++;
                    this.scrollToTop(false);
                } else {
                    this.redirectToList()
                }
            },

            afterSuccessFromGetEditData(response) {
                this.preloader = false;
                this.formData = {...this.formData ,...response.data};
                this.formData.segments = this.collection(response.data.segments).pluck('id')
                this.selected = `${response.data.subscribers_count} ${this.$t('selected')}`
            },
            getAllSubscriberCount() {
                this.axiosGet(subscribers_count).then(response => {
                    this.subscribersCount = response.data;
                });
            },
            getAllSubscriber() {
                this.axiosGet(select_subscribers_api).then(response => {
                    this.subscriberList = response.data.map(subscriber => {
                        const value = subscriber.full_name
                            ? `${subscriber.full_name} (${subscriber.email})`
                            : subscriber.email
                        return { id: subscriber.id, value }
                    });
                });
            },
            getAllSegments() {
                this.axiosGet(select_segments_api)
                    .then(response => this.segmentList = response.data );
            },

            redirectToList() {
                window.location = urlGenerator(lists_front_end);
            }

        },

        computed: {
            getActionType() {
                return this.action ? 'copy' : this.selectedUrl ? 'edit' : 'add';
            },
            allSubscribers(){
                return Boolean(this.select_all_subscribers.length);
            },
            selectedSubscriberCount(){
                if(this.select_all_subscribers.length){
                    return `(${this.subscribersCount} selected)`
                }
                return ``;
            }
        },

        mounted() {
            this.getAllSubscriberCount();
            this.getAllSegments();
        }

    }
</script>
