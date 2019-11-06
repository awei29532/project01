<template>
<section>
    <list-grid :data="reportItems" :search="search">
        <template slot="search-form">
            <b-input-group class="mr-2 mb-1" :prepend="trans('report.agent')" v-show="user.isAdmin || user.isAdminSub">
                <b-form-select value="" @change.native="agentOnChange($event)">
                    <option value="">{{trans('common.all')}}</option>
                    <option v-for="item in agentOptions" :key="item.id" :value="item.id">
                        {{ item.username }}
                    </option>
                </b-form-select>
            </b-input-group>
            <b-input-group class="mr-2 mb-1" :prepend="trans('report.provider')">
                <b-form-select value="" @change.native="providerOnChange($event)">
                    <option value="">{{trans('common.all')}}</option>
                    <option v-for="item in providerOptions" :key="item.id" :value="item.id">
                        {{ item.name }}
                    </option>
                </b-form-select>
            </b-input-group>
            <b-input-group class="mr-2 mb-1" :prepend="trans('report.member')">
                <b-input type="text" v-model="searchData.account"></b-input>
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
                :items="reportItems.data" :fields="fields" :busy="isLoading"
                show-empty :empty-text="trans('common.empty-data')">
                <loading slot="table-busy"></loading>
                <template slot="#" slot-scope="data">
                    {{data.index + 1 + reportItems.perPage * (reportItems.page - 1)}}
                </template>
                <template slot="status" slot-scope="data">
                    <span :class="'badge badge-' + (data.item.status == 1 ? 'success' : 'danger')">
                        {{ trans('common.' + (data.item.status == 1 ? 'active' : 'suspended')) }}
                    </span>
                </template>
                <template slot="amount" slot-scope="data">
                    <section :class="Number(data.item.amount) < 0 ? 'table-col-redNum' : 'table-col-greenNum'">
                        {{ data.item.amount }}
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
            reportItems: {
				data: [],
				page: 1,
				perPage: 25,
				total: 0,
				lastPage: 1,
            },
            fields: [
				{key: '#', thStyle: { width: '40px'}, class: 'text-center'},
                {key: 'ref_id', sortable: true, label: this.trans('report.ref_id'), thClass: 'text-center', class: 'table-col-other'},
                {key: 'account', sortable: true, label: this.trans('common.account'), thClass: 'text-center'},
                {key: 'provider', sortable: true, label: this.trans('report.provider'), thClass: 'text-center'},
                {key: 'agent', sortable: true, label: this.trans('report.agent'), thClass: 'text-center'},
                {key: 'type', sortable: true, label: this.trans('report.type'), thClass: 'text-center'},
                {key: 'currency', sortable: true, label: this.trans('common.currency'), class: 'table-col-cur'},
                {key: 'amount', sortable: true, label: this.trans('report.amount'), thClass: 'text-center'},
                {key: 'datetime', sortable: true, label: this.trans('common.datetime'), class: 'table-col-time'},
                {key: 'options', label: this.trans('common.options'), class: 'text-center', thStyle: {minWidth: '80px'}},
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
            agentOptions: this.getJsonData('agent-list'),
            providerOptions: this.getJsonData('provider-list'),
        };
    },
    mounted() {
        this.agentOptions.forEach(o => {
            this.searchData.agents.push(o.id);
        });
        this.providerOptions.forEach(o => {
            this.searchData.providers.push(o.id);
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

			this.$ajax('POST', '/api/report/list/all_report_sw', this.searchData)
			.then(res => {
                this.reportItems = res;
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
            const range = this.getDatetimeRange(type);
            this.searchData.startedAt  = range[0];
            this.searchData.finishedAt = range[1];
        },
        showDetail(data) {
            // this.$ajax('POST', '/api/report/bet_history/detail', { ticket_id: data.ticket_id })
            // .then(res => {
            //     window.open(res);
            // })
            // .catch(err => {
            //     console.error(err); 
            // })
        },
    }
}
</script>