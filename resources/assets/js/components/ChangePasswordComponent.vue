<template>
<section class="container-fluid">
    <div class="card">
        <div class="card-body">
            <form @submit="submit">
                <b-form-group label-cols-md="1" :label="trans('change_password.old_password')" label-for="old-password">
                    <b-form-input id="old-password" autofocus
                        type="password" 
                        v-model="data.old_password" 
                        :state="handleErrors('old_password')" 
                        required
                    ></b-form-input>
                    <b-form-invalid-feedback>
                        {{ errors['old_password'] }}
                    </b-form-invalid-feedback>
                </b-form-group>
                <b-form-group label-cols-md="1" :label="trans('change_password.new_password')" label-for="new-password">
                    <b-form-input id="new-password" 
                        type="password" 
                        v-model="data.new_password" 
                        :state="handleErrors('new_password')" 
                        required
                    ></b-form-input>
                    <b-form-invalid-feedback>
                        {{ errors['new_password'] }}
                    </b-form-invalid-feedback>
                </b-form-group>
                <b-form-group label-cols-md="1" :label="trans('change_password.check_new_password')" label-for="new-password-confirm">
                    <b-form-input id="new-password-confirm" 
                        type="password" 
                        v-model="data.new_password_confirmation" 
                        :state="handleErrors('new_password_confirmation')" 
                        required
                    ></b-form-input>
                    <b-form-invalid-feedback>
                        {{ errors['new_password_confirmation'] }}
                    </b-form-invalid-feedback>
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
    data() {
        return {
            data: {
                old_password: '',
                new_password: '',
                new_password_confirmation: '',
            },
            errors: [],
        };
    },
    methods: {
        submit(e) {
            e.preventDefault();
            this.errors = [];
            axios.post('/api/change-password', this.data).then(res => {
                location.href = '/login';
            }).catch(err => {
                if (err.response.status == 422){
                    this.errors = err.response.data.errors;
                } else {
                    console.warn(err);
                }
            });
        },
        cancel() {
            history.back();
        },
        handleErrors(errorKey) {
            if (typeof this.errors[errorKey] === 'undefined') {
                return null;
            }
            return false;
        },
    },
}
</script>

