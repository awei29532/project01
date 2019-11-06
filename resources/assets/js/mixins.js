import Vue from 'vue';
import { trans } from './libs/trans';
import { getJsonData, asyncDo, getDatetimeRange, $ajax, enabledDialog } from './libs/utils';

Vue.mixin({
    methods: {
        trans,
        getJsonData,
        asyncDo,
        getDatetimeRange,
        $ajax,
        enabledDialog,
    },
    data() {
        return {
            user: {
                ...window.user,
                isAdmin: window.isAdmin || false,
                isAdminSub: window.isAdminSub || false,
            },
        }
    },
    filters: {
        numberFormat (value) {
            let num = value.toFixed(2);
            return num.replace(/^(-?\d+?)((?:\d{3})+)(?=\.\d+$|$)/, function (all, pre, groupOf3Digital) {
                return pre + groupOf3Digital.replace(/\d{3}/g, ',$&');
            });
        }
    }
});

// Object.defineProperty(Vue.prototype, 'user', {
//     value: { ...window.user, isAdmin: window.isAdmin || false } || {},
// });