<template>
    <section>
        <list-grid :data="subItems" :search="search">
            <template slot="search-form">
                <a v-if="user.isAdmin" class="btn btn-info mr-2 mb-1" href="/accounts/sub_account-edit/">{{trans('common.add')}}</a>
                <b-input-group class="mr-2 mb-1" :prepend="trans('common.status')">
                    <b-form-select v-model="searchData.status">
                        <option value="all">{{trans('common.all')}}</option>
                        <option value="1">{{trans('common.active')}}</option>
                        <option value="0">{{trans('common.suspended')}}</option>
                    </b-form-select>
			    </b-input-group>
            </template>
            <template slot="table">
                <b-table responsive bordered striped small
                    :items="subItems.data" :fields="fields" :busy="isLoading"
					show-empty :empty-text="trans('common.empty-data')">
					<loading slot="table-busy"></loading>
                    <template slot="#" slot-scope="data">
                        {{data.index + 1 + subItems.perPage * (subItems.page - 1)}}
                    </template>
                    <template slot="username" slot-scope="data">
                        <a :href="'/accounts/sub_account-edit/' + data.item.id">{{data.item.username}}</a>
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
            subItems: {
				data: [],
				page: 1,
				perPage: 15,
				total: 0,
				lastPage: 1,
            },
            fields: [
				{key: '#', thStyle: { width: '40px'}, class: 'text-center'},
                {key: 'username', sortable: true, label: this.trans('common.username'), thClass: 'text-center'},
                {key: 'name', sortable: true, label: this.trans('common.name'), thClass: 'text-center'},
                {key: 'status', label: this.trans('common.status'), class: 'table-col-status'},
                {key: 'remark', sortable: true, label: this.trans('common.remark'), thClass: 'text-center'},
                {key: 'updated_at', sortable: true, label: this.trans('common.updated_at'), class: 'table-col-time'},
                {key: 'created_at', sortable: true, label: this.trans('common.created_at'), class: 'table-col-time'},
            ],
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

			this.$ajax('POST', '/api/accounts/sub_account/list', this.searchData)
			.then(res => {
                this.subItems = res;
                this.isLoading = false;
			})
			.catch(err => {
				console.error(err); 
			})
        },
        addSub() {
			location.href = '/accounts/sub_account-edit/';
        },
		enabled(id, enabled) {
			this.isLoading = true;
			this.$ajax('POST', '/api/accounts/sub_account/toggle-enabled', {
				id: id,
				enabled: enabled,
			})
			.then(res => {
                this.subItems.data.filter(o => o.id == id)[0].status = enabled;
                this.isLoading = false;
			})
			.catch(err => {
				console.error(err); 
			})
		},
    }
}
</script>

