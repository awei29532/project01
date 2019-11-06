<template>
<section>
    <list-grid :data="betItems" :search="search">
        <template slot="search-form">
            <b-input-group class="mr-2 mb-1" :prepend="trans('report.agent')" v-show="user.isAdmin || user.isAdminSub">
                <b-form-select value="" @change.native="agentOnChange($event.target.value)">
                    <option value="">{{trans('common.all')}}</option>
                    <option v-for="item in agentOptions" :key="item.id" :value="item.id">
                        {{ item.username }}
                    </option>
                </b-form-select>
            </b-input-group>
            <b-input-group class="mr-2 mb-1" :prepend="trans('report.game')">
                <b-form-select v-model="searchData.game">
                    <option value="">{{trans('common.all')}}</option>
                    <optgroup v-for="provider in gameOptions" :key="provider.id" :label="provider.name" v-show="provider.games.length">
                        <option v-for="game in provider.games" :key="game.id" :value="{providerId: provider.id, gameId: game.game_id}">
                            {{ game.name }}
                        </option>
                    </optgroup>
                </b-form-select>
            </b-input-group>
            <b-input-group class="mr-2 mb-1" :prepend="trans('report.member')">
                <b-input type="text" v-model="searchData.member"></b-input>
            </b-input-group>
            <b-input-group class="mr-2 mb-1" :prepend="trans('common.started_at')">
                <datetime format="YYYY-MM-DD H:i:s"
                    v-model="searchData.startedAt">
                </datetime>
            </b-input-group>
            <b-input-group class="mr-2 mb-1" :prepend="trans('common.finished_at')">
                <datetime format="YYYY-MM-DD H:i:s"
                    v-model="searchData.finishedAt">
                </datetime>
            </b-input-group>
            <b-input-group class="mr-2 mb-1">
                <button class="btn btn-outline-primary mr-2" @click="handleTime('today')">{{trans('common.today')}}</button>
                <button class="btn btn-outline-primary mr-2" @click="handleTime('yesterday')">{{trans('common.yesterday')}}</button>
                <button class="btn btn-outline-primary mr-2" @click="handleTime('this_week')">{{trans('common.this_week')}}</button>
                <button class="btn btn-outline-primary" @click="handleTime('this_month')">{{trans('common.this_month')}}</button>
            </b-input-group>
        </template>
        <template slot="table">
            <b-table responsive bordered striped small
                :items="betItems.data" :fields="fields" :busy="isLoading"
                show-empty :empty-text="trans('common.empty-data')">
                <loading slot="table-busy"></loading>
                <template slot="#" slot-scope="data">
                    {{data.index + 1 + betItems.perPage * (betItems.page - 1)}}
                </template>
                <template slot="bet" slot-scope="data">
                    {{ data.item.bet | numberFormat }}
                </template>
                <template slot="payout" slot-scope="data">
                    {{ data.item.payout | numberFormat }}
                </template>
                <template slot="win" slot-scope="data">
                    <section :class="'table-col-' + (data.item.win >= 0 ? 'greenNum' : 'redNum')">
                        {{ data.item.win | numberFormat }}
                    </section>
                </template>
                <template slot="options" slot-scope="data">
                    <button type="button" class="btn btn-sm btn-info" @click="showDetail(data.item)">{{trans('report.detail')}}</button>
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
            betItems: {
				data: [],
				page: 1,
				perPage: 25,
				total: 0,
				lastPage: 1,
            },
            fields: [
				{key: '#', thStyle: { width: '40px'}, class: 'text-center'},
                {key: 'ticket_id', sortable: true, label: this.trans('report.ticket_id'), thClass: 'text-center', thStyle: {minWidth: '85px'}},
                {key: 'agent', label: this.trans('report.agent'), thClass: 'text-center'},
                {key: 'username', label: this.trans('common.username'), thClass: 'text-center'},
                {key: 'currency', label: this.trans('common.currency'), class: 'table-col-cur'},
                {key: 'game', label: this.trans('report.game'), thClass: 'text-center'},
                {key: 'bet', label: this.trans('report.bet'), thClass: 'text-center', thStyle: {minWidth: '70px'}, class: 'table-col-num'},
                {key: 'payout', label: this.trans('report.payout'), thClass: 'text-center', thStyle: {minWidth: '70px'}, class: 'table-col-num'},
                {key: 'win', label: this.trans('report.win'), thClass: 'text-center', thStyle: {minWidth: '70px'}},
                {key: 'datetime', label: this.trans('common.datetime'), class: 'table-col-time'},
                {key: 'options', label: this.trans('common.options'), class: 'text-center', thStyle: {minWidth: '80px'}},
            ],
            isLoading: false,
            searchData: {
                agent: [],
                game: '',
                groupby: 'agent',
                startedAt: '',
                finishedAt: '',
				page: 1,
				perPage: 25,
            },
            agentOptions: this.getJsonData('agent-list'),
            gameOptions: this.getJsonData('game-list'),
        };
    },
    mounted() {
        this.agentOptions.forEach(o => {
            this.searchData.agent.push(o.id);
        });
        this.handleTime('today');
        this.searchData.startedAt = '2019-07-01 00:00:00';
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

			this.$ajax('POST', '/api/report/list/bet_history', this.searchData)
			.then(res => {
                this.betItems = res;
				this.isLoading = false;
			})
			.catch(err => {
				console.error(err); 
			})
        },
        agentOnChange(id) {
            this.searchData.agent = [];
            if (id) {
                this.searchData.agent.push(id);
            } else {
                this.agentOptions.forEach(o => {
                    this.searchData.agent.push(o.id);
                });
            }
        },
        showDetail(data) {
            this.$ajax('POST', '/api/report/bet_history/detail', { ticket_id: data.ticket_id })
            .then(res => {
                window.open(res);
            })
            .catch(err => {
                console.error(err); 
            })
        },
        handleTime(type) {
            const range = this.getDatetimeRange(type);
            this.searchData.startedAt  = range[0];
            this.searchData.finishedAt = range[1];
        }
    }
}
</script>