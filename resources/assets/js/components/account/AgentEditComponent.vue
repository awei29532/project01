<template>
<section class="container-fluid">
    <div class="card">
        <div class="card-body">
            <form @submit="send">
                <b-form-group label-cols-md="1" :label="trans('common.username')" label-for="username">
                    <b-form-input id="username" 
                        type="text"
                        v-model="data.username"
                        :state="handleErrors('username')"
                        :disabled="agentid != 0"
                        required
                    ></b-form-input>
                    <b-form-invalid-feedback>
                        {{errors['username']}}
                    </b-form-invalid-feedback>
                </b-form-group>
                <b-form-group label-cols-md="1" :label="trans('common.password')" label-for="password">
                    <b-form-input id="password"
                        type="password"
                        v-model="data.password"
                        :state="handleErrors('password')"
                        :required="agentid == 0"
                    ></b-form-input>
                    <b-form-invalid-feedback>
                        {{ errors['password'] }}
                    </b-form-invalid-feedback>
                </b-form-group>
                <b-form-group label-cols-md="1" :label="trans('common.name')" label-for="name">
                    <b-form-input id="name"
                        type="text"
                        v-model="data.name"
                        :state="handleErrors('name')"
                        required></b-form-input>
                    <b-form-invalid-feedback>
                        {{ errors['name'] }}
                    </b-form-invalid-feedback>
                </b-form-group>
                <b-form-group label-cols-md="1" :label="trans('common.currency')" label-for="currency">
                    <b-form-select id="currency" 
                        v-model="data.currency" 
                        :state="handleErrors('currency')" 
                        :disabled="agentid != 0">
                        <option value="" disabled>{{trans('common.select')}}</option>
                        <option v-for="item in currencyOptions" :key="item.id" :value="item.code">{{item.code}}</option>
                    </b-form-select>
                    <b-form-invalid-feedback>
                        {{ errors['currency'] }}
                    </b-form-invalid-feedback>
                </b-form-group>
                <b-form-group label-cols-md="1" :label="trans('common.remark')" label-for="remark">
                    <b-form-textarea id="remark" v-model="data.remark"></b-form-textarea>
                </b-form-group>
                <b-form-group label-cols-md="1" :label="trans('agents.edit_page.auth_mode')">
                    <div>
                        <b-form-radio v-model="data.auth_mode" value="1">{{trans('agents.edit_page.auth_by_local')}}</b-form-radio>
                        <b-form-radio v-model="data.auth_mode" value="0">{{trans('agents.edit_page.auth_by_operator')}}</b-form-radio>
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
                        <b-form-radio v-model="data.wallet_mode" value="0">{{trans('agents.edit_page.multi_wallet')}}</b-form-radio>
                        <b-form-radio v-model="data.wallet_mode" value="1">{{trans('agents.edit_page.single_wallet')}}</b-form-radio>
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
                <b-form-group label-cols-md="1" :label="trans('agents.edit_page.provider_config')">
                    <div class="row">
                        <b-form-group class="col-3" v-for="item in providerList" :key="item.id" :label="item.name" :label-for="'provider-' + item.code">
                            <b-input-group>
                                <b-input-group-prepend is-text>
                                    <input type="checkbox" v-model="data.configs[item.code].status">
                                </b-input-group-prepend>
                                <b-form-input :id="'provider-' + item.code"
                                    type="number"
                                    :disabled="!data.configs[item.code].status" 
                                    v-model="data.configs[item.code].percent"
                                    :state="handleErrors('configs.' + item.name + '.percent')"
                                ></b-form-input>
                            </b-input-group>
                            <b-form-invalid-feedback>
                                {{ errors['configs.' + item.name + '.percent'] }}
                            </b-form-invalid-feedback>
                        </b-form-group>
                    </div>
                </b-form-group>
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> {{trans('common.submit')}}</button>
                <button class="btn btn-danger" @click="cancel()"><i class="fa fa-ban"></i> {{trans('common.cancel')}}</button>
            </form>
        </div>
    </div>
</section>
</template>

<script>
export default {
    props: {
        agentid: {
            required: true,
        },
    },
    data() {
        return {
            data: {
                id: this.agentid,
                username: '',
                password: '',
                name: '',
                remark: '',
                currency: '',
                auth_mode: 1,
                callback: '',
                wallet_mode: 0,
                url_balance: '',
                url_deposit: '',
                url_withdrawal: '',
                url_rollback: '',
                configs: {},
            },
            currencyOptions: [],
            providerList: [],
            errors: [],
        };
    },
    mounted() {
        this.currencyOptions = JSON.parse(document.getElementById('currency-list').textContent);
        this.providerList = JSON.parse(document.getElementById('provider-list').textContent);

        // handle agent data
        if (this.agentid != 0) {
            this.data = {
                id: this.agentid,
                ...JSON.parse(document.getElementById('agent-data').textContent),
            };
        } else {
            this.providerList.forEach((p) => {
                this.data.configs[p.code] = {
                    id: p.id,
                    status: p.status,
                    percent: 0,
                };
            });
        }
    },
    methods: {
        send: function (e) {
            e.preventDefault();
            this.errors = [];
            let $url = this.agentid == 0 ? '/api/accounts/agent/add' : '/api/accounts/agent/edit';
            axios.post($url, this.data)
            .then(res => {
                location.href = '/accounts/agent';
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
        }
    },
}
</script>
