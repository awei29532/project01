<template>
<section>
    <list-grid :search="search" :paginate="false">
        <template slot="search-form">
            <b-input-group class="mr-2 mb-1" :prepend="trans('report.agent')">
                <b-form-select value="" @change.native="agentOnChange($event)">
                    <option value="" v-if="userid == 0">{{trans('common.all')}}</option>
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
                <button class="btn btn-outline-primary mr-2" @click="handleTime('week')">{{trans('common.this_week')}}</button>
                <button class="btn btn-outline-primary" @click="handleTime('month')">{{trans('common.this_month')}}</button>
            </b-input-group>
        </template>
        <template slot="table">
            <b-table striped :items="winloseItems.data.rows" :fields="rows_fields" :busy="isLoading">
                <template slot="#" slot-scope="data">
                    {{ data.index + 1}}
                </template>
            </b-table>
        </template>
    </list-grid>

	<div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <b-table striped :items="winloseItems.data.footer" :fields="footer_fields" :busy="isLoading">
                    <template slot="#" slot-scope="data">
                        {{ data.index + 1}}
                    </template>
                </b-table>
            </div>
        </div>
    </div>
</section>
</template>
<script>
const luxon = require('luxon');

export default {
    props: ['userid'],
    data() {
        return {
            winloseItems: {
				data: [],
            },
            rows_fields: [
                '#',
                {key: 'name', sortable: true, label: this.trans('report.winlose_name')},
                {key: 'currency', label: this.trans('common.currency')},
                {key: 'player', label: this.trans('report.player')},
                {key: 'ticket', label: this.trans('report.tickets')},
                {key: 'bet', label: this.trans('report.bet')},
                {key: 'payout', label: this.trans('report.payout')},
                {key: 'win', label: this.trans('report.win')},
                {key: 'margin', label: this.trans('report.margin')},
                {key: 'type', label: this.trans('report.type')},
            ],
            footer_fields: [
                {key: ' ', sortable: true, label: ''},
                {key: 'currency', label: this.trans('common.currency')},
                {key: 'player', label: this.trans('report.player')},
                {key: 'ticket', label: this.trans('report.tickets')},
                {key: 'bet', label: this.trans('report.bet')},
                {key: 'payout', label: this.trans('report.payout')},
                {key: 'win', label: this.trans('report.win')},
                {key: 'margin', label: this.trans('report.margin')},
                {key: 'type', label: this.trans('report.type')},
            ],
            isLoading: false,
            searchData: {
                agent: [],
                groupby: 'agent',
                startedAt: '',
                finishedAt: '',
            },
            agentOptions: [],
        };
    },
    mounted() {
        this.agentOptions = JSON.parse(document.getElementById('agent-list').textContent);
        this.agentOptions.forEach(o => {
            this.searchData.agent.push(o.id);
        });
    },
    methods: {
		search() {
			if (this.isLoading) {
				return;
			}
			this.isLoading = true;

			axios.post('/api/report/win_lose/list', this.searchData)
			.then(res => {
                this.winloseItems = res.data;
                console.log(res.data);
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
            console.log(type);
            let today = luxon.DateTime.local();
            let start;
            let end;
            switch (type) {
                case 'today':
                    start = today.startOf('day');
                    end   = today.endOf('day');
                    break;
                case 'yesterday':
                    today = today.plus({ days: -1});
                    start = today.startOf('day');
                    end   = today.endOf('day');
                    break;
                case 'week':
                    today = today.endOf('week').plus({ days: -1});
                    end   = today.endOf('day');
                    today = today.startOf('week').plus({ days: -1});
                    start = today.startOf('day');
                    break;
                case 'month':
                    today = today.endOf('month');
                    end   = today.endOf('day');
                    today = today.startOf('month');
                    start = today.startOf('day');
                    break;
            }
            this.searchData.startedAt = start.toFormat('yyyy-MM-dd');
            this.searchData.finishedAt = end.toFormat('yyyy-MM-dd');
        }
    }
}
</script>