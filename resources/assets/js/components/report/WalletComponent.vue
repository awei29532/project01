<template>
<section>
    <list-grid :data="walletItems" :search="search">
        <template slot="search-form">
            <b-input-group class="mr-2 mb-1" :prepend="trans('report.agent')" v-show="user.isAdmin">
                <b-form-select value="" @change.native="agentOnChange($event.target.value)">
                    <option value="">{{trans('common.all')}}</option>
                    <option v-for="item in agentOptions" :key="item.id" :value="item.id">
                        {{ item.username }}
                    </option>
                </b-form-select>
            </b-input-group>
            <b-input-group class="mr-2 mb-1" :prepend="trans('report.provider')">
                <b-form-select v-model="providerId" @change.native="providerOnChange($event.target.value)">
                    <option value="">{{trans('common.all')}}</option>
                    <option v-for="item in providerOptions" :key="item.id" :value="item.id">
                        {{ item.name }}
                    </option>
                </b-form-select>
            </b-input-group>
            <b-input-group class="mr-2 mb-1" :prepend="trans('report.game')">
                <b-form-select v-model="searchData.game" :disabled="providerId == ''">
                    <option value="">{{trans('common.all')}}</option>
                    <option v-for="game in gameOptions" :key="game.id" :value="game.game_id">
                        {{ game.name }}
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
            <b-table responsive bordered striped small
                :items="walletItems.data" :fields="fields" :busy="isLoading"
                show-empty :empty-text="trans('common.empty-data')">
                <loading slot="table-busy"></loading>
                <template slot="#" slot-scope="data">
                    {{data.index + 1 + walletItems.perPage * (walletItems.page - 1)}}
                </template>
                <template slot="deposit" slot-scope="data">
                    <button class="btn btn-sm btn-info" @click="deposit(data.item.id)" :disabled="data.item.status == 1">{{trans('report.deposit')}}</button>
                </template>
                <template slot="amount" slot-scope="data">
                    {{ data.item.amount | numberFormat }}
                </template>
            </b-table>
        </template>
    </list-grid>
</section>
</template>
<script>
export default {
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
				{key: '#', thStyle: { width: '40px'}, class: 'text-center'},
                {key: 'provider', sortable: true, label: this.trans('report.provider'), thClass: 'text-center', class: 'table-col-other'},
                {key: 'agent', sortable: true, label: this.trans('report.agent'), thClass: 'text-center', class: 'table-col-other'},
                {key: 'account', sortable: true, label: this.trans('common.account'), thClass: 'text-center', class: 'table-col-other'},
                {key: 'game', sortable: true, label: this.trans('report.game'), thClass: 'text-center', class: 'table-col-other'},
                {key: 'amount', sortable: true, label: this.trans('report.amount'), thClass: 'text-center', class: 'table-col-num'},
                {key: 'datetime', sortable: true, label: this.trans('common.datetime'), class: 'table-col-time'},
                {key: 'deposit', label:this.trans('report.deposit'), class: 'text-center', thStyle: {minWidth: '80px'}},
            ],
            isLoading: false,
            searchData: {
                agents: [],
                providers: [],
                game: '',
                status: 1,
				page: 1,
				perPage: 25,
            },
            agentOptions: this.getJsonData('agent-list'),
            allGames: this.getJsonData('game-list'),
            gameOptions: [],
            providerOptions: this.getJsonData('provider-list'),
            providerId: '',
        };
    },
    mounted() {
        this.providerOptions.forEach(o => {
            this.searchData.providers.push(o.id);
        });
        this.agentOptions.forEach(o => {
            this.searchData.agents.push(o.id);
        });
        this.search();
    },
    methods: {
		search(page = 1, perPage = 25) {
			if (this.isLoading) {
				return;
			}
            this.isLoading = true;

			this.searchData.page = page;
			this.searchData.perPage = Number(perPage);

			this.$ajax('POST', '/api/report/list/wallet', this.searchData)
			.then(res => {
                this.walletItems = res;
				this.isLoading = false;
			})
			.catch(err => {
				console.error(err); 
			})
        },
        agentOnChange(id) {
            this.searchData.agents = [];
            if (id) {
                this.searchData.agents.push(id);
            } else {
                this.agentOptions.forEach(o => {
                    this.searchData.agents.push(o.id);
                });
            }
        },
        providerOnChange(id) {
            this.searchData.providers = [];
            if (id) {
                this.searchData.providers.push(id);
            } else {
                this.providerOptions.forEach(o => {
                    this.searchData.providers.push(o.id);
                });
            }

            const provider = this.allGames.filter(o => o.id == id);
            this.gameOptions = provider.length ? provider[0].games : [];
            this.searchData.game = '';
        },
        deposit(id) {
            this.$ajax('POST', '/api/report/wallet/deposit', {
                wallet_id: id,
            })
            .then(res => {
                console.log(res)
            })
            .catch(err => {
                console.error(err); 
            })
        }
    }
}
</script>