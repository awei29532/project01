<template>
<section>
    <list-grid :data="reportItems" :search="search">
        <template slot="search-form">
            <b-input-group class="mr-2 mb-1" :prepend="trans('report.agent')">
                <b-form-select value="" @change.native="agentOnChange($event)">
                    <option value="" v-if="userid == 0">{{trans('common.all')}}</option>
                    <option v-for="item in agentOptions" :key="item.id" :value="item.id">
                        {{ item.username }}
                    </option>
                </b-form-select>
            </b-input-group>
            <b-input-group class="mr-2 mb-1" :prepend="trans('report.member')">
                <b-input type="text" v-model="searchData.member"></b-input>
            </b-input-group>
            <b-input-group class="mr-2 mb-1" :prepend="trans('common.started_at')">
                <datetime format="YYYY-MM-DD H:i"
                    v-model="searchData.startedAt">
                </datetime>
            </b-input-group>
            <b-input-group class="mr-2 mb-1" :prepend="trans('common.finished_at')">
                <datetime format="YYYY-MM-DD H:i"
                    v-model="searchData.finishedAt">
                </datetime>
            </b-input-group>
            <b-input-group class="mr-2 mb-1">
                <button class="btn btn-outline-primary mr-2" @click="handleTime('today')">{{trans('common.today')}}</button>
                <button class="btn btn-outline-primary mr-2" @click="handleTime('yesterday')">{{trans('common.yesterday')}}</button>
                <button class="btn btn-outline-primary mr-2" @click="handleTime('week')">{{trans('common.this_week')}}</button>
                <button class="btn btn-outline-primary" @click="handleTime('month')">{{trans('common.this_month')}}</button>
            </b-input-group>
        </template>
        <template slot="table">
            <b-table striped :items="reportItems.data" :fields="fields" :busy="isLoading">
                <template slot="#" slot-scope="data">
                    {{data.index + 1}}
                </template>
            </b-table>
        </template>
    </list-grid>
</section>
</template>
<script>
const luxon = require('luxon');

export default {
    props: ['userid'],
    data() {
        return {
            reportItems: {
				data: [],
				page: 1,
				perPage: 25,
				total: 0,
				lastPage: 1,
            },
            fields: [
                '#',
                {key: 'ref_id', sortable: true, label: this.trans('report.ref_id')},
                {key: 'provider', label: this.trans('report.provider')},
                {key: 'agent', label: this.trans('report.agent')},
                {key: 'username', sortable: true, label: this.trans('common.username')},
                {key: 'currency', sortable: true, label: this.trans('common.currency')},
                {key: 'credit', sortable: true, label: this.trans('report.credit')},
                {key: 'debit', sortable: true, label: this.trans('report.debit')},
                {key: 'end_balance', sortable: true, label: this.trans('report.end_balance')},
                {key: 'datetime', sortable: true, label: this.trans('common.datetime')},
            ],
            isLoading: false,
            searchData: {
                agent: [],
                member: '',
                startedAt: '',
                finishedAt: '',
				page: 1,
				perPage: 25,
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
		search(page = 1, perPage = 25) {
			if (this.isLoading) {
				return;
			}
			this.isLoading = true;

			this.searchData.page = page;
            this.searchData.perPage = Number(perPage);

			axios.post('/api/report/all_report/list', this.searchData)
			.then(res => {
                this.reportItems = res.data;
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
            this.searchData.startedAt = start.toSQL();
            this.searchData.finishedAt = end.toSQL();
        }
    }
}
</script>