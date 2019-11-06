import {
    format,
    startOfToday, endOfToday,
    startOfYesterday, endOfYesterday,
    startOfWeek, endOfWeek,
    startOfMonth, endOfMonth
} from 'date-fns';

/**
 * 取得json資料
 * @param {string} elemId 元素ID
 */
export function getJsonData(elemId) {
    let elem = document.querySelector(`script#${elemId}[type="application/json"]`);
    try {
        return (elem && elem !== null) ? JSON.parse(elem.textContent) : null;
    } catch (e) {
        console.error(e);
        return null;
    }
}
/**
 * 非同步執行
 * @param {Promise} promise 非同步物件
 */
export function asyncDo(promise) {
    return promise
    .then((res) => [ undefined, res ])
    .catch((err) => [ err, undefined ]);
}
/**
 * 取得時間區間
 * @param {string} name 名稱
 * @param {string} dtFormat 格式
 */
export function getDatetimeRange(name = 'today', dtFormat = 'yyyy-MM-dd HH:mm:ss') {
    const dateTime = new Date();
    let range = [];
    switch (name.toLocaleLowerCase()) {
        case 'yesterday':
            range = [ startOfYesterday(), endOfYesterday() ];
            break;
        case 'this_week':
            range = [ startOfWeek(dateTime), endOfWeek(dateTime) ];
            break;
        case 'this_month':
            range = [ startOfMonth(dateTime), endOfMonth(dateTime) ];
            break;
        case 'today': default:
            range = [ startOfToday(), endOfToday() ];
            break;
    }
    return [ format(range[0], dtFormat), format(range[1], dtFormat) ];
}

export function $ajax(method, url, data = null, params = null, showLoading = true) {
    let loader = showLoading ? this.$loading.show() : null;
    return new Promise((resolve, reject) => {
        axios({
            method: method,
            url: url,
            data: data,
            params: params,
        }).then(res => {
            showLoading ? loader.isActive = false : null;
            return resolve(res.data);
        }).catch(err => {
            showLoading ? loader.isActive = false : null;
            if (err.response.status == 500) {
                unknowErrorDialog.call(this);
            }
            return reject(err);
        });
    })
}

export function enabledDialog(id, name, status, enabledFunc) {
    let msg = this.trans('common.' + (status ? 'suspended' : 'active')) + ' ' + name; 
    this.$bvModal.msgBoxConfirm(msg, {
        okTitle: this.trans('common.ok'),
        cancelTitle: this.trans('common.cancel'),
    }).
    then(value => {
        if (value) {
            enabledFunc(id, status ? 0 : 1);
        }
    }).catch(err => {
        console.warn(err);
    });
}

export function unknowErrorDialog() {
    let msg = this.trans('common.unknow-error');
    this.$bvModal.msgBoxOk(msg, {
        okTitle: this.trans('common.ok'),
    });
}
