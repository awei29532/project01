import Vue from 'vue';
import { trans } from './libs/trans';
import { getJsonData } from './libs/utils';

Vue.mixin({
    methods: {
        trans,
        getJsonData
    }
});
