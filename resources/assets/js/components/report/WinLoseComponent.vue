<template>
<section>
    <list-grid :search="search" :paginate="false">
        <template slot="search-form">
            <b-input-group class="mr-2 mb-1" :prepend="trans('report.agent')" v-show="user.isAdmin || user.isAdminSub">
                <b-form-select value="" @change.native="agentOnChange($event)">
                    <option value="">{{trans('common.all')}}</option>
                    <option v-for="item in agentOptions" :key="item.id" :value="item.id">
                        {{ item.username }}
                    </option>
                </b-form-select>
            </b-input-group>
            <b-input-group class="mr-2 mb-1" :prepend="trans('report.group_by')">
                <b-form-select v-model="searchData.groupby">
                    <option value="agent">{{trans('report.agent')}}</option>
                    <option value="member">{{trans('report.member')}}</option>
                    <option value="day">{{trans('report.day')}}</option>
                    <option value="provider">{{trans('report.provider')}}</option>
                    <option value="game">{{trans('report.game')}}</option>
                </b-form-select>
            </b-input-group>
            <b-input-group class="mr-2 mb-1" :prepend="trans('common.started_at')">
                <b-form-input type="date" v-model="searchData.startedAt"></b-form-input>
            </b-input-group>
            <b-input-group class="mr-2 mb-1" :prepend="trans('common.finished_at')">
                <b-form-input type="date" v-model="searchData.finishedAt"></b-form-input>
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
                :items="winloseItems.data.rows" :fields="rows_fields" :busy="isLoading"
                show-empty :empty-text="trans('common.empty-data')">
                <loading slot="table-busy"></loading>
                <template slot="#" slot-scope="data">
                    {{ data.index + 1 }}
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
            </b-table>
        </template>
    </list-grid>

	<div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <b-table responsive bordered striped small
                    :items="winloseItems.data.footer" :fields="footer_fields" :busy="isLoading"
                    show-empty :empty-text="trans('common.empty-data')">
					<loading slot="table-busy"></loading>
                    <template slot="#" slot-scope="data">
                        {{ data.index + 1 }}
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
                </b-table>
            </div>
        </div>
    </div>
</section>
</template>
<script>

export default {
    data() {
        return {
            winloseItems: {
				data: [],
            },
            rows_fields: [
				{key: '#', thStyle: { width: '40px'}, class: 'text-center'},
                {key: 'name', sortable: true, label: this.trans('report.winlose_name'), thClass: 'text-center'},
                {key: 'currency', label: this.trans('common.currency'), class: 'table-col-cur'},
                {key: 'player', label: this.trans('report.player'), thClass: 'text-center', class: 'text-right', thStyle: {minWidth: '70px'}},
                {key: 'ticket', label: this.trans('report.tickets'), thClass: 'text-center', class: 'text-right'},
                {key: 'bet', label: this.trans('report.bet'), thClass: 'text-center', class: 'table-col-num'},
                {key: 'payout', label: this.trans('report.payout'), thClass: 'text-center', class: 'table-col-num'},
                {key: 'win', label: this.trans('report.win'), thClass: 'text-center', class: 'table-col-num'},
                {key: 'margin', label: this.trans('report.margin'), thClass: 'text-center', class: 'text-right'},
                {key: 'type', label: this.trans('report.type'), thClass: 'text-center'},
            ],
            footer_fields: [
				{key: '#', thStyle: { width: '40px'}, class: 'text-center'},
                {key: 'currency', label: this.trans('common.currency'), class: 'table-col-cur'},
                {key: 'player', label: this.trans('report.player'), thClass: 'text-center', class: 'text-right', thStyle: {minWidth: '70px'}},
                {key: 'ticket', label: this.trans('report.tickets'), thClass: 'text-center', class: 'text-right'},
                {key: 'bet', label: this.trans('report.bet'), thClass: 'text-center', class: 'table-col-num'},
                {key: 'payout', label: this.trans('report.payout'), thClass: 'text-center', class: 'table-col-num'},
                {key: 'win', label: this.trans('report.win'), thClass: 'text-center'},
                {key: 'margin', label: this.trans('report.margin'), thClass: 'text-center', class: 'text-right'},
                {key: 'type', label: this.trans('report.type'), thClass: 'text-center'},
            ],
            isLoading: false,
            searchData: {
                agent: [],
                groupby: 'agent',
                startedAt: '',
                finishedAt: '',
            },
            agentOptions: this.getJsonData('agent-list'),
        };
    },
    mounted() {
        this.agentOptions.forEach(o => {
            this.searchData.agent.push(o.id);
        });
        this.handleTime('today');
        this.searchData.startedAt = '2019-07-01';
        this.search();
    },
    methods: {
		search() {
			if (this.isLoading) {
				return;
			}
			this.isLoading = true;

			this.$ajax('POST', '/api/report/list/win_lose', this.searchData)
			.then(res => {
                this.winloseItems = res;
				this.isLoading = false;
			})
			.catch(err => {
				console.error(err); 
			})
        },
        agentOnChange(event) {
            this.searchData.agent = [];
            let id = event.target.value;
            if (id) {
                this.searchData.agent.push(id);
            } else {
                this.agentOptions.forEach(o => {
                    this.searchData.agent.push(o.id);
                });
            }
        },
        handleTime(type) {
            const range = this.getDatetimeRange(type, 'yyyy-MM-dd');
            this.searchData.startedAt  = range[0];
            this.searchData.finishedAt = range[1];
        }
    },
}
</script>