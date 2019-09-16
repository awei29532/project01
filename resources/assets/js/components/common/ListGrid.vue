<template>
	<div class="container-fluid">
		<div class="card">
			<b-navbar>
				<b-nav-form @submit.prevent="">
					<!-- search form -->
					<slot name="search-form"></slot>
					<b-input-group class="mb-1">
						<button type="button" class="btn btn-primary" @click="search()">
							<i class="fa fa-search"></i> {{trans('common.search')}}
						</button>
					</b-input-group>
				</b-nav-form>
			</b-navbar>
			<div class="card-body">
				<!-- table -->
				<slot name="table"></slot>
			</div>
			<div class="card-footer" v-if="paginate">
				<div class="row justify-content-between align-items-baseline">
					<b-input-group class="col-3" :prepend="trans('common.per_page')">
						<b-form-select v-model="perPage" @change.native="listSearch(1)">
							<option active value="25">25</option>
							<option value="50">50</option>
							<option value="100">100</option>
						</b-form-select>
					</b-input-group>
					<b-input-group class="col-3">
						<b-pagination 
							class="mx-auto" 
							size="md" 
							:total-rows="data.total" 
							:per-page="data.perPage" 
							v-model="data.page" 
							@change="listSearch"
						></b-pagination>
					</b-input-group>
					<b-input-group class="col-3" :prepend="trans('common.go_to')" :append="trans('common.page')">
						<b-form-input 
							type="number"
							min="1"
							:max="data.lastPage"
							v-model="page"
							v-on:keyup.enter="listSearch(page)"
						></b-form-input>
					</b-input-group>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
export default {
    props: {
		data: {
			type: Object,
		},
		search: {
			type: Function
		},
		paginate: {
			type: Boolean,
			default: true,
		},
	},
	data() {
		return {
			perPage: 25,
			page: '',
		};
	},
	mounted() {
		if (this.paginate) {
			this.page = this.data.page;
		}
	},
	watch: {
		data: function() {
			if (this.paginate) {
				this.page = this.data.page;
			}
		},
	},
	methods: {
		listSearch(page = 1) {
			this.search(page, this.perPage);
		}
	},
}
</script>
