<template>
    <section>
        <list-grid :data="providerItems" :search="search">
            <template slot="search-form">
				<a v-if="user.isAdmin" class="btn btn-info mr-2 mb-1" href="/products/provider-edit/">{{trans('common.add')}}</a>
                <b-input-group class="mr-2 mb-1" :prepend="trans('common.name')">
                    <b-input v-model="searchData.name"></b-input>
                </b-input-group>
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
					:items="providerItems.data" :busy="isLoading" :fields="fields"
					show-empty :empty-text="trans('common.empty-data')">
					<loading slot="table-busy"></loading>
                    <template slot="#" slot-scope="data">
                        {{data.index + 1 + providerItems.perPage * (providerItems.page - 1)}}
                    </template>
                    <template slot="name" slot-scope="data">
                        <a :href="'/products/provider-edit/' + data.item.id">{{data.item.name}}</a>
                    </template>
                    <template slot="status" slot-scope="data">
                        <button :class="'btn-pill btn-sm btn-' + (data.item.status ? 'success' : 'danger')"
							@click="enabledDialog(data.item.id, data.item.name, data.item.status, enabled)">
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
            providerItems: {},
			fields: [
				{key: '#', thStyle: { width: '40px'}, class: 'text-center'},
				{key: 'name', sortable: true, label: this.trans('common.name'), thClass: 'text-center', thStyle: {minWidth: '55px'}},
				{key: 'code', sortable: true, label: this.trans('providers.code'), thClass: 'text-center', thStyle: {minWidth: '60px'}},
                {key: 'status', sortable: true, label: this.trans('common.status'), class: 'table-col-status'},
                {key: 'maintenance_start', sortable: true, label: this.trans('providers.maintenance_start'), class: 'table-col-time'},
                {key: 'maintenance_end', sortable: true, label: this.trans('providers.maintenance_end'), class: 'table-col-time'},
				{key: 'updated_at', sortable: true, label: this.trans('common.updated_at'), class: 'table-col-time'},
				{key: 'created_at', sortable: true, label: this.trans('common.created_at'), class: 'table-col-time'},
			],
            isLoading: false,
            searchData: {
                name: '',
                status: 'all',
            },
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

			this.$ajax('POST', '/api/products/provider/list', this.searchData)
			.then(res => {
				this.providerItems = res;
				this.isLoading = false;
			})
			.catch(err => {
				console.error(err); 
			})
		},
		enabled(id, enabled) {
			this.$ajax('POST', '/api/products/provider/toggle-enabled', {
				id: id,
				enabled: enabled,
			})
			.then(res => {
				this.providerItems.data.filter(o => o.id == id)[0].status = enabled;
			})
			.catch(err => {
				console.error(err); 
			})
		},
    },
}
</script>