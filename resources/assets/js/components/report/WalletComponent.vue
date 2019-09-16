<template>
<section>
    <list-grid :data="walletItems" :search="search">
        <template slot="search-form">
            <b-input-group class="mr-2 mb-1" :prepend="trans('report.provider')">
                <b-form-select value="" @change.native="providerOnChange($event)">
                    <option value="">{{trans('common.all')}}</option>
                    <option v-for="item in providerOptions" :key="item.id" :value="item.id">
                        {{ item.name }}
                    </option>
                </b-form-select>
            </b-input-group>
            <b-input-group class="mr-2 mb-1" :prepend="trans('report.game')">
                <b-form-select @change.native="gameOnChange($event)">
                    <option value="">{{trans('common.all')}}</option>
                    <option v-for="item in gameOptions" :key="item.id" :value="item.id">
                        {{ item.name }}
                    </option>
                </b-form-select>
            </b-input-group>
            <b-input-group class="mr-2 mb-1" :prepend="trans('common.status')">
                <b-form-select v-model="searchData.status">
                    <option value="1">{{trans('common.active')}}</option>
                    <option value="0">{{trans('common.suspended')}}</option>
                </b-form-select>
            </b-input-group>
        </template>
        <template slot="table">
            <b-table striped :items="walletItems.data" :fields="fields" :busy="isLoading">
                <template slot="#" slot-scope="data">
                    {{data.index + 1}}
                </template>
                <template slot="deposit" slot-scope="data">
                    <button class="btn btn-info" @click="deposit(data.item)">{{trans('report.deposit')}}</button>
                </template>
                <template slot="status" slot-scope="data">
                    <span :class="'badge badge-' + (data.item.status == 1 ? 'success' : 'danger')">
                        {{ trans('common.' + (data.item.status == 1 ? 'active' : 'suspended')) }}
                    </span>
                </template>
            </b-table>
        </template>
    </list-grid>
</section>
</template>
<script>
export default {
    props: ['userid'],
    data() {
        return {
            walletItems: {
				data: [],
				page: 1,
				perPage: 15,
				total: 0,
				lastPage: 1,
            },
            fields: [
                '#',
                {key: 'provider', sortable: true, label: this.trans('report.provider')},
                {key: 'agent', sortable: true, label: this.trans('report.agent')},
                {key: 'account', sortable: true, label: this.trans('common.account')},
                {key: 'game', sortable: true, label: this.trans('report.game')},
                {key: 'amount', sortable: true, label: this.trans('report.amount')},
                {key: 'status', sortable: true, label: this.trans('common.status')},
                {key: 'datetime', sortable: true, label: this.trans('common.datetime')},
                {key: 'deposit', label:this.trans('report.deposit')},
            ],
            isLoading: false,
            searchData: {
                providers: [],
                games: [],
                status: 1,
				page: 1,
				perPage: 25,
            },
            gameOptions: [],
            providerOptions: [],
        };
    },
    mounted() {
        this.gameOptions = JSON.parse(document.getElementById('game-list').textContent);
        this.gameOptions.forEach(o => {
            this.searchData.games.push(o.id);
        });

        this.providerOptions = JSON.parse(document.getElementById('provider-list').textContent);
        this.providerOptions.forEach(o => {
            this.searchData.providers.push(o.id);
        });
    },
    methods: {
		search(page = 1, perPage = 25) {
			if (this.isLoading) {
				return;
			}
            this.isLoading = true;

			this.searchData.page = page;
			this.searchData.perPage = Number(perPage);

			axios.post('/api/report/wallet/list', this.searchData)
			.then(res => {
                this.walletItems = res.data;
				this.isLoading = false;
			})
			.catch(err => {
				console.error(err); 
			})
        },
        gameOnChange(event) {
            this.searchData.games = [];
            let id = event.target.value;
            if (id) {
                this.searchData.games.push(id);
            } else {
                this.agentOptions.forEach(o => {
                    this.searchData.games.push(o.id);
                });
            }
        },
        providerOnChange(event) {
            this.searchData.providers = [];
            let id = event.target.value;
            if (id) {
                this.searchData.providers.push(id);
            } else {
                this.agentOptions.forEach(o => {
                    this.searchData.providers.push(o.id);
                });
            }
        },
        deposit(data) {
            console.log(data);
        }
    }
}
</script>