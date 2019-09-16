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