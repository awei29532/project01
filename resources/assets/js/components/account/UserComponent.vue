<template>
    <section>
        <list-grid :data="userItems" :search="search">
            <template slot="search-form">
                <a v-if="user.isAdmin" class="btn btn-info mr-2 mb-1" @click="updateAllUser()">{{trans('users.update-all-user')}}</a>
				<b-input-group class="mr-2 mb-1" :prepend="trans('common.account')">
					<b-form-input type="text" v-model="searchData.account"></b-form-input>
				</b-input-group>
				<b-input-group class="mr-2 mb-1" :prepend="trans('common.status')">
					<b-form-select v-model="searchData.status">
						<option value="all">{{trans('common.all')}}</option>
						<option value="1">{{trans('common.active')}}</option>
						<option value="0">{{trans('common.suspended')}}</option>
					</b-form-select>
				</b-input-group>
                <b-input-group class="mr-2 mb-1" :prepend="trans('common.role')">
                    <b-form-select value="" @change.native="roleOnChange($event)">
                        <option value="">{{trans('common.all')}}</option>
                        <option :value="item.id"
                            v-for="item in roleOptions"
                            :key="item.id">
                                {{item.name}}
                        </option>
                    </b-form-select>
			    </b-input-group>
            </template>
            <template slot="table">
                <b-table responsive bordered striped small
                    :items="userItems.data" :fields="fields" :busy="isLoading"
					show-empty :empty-text="trans('common.empty-data')">
					<loading slot="table-busy"></loading>
                    <template slot="#" slot-scope="data">
                        {{data.index + 1 + userItems.perPage * (userItems.page - 1)}}
                    </template>
					<template slot="status" slot-scope="data">
						<button :class="'btn-pill btn-sm btn-' + (data.item.status ? 'success' : 'danger')"
							@click="enabledDialog(data.item.id, data.item.username, data.item.status, enabled)">
								{{ trans('common.' + (data.item.status ? 'active' : 'suspended')) }}
						</button>
					</template>
                    <template slot="role" slot-scope="data">
                        <b-form-select :id="'role-' + data.item.id"
                            :value="data.item.role"
                            @change.native="updateUserRoleDialog($event.target.value, data.item)">
                            <option :value="item.name"
                                v-for="item in roleOptions"
                                :key="item.id"
                                :disabled="data.item.role == item.name">
                                    {{trans('users.' + item.name)}}
                            </option>
                        </b-form-select>
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
            userItems: {
				data: [],
				page: 1,
				perPage: 25,
				total: 0,
				lastPage: 1,
            },
            fields: [
				{key: '#', thStyle: { width: '40px'}, class: 'text-center'},
                {key: 'username', sortable: true, label: this.trans('common.username'), thClass: 'text-center'},
				{key: 'status', sortable: true, label: this.trans('common.status'), class: 'table-col-status'},
                {key: 'role', sortable: true, label: this.trans('common.role'), thClass: 'text-center', thStyle: {minWidth: '190px'}},
                {key: 'updated_at', sortable: true, label: this.trans('common.updated_at'), class: 'table-col-time'},
                {key: 'created_at', sortable: true, label: this.trans('common.created_at'), class: 'table-col-time'},
            ],
            isLoading: false,
            searchData: {
                account: '',
                status: 'all',
                roles: [],
                page: 1,
                perPage: 25,
            },
            roleOptions: this.getJsonData('role-list'),
        };
    },
    mounted() {
        this.roleOptions.forEach(o => {
            this.searchData.roles.push(o.id);
        });
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

			this.$ajax('POST', '/api/accounts/user/list', this.searchData)
			.then(res => {
				this.userItems = res;
				this.isLoading = false;
			})
			.catch(err => {
				console.error(err); 
			})
        },
        roleOnChange(event) {
            this.searchData.roles = [];
            let id = event.target.value;
            if (id) {
                this.searchData.roles.push(id);
            } else {
                this.roleOptions.forEach(o => {
                    this.searchData.roles.push(o.id);
                });
            }
        },
		updateUserRoleDialog(value, data) {
			this.$bvModal.msgBoxConfirm(this.trans('users.' + data.role) + ' => ' + this.trans('users.' + value) + '?', {
				okTitle: this.trans('common.ok'),
				cancelTitle: this.trans('common.cancel'),
			}).
			then(res => {
				if (res) {
					this.updateUserRole(value, data.id);
				} else {
                    document.getElementById('role-' + data.id).value = data.role;
                }
			}).catch(err => {
				console.warn(err);
			});
		},
        updateUserRole(value, userID) {
            this.$ajax('POST', '/api/accounts/user/update-user-role', {
                role: value,
                id: userID,
            })
            .then(res => {
				this.userItems.data.filter(o => o.id == userID)[0].role = value;
            })
            .catch(err => {
                console.error(err); 
            })
        },
        updateAllUser() {
            this.isLoading = true;
            axios.get('/api/accounts/user/update-all-user')
            .then(res => {
                this.isLoading = false;
                this.search();
            })
            .catch(err => {
                console.error(err); 
            })
        },
		enabled(id, enabled) {
			this.$ajax('POST', '/api/accounts/user/toggle-enabled', {
				id: id,
				enabled: enabled,
			})
			.then(res => {
				this.userItems.data.filter(o => o.id == id)[0].status = enabled;
			})
			.catch(err => {
				console.error(err); 
			})
		},
    }
}
</script>

