<template>
    <section>
        <list-grid :data="subItems" :search="search">
            <template slot="search-form">
                <a class="btn btn-info mr-2 mb-1" href="/accounts/sub_account-edit/">{{trans('common.add')}}</a>
                <b-input-group class="mr-2 mb-1" :prepend="trans('common.status')">
                    <b-form-select v-model="searchData.status">
                        <option value="all">{{trans('common.all')}}</option>
                        <option value="1">{{trans('common.active')}}</option>
                        <option value="0">{{trans('common.suspended')}}</option>
                    </b-form-select>
			    </b-input-group>
            </template>
            <template slot="table">
                <b-table striped :items="subItems.data" :fields="fields" :busy="isLoading">
                    <template slot="#" slot-scope="data">
                        {{data.index + 1}}
                    </template>
                    <template slot="username" slot-scope="data">
                        <a :href="'/accounts/sub_account-edit/' + data.item.id">{{data.item.username}}</a>
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
            subItems: {
				data: [],
				page: 1,
				perPage: 15,
				total: 0,
				lastPage: 1,
            },
            fields: [
                '#',
                {key: 'username', sortable: true, label: this.trans('common.username')},
                {key: 'name', sortable: true, label: this.trans('common.name')},
                {key: 'status', label: this.trans('common.status')},
                {key: 'remark', sortable: true, label: this.trans('common.remark')},
                {key: 'updated_at', sortable: true, label: this.trans('common.updated_at')},
                {key: 'created_at', sortable: true, label: this.trans('common.created_at')},
            ],
            isLoading: false,
            searchData: {
                account: '',
                status: 'all',
                page: 1,
                perPage: 25,
            },
            enabledSubData: {},
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

			axios.post('/api/accounts/sub_account/list', this.searchData)
			.then(res => {
				this.subItems = res.data;
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
			axios.post('/api/accounts/sub_account/toggle-enabled', {
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
			this.$bvModal.msgBoxConfirm(msg).
			then(value => {
				if (value) {
					this.enabled(data.id, data.status ? 0 : 1);
				}
			}).catch(err => {
				console.warn(err);
			});
		},
    }
}
</script>

