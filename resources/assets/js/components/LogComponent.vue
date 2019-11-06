<template>
<section>
    <list-grid :data="logItems" :search="search">
        <template slot="search-form">
            <b-input-group class="mr-2 mb-1" :prepend="trans('common.account')">
                <b-form-input v-model="searchData.account"></b-form-input>
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
        </template>
        <template slot="table">
            <b-table responsive bordered striped small
                :items="logItems.data" :fields="fields" :busy="isLoading"
                show-empty :empty-text="trans('common.empty-data')">
                <loading slot="table-busy"></loading>
                <template slot="#" slot-scope="data">
                    {{data.index + 1}}
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
			fields: [
				{key: '#', thStyle: { width: '40px'}, class: 'text-center'},
                {key: 'username', sortable: true, label: this.trans('common.username'), thClass: 'text-center', class: 'table-col-other'},
                {key: 'content', label: this.trans('log.content'), thClass: 'text-center', class: 'table-col-other'},
                {key: 'ip', sortable: true, label: 'IP', thClass: 'text-center', class: 'table-col-other'},
                {key: 'device', sortable: true, label: this.trans('log.device'), thClass: 'text-center', class: 'table-col-other'},
				{key: 'created_at', sortable: true, label: this.trans('common.created_at'), class: 'table-col-time'},
			],
			searchData: {
                account: '',
                startedAt: '',
                finishedAt: '',
				page: 1,
				perPage: 25,
            },
            isLoading: false,
            logItems: {},
        };
    },
    mounted() {
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

			this.$ajax('POST', '/api/log/list', this.searchData)
			.then(res => {
				this.logItems = res;
				this.isLoading = false;
			})
			.catch(err => {
				console.error(err); 
			})
        },
    }
}
</script>

<style>

</style>