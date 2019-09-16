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
            <b-table striped :items="memberItems.data" :fields="fields" :busy="isLoading">
				<template slot="#" slot-scope="data">
					{{data.index + 1}}
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
            fields: [
                '#',
                {key: 'username', sortable: true, label: this.trans('common.username')},
                {key: 'agent', sortable: true, label: this.trans('members.agent')},
                {key: 'currency', sortable: true, label: this.trans('common.currency')},
                {key: 'balance', sortable: true, label: this.trans('members.balance')},
                {key: 'status', sortable: true, label: this.trans('common.status')},
                {key: 'updated_at', sortable: true, label: this.trans('common.updated_at')},
                {key: 'created_at', sortable: true, label: this.trans('common.created_at')},
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
            enabledMemberData: {},
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

			axios.post('/api/accounts/member/list', this.searchData)
			.then(res => {
				this.memberItems = res.data;
				this.isLoading = false;
			})
			.catch(err => {
				console.error(err); 
			})
		},
		enabled(id, enabled) {
			axios.post('/api/accounts/member/toggle-enabled', {
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
