<template>
    <div class="container">
        <div class="py-5"></div>
        <div class="card mx-auto p-3" style="max-width:500px">
            <div class="card-body">
                <h1>Login</h1>
                <p class="text-muted">Sign In to your account</p>
                <ul class="alert alert-danger" v-if="errors">
                    <li v-for="(value, key, index) in errors" :key="index">{{ value }}</li>
                </ul>
                <form @submit="submit">
                    <b-input-group class="mb-3">
                        <b-input-group-text slot="prepend" style="width:40px">
                            <i class="fas fa-user"></i>
                        </b-input-group-text>
                        <b-form-input type="text" autofocus
                            :placeholder="trans('common.username')"
                            v-model="username"
                            required>
                        </b-form-input>
                    </b-input-group>
                    <b-input-group class="mb-3">
                        <b-input-group-text slot="prepend" style="width:40px">
                            <i class="fas fa-key"></i>
                        </b-input-group-text>
                        <b-form-input type="password"
                            :placeholder="trans('common.password')"
                            v-model="password"
                            required>
                        </b-form-input>
                    </b-input-group>
                    <b-input-group class="mb-3">
                        <b-input-group-text slot="prepend" style="width:40px">
                            <i class="fas fa-lock"></i>
                        </b-input-group-text>
                        <b-form-input type="text"
                            :placeholder="trans('login.captcha')"
                            v-model="captcha"
                            required>
                        </b-form-input>
                        <img :src="captchaImg" @click="getCaptchaImg()">
                    </b-input-group>
                    <div class="row mb-2">
                        <div class="col-6">
                            <button class="btn btn-primary px-4" type="submit">
                                <i class="fas fa-sign-in-alt"></i> {{ trans('login.login') }}
                            </button>
                        </div>
                        <div class="col-6">
                            <b-form-select v-model="lang" @change="changeLang">
                                <option :value="null" disabled>{{ trans('login.lang') }}</option>
                                <option v-for="(item, idx) in langs" :key="idx" :value="item">{{ trans('common.lang.' + item) }}</option>
                            </b-form-select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
            username: '',
            password: '',
            captcha: '',
            errors: '',
            lang: null,
            langs: window.langs,
            captchaImg: document.getElementById('captcha').textContent,
        };
    },
    methods: {
        submit(evt) {
            evt.preventDefault();
            this.errors = '';
            axios.post('/api/login', {
                username: this.username,
                password: this.password,
                captcha: this.captcha,
            }).then(res => {
                location.href = res.data.redirect;
            }).catch(err => {
                if (err.response.status == 422){
                    this.errors = err.response.data.errors;
                } else {
                    console.warn(err);
                }
            });
        },
        getCaptchaImg() {
            axios.get('/login-captcha-img')
            .then(res => {
                this.captchaImg = res.data;
            })
            .catch(err => {
                console.error(err); 
            })
        },
        changeLang() {
            axios.get('lang', {
                params: { lang: this.lang }
            })
            .then(res => {
                location.reload();
            })
            .catch(err => {
                console.error(err); 
            });
        }
    },
}
</script>

