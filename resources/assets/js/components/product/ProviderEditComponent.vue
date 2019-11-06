<template>
    <section class="container-fluid">
        <div class="card">
            <div class="card-body">
                <form @submit="send">
                    <b-form-group label-cols-md="1" :label="trans('common.name')" label-for="name">
                        <b-form-input id="name"
                            type="text"
                            v-model="data.name"
                            :state="handleErrors('name')"
                            required>
                        </b-form-input>
                        <b-form-invalid-feedback>
                            {{errors['name']}}
                        </b-form-invalid-feedback>
                    </b-form-group>
                    <b-form-group label-cols-md="1" :label="trans('providers.code')" label-for="code">
                        <b-form-input id="code"
                            type="text"
                            v-model="data.code"
                            :state="handleErrors('code')"
                            required
                            :disabled="providerid != 0">
                        </b-form-input>
                        <b-form-invalid-feedback>
                            {{errors['code']}}
                        </b-form-invalid-feedback>
                    </b-form-group>
                    <b-form-group label-cols-md="1" :label="trans('providers.maintenance_start')" label-for="maintenance_start">
                        <datetime format="YYYY-MM-DD H:i:s"
                            v-model="data.maintenance_start">
                        </datetime>
                        <b-form-invalid-feedback>
                            {{errors['maintenance_start']}}
                        </b-form-invalid-feedback>
                    </b-form-group>
                    <b-form-group label-cols-md="1" :label="trans('providers.maintenance_end')" label-for="maintenance_end">
                        <datetime format="YYYY-MM-DD H:i:s"
                            v-model="data.maintenance_end">
                        </datetime>
                        <b-form-invalid-feedback>
                            {{errors['maintenance_end']}}
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
    props: {
        providerid: {
            required: true,
        },
    },
    data() {
        return {
            data: {
                id: this.providerid,
                name: '',
                code: '',
                maintenance_start: '',
                maintenance_end: '',
                has_fun: '',
            },
            errors: {},
        };
    },
    mounted() {
        // handle data
        if (this.providerid != 0) {
            let provider = this.getJsonData('provider-data');
            this.data = {
                id: this.providerid,
                ...provider,
            };
        }
    },
    methods: {
        send(e) {
            e.preventDefault();
            this.errors = [];
            let url = '/api/products/provider/' + (this.providerid == 0 ? 'add' : 'edit');
            this.$ajax('POST', url, this.data)
            .then(res => {
                location.href = '/products/provider';
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
    }
}
</script>