
function getProp(object, path, defaultVal) {
    const PATH = Array.isArray(path) ? path : path.split('.').filter(i => i.length);
    if (!PATH.length) {
        return object === undefined ? defaultVal : object;
    }
    if (object === null || object === undefined || typeof (object[PATH[0]]) === 'undefined') {
        return defaultVal;
    }
    return getProp(object[PATH.shift()], PATH, defaultVal);
}

/**
 * 翻譯
 * @param {string} path 路徑
 * @param {object} args 參數物件
 */
 export function trans(path, args)  {
    if (!path) {
        return path;
    }
    let val = getProp(window.i18n || {}, path, path);
    if (typeof args === 'object' && args !== null) {
        for (let key in args) {
            val = val.replace(new RegExp(':' + key, 'g'), args[key]);
        }
    }
    return val;
}