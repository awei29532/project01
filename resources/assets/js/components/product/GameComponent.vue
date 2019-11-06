<template>
    <section>
        <list-grid :data="gameItems" :search="search">
            <template slot="search-form">
				<a v-if="user.isAdmin" class="btn btn-info mr-2 mb-1" href="/products/game-edit/">{{trans('common.add')}}</a>
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
				<b-input-group class="mr-2 mb-1" :prepend="trans('games.has_fun_col')">
					<b-form-select v-model="searchData.has_fun">
						<option value="all">{{trans('common.all')}}</option>
						<option value="1">{{trans('games.has_fun')}}</option>
						<option value="0">{{trans('games.not_has_fun')}}</option>
					</b-form-select>
				</b-input-group>
				<b-input-group class="mr-2 mb-1" :prepend="trans('games.provider')">
					<b-form-select value="" @change.native="providerOnChange($event.target.value)">
						<option value="">{{trans('common.all')}}</option>
                        <option v-for="item in providerOptions"
                            :key="item.id"
                            :value="item.id">
                                {{item.name}}
                        </option>
					</b-form-select>
				</b-input-group>
            </template>

            <template slot="table">
                <b-table responsive bordered striped small
                    :items="gameItems.data" :busy="isLoading" :fields="fields"
					show-empty :empty-text="trans('common.empty-data')">
					<loading slot="table-busy"></loading>
                    <template slot="#" slot-scope="data">
                        {{data.index + 1 + gameItems.perPage * (gameItems.page - 1)}}
                    </template>
                    <template slot="game_code" slot-scope="data">
                        <a :href="'/products/game-edit/' + data.item.id">{{data.item.game_code}}</a>
                    </template>
                    <template slot="status" slot-scope="data">
                        <button :class="'btn-pill btn-sm btn-' + (data.item.status ? 'success' : 'danger')"
							@click="enabledDialog(data.item.id, data.item.name, data.item.status, enabled)">
                                {{ trans('common.' + (data.item.status ? 'active' : 'suspended')) }}
                        </button>
                    </template>
                    <template slot="has_fun" slot-scope="data">
                        <span :class="'badge badge-' + (data.item.has_fun == 1 ? 'success' : 'danger')">
                            {{ trans('games.' + (data.item.has_fun == 1 ? 'has_fun' : 'not_has_fun')) }}
                        </span>
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
            gameItems: {},
			fields: [
				{key: '#', thStyle: { width: '40px'}, class: 'text-center'},
				{key: 'provider_name', sortable: true, label: this.trans('games.provider'), thClass: 'text-center', thStyle: {minWidth: '70px'}},
				{key: 'game_code', label: this.trans('games.game_code'), thClass: 'text-center', thStyle: {minWidth: '105px'}},
				{key: 'name', sortable: true, label: this.trans('common.name'), thClass: 'text-center', thStyle: {minWidth: '55px'}},
				{key: 'has_fun', label: this.trans('games.has_fun_col'), class: 'table-col-status'},
				{key: 'status', sortable: true, label: this.trans('common.status'), class: 'table-col-status'},
				{key: 'updated_at', sortable: true, label: this.trans('common.updated_at'), class: 'table-col-time'},
				{key: 'created_at', sortable: true, label: this.trans('common.created_at'), class: 'table-col-time'},
			],
            isLoading: false,
            searchData: {
                name: '',
                status: 'all',
                has_fun: 'all',
                providers: [],
            },
            providerOptions: this.getJsonData('provider-list'),
        };
    },
    mounted() {
        this.providerOptions.forEach(p => {
            this.searchData.providers.push(p.id);
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

			this.$ajax('POST', '/api/products/game/list', this.searchData)
			.then(res => {
				this.gameItems = res;
				this.isLoading = false;
			})
			.catch(err => {
				console.error(err); 
			})
		},
        providerOnChange(id) {
            this.searchData.providers = [];
            if (id) {
                this.searchData.providers.push(id);
            } else {
                this.providerOptions.forEach(o => {
                    this.searchData.providers.push(o.id);
                });
            }
        },
		enabled(id, enabled) {
			this.$ajax('POST', '/api/products/game/toggle-enabled', {
				id: id,
				enabled: enabled,
			})
			.then(res => {
				this.gameItems.data.filter(o => o.id == id)[0].status = enabled;
			})
			.catch(err => {
				console.error(err); 
			})
		},
    },
}
</script>