<template>
<section>
    <list-grid :data="transferItems" :search="search">
        <template slot="search-form">
            <b-input-group class="mr-2 mb-1" :prepend="trans('report.agent')" v-show="user.isAdmin || user.isAdminSub">
                <b-form-select value="" @change.native="agentOnChange($event)">
                    <option value="">{{trans('common.all')}}</option>
                    <option v-for="item in agentOptions" :key="item.id" :value="item.id">
                        {{ item.username }}
                    </option>
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
                :items="transferItems.data" :fields="fields" :busy="isLoading"
                show-empty :empty-text="trans('common.empty-data')">
                <loading slot="table-busy"></loading>
                <template slot="#" slot-scope="data">
                    {{data.index + 1 + transferItems.perPage * (transferItems.page - 1)}}
                </template>
                <template slot="amount" slot-scope="data">
                    <section :class="'table-col-' + (data.item.amount >= 0 ? 'greenNum' : 'redNum')">
                        {{ data.item.amount | numberFormat }}
                    </section>
                </template>
                <template slot="balance" slot-scope="data">
                    {{ data.item.balance | numberFormat }}
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
            transferItems: {
				data: [],
				page: 1,
				perPage: 25,
				total: 0,
				lastPage: 1,
            },
            fields: [
				{key: '#', thStyle: { width: '40px'}, class: 'text-center'},
                {key: 'ref_id', sortable: true, label: this.trans('report.ref_id'), thClass: 'text-center', class: 'table-col-other'},
                {key: 'agent', label: this.trans('report.agent'), thClass: 'text-center', class: 'table-col-other'},
                {key: 'username', label: this.trans('common.username'), thClass: 'text-center', class: 'table-col-other'},
                {key: 'currency', label: this.trans('common.currency'), class: 'table-col-cur'},
                {key: 'amount', sortable: true, label: this.trans('report.amount'), thClass: 'text-center'},
                {key: 'balance', label: this.trans('report.balance'), thClass: 'text-center', class: 'table-col-num'},
                {key: 'datetime', label: this.trans('common.datetime'), class: 'table-col-time'},
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
            agentOptions: this.getJsonData('agent-list'),
        };
    },
    mounted() {
        this.agentOptions.forEach(o => {
            this.searchData.agent.push(o.id);
        });
        this.handleTime('today');
        this.searchData.startedAt = '2019-07-01 00:00:00'
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

			this.$ajax('POST', '/api/report/list/transfer', this.searchData)
			.then(res => {
                this.transferItems = res;
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
            const range = this.getDatetimeRange(type);
            this.searchData.startedAt  = range[0];
            this.searchData.finishedAt = range[1];
        }
    }
}
</script>