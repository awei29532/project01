<template>
	<section>
		<list-grid :data="agentItems" :search="search">
			<template slot="search-form">
				<a class="btn btn-info mr-2 mb-1" href="/accounts/agent-edit/">{{trans('common.add')}}</a>
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
				<b-table striped :items="agentItems.data" :fields="fields" :busy="isLoading">
					<template slot="#" slot-scope="data">
						{{data.index + 1}}
					</template>
					<template slot="status" slot-scope="data">
						<button :class="'btn-pill btn-' + (data.item.status ? 'success' : 'danger')"
							@click="enableMsgDialog(data.item)">
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
				'#',
				{key: 'username', sortable: true, label: this.trans('common.username')},
				{key: 'name', sortable: true, label: this.trans('common.name')},
				{key: 'currency', label: this.trans('common.currency')},
				{key: 'products', label: this.trans('agents.products')},
				{key: 'members', sortable: true, label: this.trans('agents.members')},
				{key: 'remark', label: this.trans('common.remark')},
				{key: 'status', sortable: true, label: this.trans('common.status')},
				{key: 'updated_at', sortable: true, label: this.trans('common.updated_at')},
				{key: 'created_at', sortable: true, label: this.trans('common.created_at')},
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
			enabledAgentData: {},
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

			axios.post('/api/accounts/agent/list', this.searchData)
			.then(res => {
				this.agentItems = res.data;
				this.isLoading = false;
			})
			.catch(err => {
				console.error(err); 
			})
		},
		enabled(id, enabled) {
			axios.post('/api/accounts/agent/toggle-enabled', {
				id: id,
				enabled: enabled,
			})
			.then(res => {
				this.search();
			})
			.catch(err => {
				console.error(err); 
			})
		},
		enableMsgDialog(data) {
			let msg = this.trans('common.' + (data.status ? 'suspended' : 'active')) + ' ' + data.username; 
			this.$bvModal.msgBoxConfirm(msg, {
				okTitle: this.trans('common.ok'),
				cancelTitle: this.trans('common.cancel'),
			}).
			then(value => {
				if (value) {
					this.enabled(data.id, data.status ? 0 : 1);
				}
			}).catch(err => {
				console.warn(err);
			});
		},
	}
};
</script>
