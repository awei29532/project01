<template>
	<section>
		<list-grid :data="agentItems" :search="search">
			<template slot="search-form">
				<a v-if="user.isAdmin" class="btn btn-info mr-2 mb-1" href="/accounts/agent-edit/">{{trans('common.add')}}</a>
				<b-input-group class="mr-2 mb-1" :prepend="trans('common.status')">
					<b-form-select v-model="searchData.status">
						<option value="all">{{trans('common.all')}}</option>
						<option value="1">{{trans('common.active')}}</option>
						<option value="0">{{trans('common.suspended')}}</option>
					</b-form-select>
				</b-input-group>
				<b-input-group class="mr-2 mb-1" :prepend="trans('common.account')">
					<b-form-input type="text" v-model="searchData.account"></b-form-input>
				</b-input-group>
			</template>

			<template slot="table">
				<b-table responsive bordered striped small
					:items="agentItems.data" :fields="fields" :busy="isLoading"
					show-empty :empty-text="trans('common.empty-data')">
					<loading slot="table-busy"></loading>
					<template slot="#" slot-scope="data">
						{{data.index + 1 + agentItems.perPage * (agentItems.page - 1)}}
					</template>
					<template slot="status" slot-scope="data">
						<button :class="'btn-pill btn-sm btn-' + (data.item.status ? 'success' : 'danger')"
							@click="enabledDialog(data.item.id, data.item.username, data.item.status, enabled)">
								{{ trans('common.' + (data.item.status ? 'active' : 'suspended')) }}
						</button>
					</template>
					<template slot="username" slot-scope="data">
						<a :href="'/accounts/agent-edit/' + data.item.id">{{data.item.username}}</a>
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
				{key: 'name', sortable: true, label: this.trans('common.name'), thClass: 'text-center', class: 'table-col-other'},
				{key: 'currency', label: this.trans('common.currency'), class: 'table-col-cur'},
				{key: 'products', label: this.trans('agents.products'), thClass: 'text-center', class: 'table-col-other'},
				{key: 'members', sortable: true, label: this.trans('agents.members'), class: 'text-right', thClass: 'text-center', thStyle: { minWidth: '70px'}},
				{key: 'remark', label: this.trans('common.remark'), thClass: 'text-center', class: 'table-col-other'},
				{key: 'status', sortable: true, label: this.trans('common.status'), class: 'table-col-status'},
				{key: 'updated_at', sortable: true, label: this.trans('common.updated_at'), class: 'table-col-time'},
				{key: 'created_at', sortable: true, label: this.trans('common.created_at'), class: 'table-col-time'},
			],
			agentItems: {
				data: [],
				page: 1,
				perPage: 15,
				total: 0,
				lastPage: 1,
			},
			searchData: {
				account: '',
				status: 'all',
				page: 1,
				perPage: 25,
			},
			isLoading: false,
		}
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

			this.$ajax('POST', '/api/accounts/agent/list', this.searchData)
			.then(res => {
				this.agentItems = res;
				this.isLoading = false;
			})
			.catch(err => {
				console.error(err); 
			})
		},
		enabled(id, enabled) {
			this.isLoading = true;
			this.$ajax('POST', '/api/accounts/agent/toggle-enabled', {
				id: id,
				enabled: enabled,
			})
			.then(res => {
				this.agentItems.data.filter(o => o.id == id)[0].status = enabled;
				this.isLoading = false;
			})
			.catch(err => {
				console.error(err); 
			})
		},
	},
};
</script>
