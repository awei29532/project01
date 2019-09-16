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
            <b-input-group class="mr-2 mb-1" :prepend="trans('report.provider')">
                <b-form-select value="" @change.native="providerOnChange($event)">
                    <option value="" v-if="userid == 0">{{trans('common.all')}}</option>
                    <option v-for="item in providerOptions" :key="item.id" :value="item.id">
                        {{ item.name }}
                    </option>
                </b-form-select>
            </b-input-group>
            <b-input-group class="mr-2 mb-1" :prepend="trans('report.member')">
                <b-input type="text" v-model="searchData.account"></b-input>
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
                <template slot="status" slot-scope="data">
					<template slot="#" slot-scope="data">
						{{data.index + 1}}
					</template>
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
                {key: 'account', sortable: true, label: this.trans('common.account')},
                {key: 'provider', sortable: true, label: this.trans('report.provider')},
                {key: 'agent', sortable: true, label: this.trans('report.agent')},
                {key: 'type', sortable: true, label: this.trans('report.type')},
                {key: 'currency', sortable: true, label: this.trans('common.currency')},
                {key: 'amount', sortable: true, label: this.trans('report.amount')},
                {key: 'status', sortable: true, label: this.trans('common.status')},
                {key: 'datetime', sortable: true, label: this.trans('common.datetime')},
            ],
            isLoading: false,
            searchData: {
                providers: [],
                agents: [],
                account: '',
                startedAt: '',
                finishedAt: '',
				page: 1,
				perPage: 25,
            },
            agentOptions: [],
            providerOptions: [],
        };
    },
    mounted() {
        this.agentOptions = this.getJsonData('agent-list');
        this.agentOptions.forEach(o => {
            this.searchData.agents.push(o.id);
        });
        
        this.providerOptions = this.getJsonData('provider-list');
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

			axios.post('/api/report/all_report_sw/list', this.searchData)
			.then(res => {
                this.reportItems = res.data;
				this.isLoading = false;
			})
			.catch(err => {
				console.error(err); 
			})
        },
        agentOnChange(event) {
            this.searchData.agents = [];
            let id = event.target.value;
            if (id) {
                this.searchData.agents.push(id);
            } else {
                this.agentOptions.forEach(o => {
                    this.searchData.agents.push(o.id);
                });
            }
        },
        providerOnChange(event) {
            this.searchData.providers = [];
            let id = event.target.value;
            if (id) {
                this.searchData.providers.push(id);
            } else {
                this.providerOptions.forEach(o => {
                    this.searchData.providers.push(o.id);
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