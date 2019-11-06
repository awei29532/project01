<template>
<section>
    <list-grid :data="memberItems" :search="search">
        <template slot="search-form">
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
			<b-input-group class="mr-2 mb-1" :prepend="trans('members.agent')">
				<b-form-input type="text" v-model="searchData.agent"></b-form-input>
			</b-input-group>
        </template>
        <template slot="table">
            <b-table responsive bordered striped small
				:items="memberItems.data" :fields="fields" :busy="isLoading"
				show-empty :empty-text="trans('common.empty-data')">
				<loading slot="table-busy"></loading>
				<template slot="#" slot-scope="data">
					{{data.index + 1 + memberItems.perPage * (memberItems.page - 1)}}
				</template>
                <template slot="status" slot-scope="data">
					<button :class="'btn-pill btn-sm btn-' + (data.item.status ? 'success' : 'danger')"
						@click="enabledDialog(data.item.id, data.item.username, data.item.status, enabled)">
							{{ trans('common.' + (data.item.status ? 'active' : 'suspended')) }}
					</button>
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
                {key: 'username', sortable: true, label: this.trans('common.username'), thClass: 'text-center'},
                {key: 'agent', sortable: true, label: this.trans('members.agent'), thClass: 'text-center', thStyle: {minWidth: '125px'}},
                {key: 'currency', sortable: true, label: this.trans('common.currency'), class: 'table-col-cur'},
                {key: 'balance', sortable: true, label: this.trans('members.balance'), thClass: 'text-center', class: 'text-right'},
                {key: 'status', sortable: true, label: this.trans('common.status'), class: 'table-col-status'},
                {key: 'updated_at', sortable: true, label: this.trans('common.updated_at'), class: 'table-col-time'},
                {key: 'created_at', sortable: true, label: this.trans('common.created_at'), class: 'table-col-time'},
            ],
            memberItems: {
				data: [],
				page: 1,
				perPage: 15,
				total: 0,
				lastPage: 1,
			},
            isLoading: false,
            searchData: {
                account: '',
                status: 'all',
                page: 1,
				perPage: 25,
            },
			isLoading: false,
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

			this.$ajax('POST', '/api/accounts/member/list', this.searchData)
			.then(res => {
				this.memberItems = res;
				this.isLoading = false;
			})
			.catch(err => {
				console.error(err); 
			})
		},
		enabled(id, enabled) {
			this.isLoading = true;
			this.$ajax('POST', '/api/accounts/member/toggle-enabled', {
				id: id,
				enabled: enabled,
			})
			.then(res => {
				this.memberItems.data.filter(o => o.id == id)[0].status = enabled;
				this.isLoading = false;
			})
			.catch(err => {
				console.error(err); 
			})
		},
    }
}
</script>
