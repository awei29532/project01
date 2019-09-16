<template>
    <section>
        <list-grid :data="providerItems" :search="search">
            <template slot="search-form">
				<a class="btn btn-info mr-2" href="/products/provider-edit/">{{trans('common.add')}}</a>
                <b-input-group class="mr-2 mb -1" :prepend="trans('common.name')">
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
                <b-table :items="providerItems.data" :busy="isLoading" :fields="fields">
                    <div slot="table-busy" class="text-center text-danger my-2">
                        <strong>Loading...</strong>
                    </div>
                    <template slot="#" slot-scope="data">
                        {{data.index + 1}}
                    </template>
                    <template slot="name" slot-scope="data">
                        <a :href="'/products/provider-edit/' + data.item.id">{{data.item.name}}</a>
                    </template>
                    <template slot="status" slot-scope="data">
                        <button :class="'btn-pill btn-' + (data.item.status ? 'success' : 'danger')"
                            @click="enableMsgDialog(data.item)">
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
				'#',
				{key: 'name', sortable: true, label: this.trans('common.name')},
				{key: 'code', sortable: true, label: this.trans('providers.code')},
                {key: 'status', sortable: true, label: this.trans('common.status')},
                {key: 'maintenance_start', sortable: true, label: this.trans('providers.maintenance_start')},
                {key: 'maintenance_end', sortable: true, label: this.trans('providers.maintenance_end')},
				{key: 'updated_at', sortable: true, label: this.trans('common.updated_at')},
				{key: 'created_at', sortable: true, label: this.trans('common.created_at')},
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

			axios.post('/api/products/provider/list', this.searchData)
			.then(res => {
				this.providerItems = res.data;
				this.isLoading = false;
			})
			.catch(err => {
				console.error(err); 
			})
		},
		enabled(id, enabled) {
			axios.post('/api/products/provider/toggle-enabled', {
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
			let msg = this.trans('common.' + (data.status ? 'suspended' : 'active')) + ' ' + data.name; 
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
    },
}
</script>