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
                    <b-form-group label-cols-md="1" :label="trans('games.game_id')" label-for="game_id">
                        <b-form-input id="game_id"
                            type="text"
                            v-model="data.game_id"
                            :state="handleErrors('game_id')"
                            required
                            :disabled="gameid != 0">
                        </b-form-input>
                        <b-form-invalid-feedback>
                            {{errors['game_id']}}
                        </b-form-invalid-feedback>
                    </b-form-group>
                    <b-form-group label-cols-md="1" :label="trans('games.provider')" label-for="provider">
                        <b-form-select id="provider"
                            v-model="data.provider_id"
                            :state="handleErrors('provider_id')"
                            required
                            :disabled="gameid != 0">
                                <option value="" disabled>{{trans('common.select')}}</option>
                                <option v-for="item in providerOptions" :key="item.id" :value="item.id">{{item.name}}</option>
                        </b-form-select>
                        <b-form-invalid-feedback>
                            {{errors['provider_id']}}
                        </b-form-invalid-feedback>
                    </b-form-group>
                    <b-form-group label-cols-md="1" :label="trans('games.has_fun_col')" label-for="has_fun">
                        <b-form-select id="has_fun"
                            v-model="data.has_fun"
                            :state="handleErrors('has_fun')"
                            required>
                                <option value="" disabled>{{trans('common.select')}}</option>
                                <option value="1">{{trans('games.has_fun')}}</option>
                                <option value="0">{{trans('games.not_has_fun')}}</option>
                        </b-form-select>
                        <b-form-invalid-feedback>
                            {{errors['provider_id']}}
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
        gameid: {
            required: true,
        },
    },
    data() {
        return {
            data: {
                id: this.gameid,
                provider_id: '',
                game_id: '',
                name: '',
                has_fun: '',
            },
            errors: {},
            providerOptions: this.getJsonData('providers'),
        };
    },
    mounted() {
        // handle data
        if (this.gameid != 0) {
            this.data = this.getJsonData('game-data');
        }
    },
    methods: {
        send(e) {
            e.preventDefault();
            this.errors = [];
            let url = '/api/products/game/' + (this.gameid == 0 ? 'add' : 'edit');
            this.$ajax('POST', url, this.data)
            .then(res => {
                location.href = '/products/game';
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