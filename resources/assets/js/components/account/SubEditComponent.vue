<template>
<section class="container-fluid">
    <div class="card">
        <div class="card-body">
            <form @submit="send">
                <b-form-group label-cols-md="1" :label="trans('common.username')" label-for="username">
                    <b-input-group>
                        <b-input-group-text slot="append" v-if="subid == 0">
                            @{{data.agent ? data.agent : 'admin'}}
                        </b-input-group-text>
                        <b-form-input id="username"
                            type="text"
                            v-model="data.username"
                            :state="handleErrors('username')"
                            :disabled="subid != 0"
                            required
                        ></b-form-input>
                    </b-input-group>
                    <b-form-invalid-feedback>
                        {{errors['username']}}
                    </b-form-invalid-feedback>
                </b-form-group>
                <b-form-group label-cols-md="1" :label="trans('common.password')" label-for="password">
                    <b-form-input id="password"
                        type="password" 
                        v-model="data.password"
                        :state="handleErrors('password')"
                        :required="subid == 0"
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
                        required
                    ></b-form-input>
                    <b-form-invalid-feedback>
                        {{ errors['name'] }}
                    </b-form-invalid-feedback>
                </b-form-group>
                <b-form-group label-cols-md="1" :label="trans('common.remark')" label-for="remark">
                    <b-form-textarea id="remark" v-model="data.remark"></b-form-textarea>
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
        subid: {
            required: true,
        },
    },
    data() {
        return {
            data: {
                id: this.subid,
                agent: '',
                username: '',
                password: '',
                name: '',
                remark: '',
            },
            errors: [],
        };
    },
    mounted() {
        // handle agent data
        if (this.subid != 0) {
            this.data = {
                ...JSON.parse(document.getElementById('sub-data').textContent),
                id: this.subid,
            };
        }
    },
    methods: {
        send(e) {
            e.preventDefault();
            this.errors = [];
            let $url = this.subid == 0 ? '/api/accounts/sub_account/add' : '/api/accounts/sub_account/edit';
            axios.post($url, this.data)
            .then(res => {
                location.href = '/accounts/sub_account';
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
