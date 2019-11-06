<template>
<section class="container-fluid">
    <div class="card">
        <div class="card-body">
            <form @submit="submit">
                <b-form-group label-cols-md="1" :label="trans('agents.edit_page.auth_mode')">
                    <div>
                        <b-form-radio v-model="data.auth_mode" value="1" disabled>{{trans('agents.edit_page.auth_by_local')}}</b-form-radio>
                        <b-form-radio v-model="data.auth_mode" value="0" disabled>{{trans('agents.edit_page.auth_by_operator')}}</b-form-radio>
                    </div>
                    <div class="mt-2" v-if="data.auth_mode == 0">
                        <b-form-group :label="trans('agents.edit_page.callback_url')" label-for="callback-url">
                            <b-form-input id="callback-url"
                                type="text"
                                v-model="data.callback"
                                :state="handleErrors('callback')"
                                :required="data.auth_mode == 0"
                            ></b-form-input>
                            <b-form-invalid-feedback>
                                {{ errors['callback'] }}
                            </b-form-invalid-feedback>
                        </b-form-group>
                    </div>
                </b-form-group>
                <b-form-group label-cols-md="1" :label="trans('agents.edit_page.wallet_mode')">
                    <div>
                        <b-form-radio v-model="data.wallet_mode" value="0" disabled>{{trans('agents.edit_page.multi_wallet')}}</b-form-radio>
                        <b-form-radio v-model="data.wallet_mode" value="1" disabled>{{trans('agents.edit_page.single_wallet')}}</b-form-radio>
                    </div>
                    <div class="mt-2 row" v-if="data.wallet_mode == 1">
                        <b-form-group class="col-6" :label="trans('agents.edit_page.balance_url')" label-for="balance-url">
                            <b-form-input id="balance-url"
                                type="text"
                                v-model="data.url_balance"
                                :state="handleErrors('url_balance')"
                                :required="data.wallet_mode == 1"
                            ></b-form-input>
                            <b-form-invalid-feedback>
                                {{errors['url_balance']}}
                            </b-form-invalid-feedback>
                        </b-form-group>
                        <b-form-group class="col-6" :label="trans('agents.edit_page.deposit_url')" label-for="deposit-url">
                            <b-form-input id="deposit-url"
                                type="text"
                                v-model="data.url_deposit"
                                :state="handleErrors('url_deposit')"
                                :required="data.wallet_mode == 1"
                            ></b-form-input>
                            <b-form-invalid-feedback>
                                {{errors['url_deposit']}}
                            </b-form-invalid-feedback>
                        </b-form-group>
                        <b-form-group class="col-6" :label="trans('agents.edit_page.withdrawal_url')" label-for="withdrawal-url">
                            <b-form-input id="withdrawal-url"
                                type="text"
                                v-model="data.url_withdrawal"
                                :state="handleErrors('url_withdrawal')"
                                :required="data.wallet_mode == 1"
                            ></b-form-input>
                            <b-form-invalid-feedback>
                                {{errors['url_withdrawal']}}
                            </b-form-invalid-feedback>
                        </b-form-group>
                        <b-form-group class="col-6" :label="trans('agents.edit_page.rollback_url')" label-for="rollback-url">
                            <b-form-input id="rollback-url"
                                type="text"
                                v-model="data.url_rollback"
                                :state="handleErrors('url_rollback')"
                                :required="data.wallet_mode == 1"
                            ></b-form-input>
                            <b-form-invalid-feedback>
                                {{errors['url_rollback']}}
                            </b-form-invalid-feedback>
                        </b-form-group>
                    </div>
                </b-form-group>
                <b-form-group>
                    <button type="button" class="btn btn-danger"
                        @click="$bvModal.show('regenerateSecretDialog')">
                        {{trans('setting.regenerate-secret-code')}}
                    </button>
                </b-form-group>
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> {{trans('common.submit')}}</button>
                <button type="button" class="btn btn-danger" @click="cancel()"><i class="fa fa-ban"></i> {{trans('common.cancel')}}</button>
            </form>
        </div>
    </div>
    <b-modal
        id="regenerateSecretDialog"
        ref="modal"
        :title="trans('setting.insert-password')"
        @show="resetModal"
        @hidden="resetModal"
        @ok="regenerateSecret"
        :ok-title="trans('common.ok')"
        :cancel-title="trans('common.cancel')">
        <form ref="form" @submit.stop.prevent="regenerateSecret">
            <b-form-group
            :label="trans('common.password')"
            label-for="password">
                <b-form-input
                    id="password"
                    type="password"
                    v-model="password"
                    :state="handleErrors('password')"
                    required
                ></b-form-input>
                <b-form-invalid-feedback>
                    {{errors['password']}}
                </b-form-invalid-feedback>
            </b-form-group>
        </form>
    </b-modal>
</section>
</template>

<script>
export default {
    data() {
        return {
            data: {
                auth_mode: 0,
                callback: '',
                wallet_mode: 1,
                url_balance: '',
                url_deposit: '',
                url_withdrawal: '',
                url_rollback: '',
            },
            errors: [],
            password: '',
        };
    },
    mounted() {
        this.data = this.getJsonData('setting-data');
    },
    methods: {
        submit: function (e) {
            e.preventDefault();
            this.errors = [];
            this.$ajax('POST', '/api/setting', this.data)
            .then(res => {
                location.href = '/dashboard';
            })
            .catch(err => {
                if (err.response.status == 422){
                    let errors = err.response.data.errors;
                    for (let i in errors) {
                        errors[i] = errors[i].join(', ');
                    }
                    this.errors = errors;
                } else {
                    console.warn(err);
                }
            });
        },
        handleErrors(errorKey) {
            if (typeof this.errors[errorKey] === 'undefined') {
                return null;
            }
            return false;
        },
        cancel() {
            history.back();
        },
        regenerateSecret(e) {
            e.preventDefault();
            this.errors = [];
            this.$ajax('POST', '/api/regenerate-secret-code', { password: this.password })
            .then(res => {
                this.$root.$emit('bv::hide::modal', 'regenerateSecretDialog');
                this.regenerateFinish(res);
            })
            .catch(err => {
                if (err.response.status == 422){
                    let errors = err.response.data.errors;
                    for (let i in errors) {
                        errors[i] = errors[i].join(', ');
                    }
                    this.errors = errors;
                } else {
                    console.warn(err);
                }
            });
        },
        resetModal() {
            this.password = '';
            this.errors['password'] = null;
        },
        handleErrors(errorKey) {
            if (typeof this.errors[errorKey] === 'undefined' || this.errors[errorKey] == null) {
                return null;
            }
            return false;
        },
        regenerateFinish(data) {
            this.$bvModal.msgBoxOk(data, {
                title: this.trans('setting.regenerate-complete'),
                size: 'sm',
                buttonSize: 'sm',
                okVariant: 'success',
                headerClass: 'p-2 border-bottom-0',
                footerClass: 'p-2 border-top-0',
                centered: true
            })
        },
    },
}
</script>
