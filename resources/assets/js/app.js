
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
// polyfill
import 'core-js/es6/promise';           // promise
import '@babel/runtime/regenerator';    // async/await

import Vue from 'vue';
import { SidebarToggler, HeaderDropdown } from '@coreui/vue';
import { 
    BPagination, BTable, FormPlugin, 
    FormSelectPlugin, DropdownPlugin, 
    BadgePlugin, NavbarPlugin, ModalPlugin, 
    InputGroupPlugin, FormInputPlugin, FormGroupPlugin, 
    FormTextareaPlugin, BFormRadio, BFormCheckbox,
    FormRadioPlugin 
} from 'bootstrap-vue';
import datetime from 'vuejs-datetimepicker';
import VueLoading from 'vue-loading-overlay';

// custom
import './bootstrap';
import './mixins';

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
// register modules
Vue.use(FormPlugin);
Vue.use(FormInputPlugin);
Vue.use(FormSelectPlugin);
Vue.use(DropdownPlugin);
Vue.use(BadgePlugin);
Vue.use(NavbarPlugin);
Vue.use(ModalPlugin);
Vue.use(InputGroupPlugin);
Vue.use(FormGroupPlugin);
Vue.use(FormTextareaPlugin);
Vue.use(VueLoading);
Vue.use(FormRadioPlugin);

Vue.component('sidebar-toggler', SidebarToggler);
Vue.component('header-dropdown', HeaderDropdown);

// vue component
Vue.component('b-pagination', BPagination);
Vue.component('b-table', BTable);
Vue.component('b-form-radio', BFormRadio);
Vue.component('b-form-checkbox', BFormCheckbox);
Vue.component('datetime', datetime);

// custom common component
Vue.component('list-grid', require('./components/common/ListGrid.vue').default);
Vue.component('loading', require('./components/common/Loading.vue').default);

// chart
Vue.component('line-chart', require('./components/chart/LineChart.vue').default);
Vue.component('bar-chart', require('./components/chart/BarChart.vue').default);

// component
Vue.component('login-component', () => import( /* webpackChunkName: "login" */ './components/LoginComponent.vue'));
Vue.component('provider-component', () => import( /* webpackChunkName: "provider" */ './components/product/ProviderComponent.vue'));
Vue.component('provider-edit-component', () => import( /* webpackChunkName: "provider-edit" */ './components/product/ProviderEditComponent.vue'));
Vue.component('game-component', () => import( /* webpackChunkName: "game" */ './components/product/GameComponent.vue'));
Vue.component('game-edit-component', () => import( /* webpackChunkName: "game-edit" */ './components/product/GameEditComponent.vue'));
Vue.component('agent-component', () => import( /* webpackChunkName: "agent" */ './components/account/AgentComponent.vue'));
Vue.component('agentedit-component', () => import( /* webpackChunkName: "agent-edit" */ './components/account/AgentEditComponent.vue'));
Vue.component('member-component', () => import( /* webpackChunkName: "member" */ './components/account/MemberComponent.vue'));
Vue.component('sub-component', () => import( /* webpackChunkName: "sub" */ './components/account/SubComponent.vue'));
Vue.component('subedit-component', () => import( /* webpackChunkName: "sub-edit" */ './components/account/SubEditComponent.vue'));
Vue.component('user-component', () => import( /* webpackChunkName: "user" */ './components/account/UserComponent.vue'));
Vue.component('winlose-component', () => import( /* webpackChunkName: "win-lose" */ './components/report/WinLoseComponent.vue'));
Vue.component('bethistory-component', () => import( /* webpackChunkName: "bet-history" */ './components/report/BetHistoryComponent.vue'));
Vue.component('transfer-component', () => import( /* webpackChunkName: "transfer" */ './components/report/TransferHistoryComponent.vue'));
Vue.component('allreport-component', () => import( /* webpackChunkName: "all-report" */ './components/report/AllReportComponent.vue'));
Vue.component('allreportsw-component', () => import( /* webpackChunkName: "all-report-sw" */ './components/report/AllReportSWComponent.vue'));
Vue.component('wallet-component', () => import( /* webpackChunkName: "wallet" */ './components/report/WalletComponent.vue'));
Vue.component('change-password-component', () => import( /* webpackChunkName: "change-password" */ './components/ChangePasswordComponent.vue'));
Vue.component('setting-component', () => import( /* webpackChunkName: "setting" */ './components/SettingComponent.vue'));
Vue.component('log-component', () => import( /* webpackChunkName: "log" */ './components/LogComponent.vue'));
Vue.component('dashboard-component', () => import( /* webpackChunkName: "dashboard" */ './components/DashboardComponent.vue'));

const app = new Vue({
    el: '#app'
});
