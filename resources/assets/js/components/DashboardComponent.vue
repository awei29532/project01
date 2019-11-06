<template>
    <section class="container-fluid">
        <div class="card">
            <div class="card-header">
                {{trans('dashboard.summary')}}
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="callout callout-secondary">
                            <small class="text-muted">{{ trans('dashboard.players') }}</small>
                            <br>
                            <strong class="h4">{{ players }}</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="callout callout-secondary">
                            <small class="text-muted">{{ trans('dashboard.ticket') }}</small>
                            <br>
                            <strong class="h4">{{ players }}</strong>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-3 col-lg-2" v-for="item in currencys" :key="item">
                        <div class="callout callout-secondary">
                            <small class="text-muted">{{ item }}</small>
                            <br>
                            <strong class="h4">{{ profit[item].win }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                {{ trans('dashboard.players') }}
            </div>
            <div class="card-body">
                <b-form-group>
                    <b-form-radio-group class="mr-3" buttons button-variant="outline-primary" v-model="memberCountsType">
                        <b-form-radio class="mx-0" value="day">{{ trans('dashboard.day') }}</b-form-radio>
                        <b-form-radio class="mx-0" value="week">{{ trans('dashboard.week') }}</b-form-radio>
                        <b-form-radio class="mx-0" value="month">{{ trans('dashboard.month') }}</b-form-radio>
                    </b-form-radio-group>
                </b-form-group>
                <line-chart :chart-data="lineData"></line-chart>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                {{ trans('dashboard.profit') }}
            </div>
            <div class="card-body">
                <b-form-group>
                    <b-form-radio-group class="mr-3" buttons button-variant="outline-primary" v-model="reportType">
                        <b-form-radio class="mx-0" value="day">{{ trans('dashboard.day') }}</b-form-radio>
                        <b-form-radio class="mx-0" value="week">{{ trans('dashboard.week') }}</b-form-radio>
                        <b-form-radio class="mx-0" value="month">{{ trans('dashboard.month') }}</b-form-radio>
                    </b-form-radio-group>
                </b-form-group>

                <div class="card" v-for="item in barData" :key="item.currency">
                    <div class="card-header">
                        {{ item.currency }}
                    </div>
                    <div class="card-body">
                        <bar-chart :chart-data="item"></bar-chart>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import { startOfMonth, endOfMonth, startOfWeek, endOfWeek, format, addDays } from 'date-fns';
export default {
    data() {
        return {
            lineData: {
                labels: [],
                datasets: [{
                    label: this.trans('dashboard.players'),
                    data: [],
                }]
            },
            currencys: this.getJsonData('currencys'),
            players: this.getJsonData('players'),
            profit: this.getJsonData('profit'),
            barData: [],
            labels: {
                day: [],
                week: [],
                month: [],
            },
            memberCountsType: 'day',
            reportType: 'day',
        }
    },
    watch: {
        memberCountsType(type) {
            this.changeMemberType(type);
        },
        reportType(type) {
            this.changeReportType(type);
        }
    },
    mounted() {
        for (let i = 0; i <= 23; i++) {
            this.labels.day.push(i);
        }

        const date = new Date('2019-07-01');
        const weekStart = startOfWeek(date);
        for (let i = 0; i <= 6; i++) {
            let d = addDays(weekStart, i);
            this.labels.week.push(format(d, 'MM/dd (EEE)'));
        }

        const monStart = startOfMonth(date);
        const monEnd = format(endOfMonth(date), 'd');
        for (let i = 0; i < monEnd; i++) {
            let d = addDays(monStart, i);
            this.labels.month.push(format(d, 'MM/dd'));
        }

        for (let cur of this.currencys) {
            this.barData.push({
                currency: cur,
                labels: [],
                datasets: [
                    {
                        label: this.trans('dashboard.stake'),
                        data: [],
                        backgroundColor: '#3097D1',
                        key: 'stake',
                    },
                    {
                        label: this.trans('dashboard.payout'),
                        data: [],
                        backgroundColor: '#2ab27b',
                        key: 'payout',
                    },
                    {
                        label: this.trans('dashboard.win'),
                        data: [],
                        backgroundColor: '#bf5329',
                        key: 'win',
                    },
                ]
            });
        }

        this.changeMemberType('day');
        this.changeReportType('day');
    },
    methods: {
        changeMemberType(type) {
            this.$ajax('POST', '/api/dashboard/player-chart', { type: type })
                .then(res => {
                    let data = this.handleData(type, this.labels[type], res);
                    this.lineData['datasets'][0]['data'] = data['members'];
                    this.lineData = {...this.lineData, labels: this.labels[type]};
                });
        },
        changeReportType(type) {
            this.$ajax('POST', '/api/dashboard/profit-chart', { type: type })
                .then(res => {
                    for (let i of res) {
                        let data = this.handleData(type, this.labels[type], i.data);
                        for (let j in this.barData) {
                            if (this.barData[j].currency == i.currency) {
                                for (let k in this.barData[j]['datasets']) {
                                    let key = this.barData[j]['datasets'][k]['key'];
                                    this.barData[j]['datasets'][k]['data'] = data[key];
                                }
                                this.barData[j] = {...this.barData[j], labels: this.labels[type]};
                            }
                        }
                    }
                    this.barData = {...this.barData};
                });
        },
        handleData(type, labels, res) {
            if (!res.length) {
                return [];
            }

            let obj = [];
            for (let dataKey of Object.keys(res[0].data)) {
                obj[dataKey] = [];
                for (let labelKey of Object.keys(labels)) {
                    if (type == 'day') {
                        let row = res.filter(o => o.time == labels[labelKey])[0];
                        let data = row == undefined ? 0 : row.data[dataKey];
                        obj[dataKey].push(data);
                    } else {
                        let row = res.filter(o => {
                            let dateArr = o.time.split('-');
                            let matchDate = dateArr[1] + '/' + dateArr[2];
                            return labels[labelKey].indexOf(matchDate) != -1;
                        })[0];
                        let data = row == undefined ? 0 : row.data[dataKey];
                        obj[dataKey].push(data);
                    }
                }
            }
            return obj;
        },
    },
}
</script>

<style>

</style>