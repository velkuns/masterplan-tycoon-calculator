
window.addToList = function (buildingId, buildingName)
{
    let pattern = buildingId + ' x(\\d+)';
    let regexp  = new RegExp(pattern);

    let select  = document.getElementById('buildings-list');

    //~ Update existing entry
    for (let index in select.options) {
        let option   = select.options[index];
        let matches  = regexp.exec(option.value);

        if (matches === null) {
            continue;
        }

        let quantity = parseInt(matches[1]) + 1;
        option.text  = buildingName + ' x' + quantity;
        option.value = buildingId + ' x' + quantity;
        return;
    }

    //~ If not in list, add new entry in list
    let option   = document.createElement('option');
    let quantity = 1;
    option.text  = buildingName + ' x' + quantity;
    option.value = buildingId + ' x' + quantity;
    option.selected = true;
    select.add(option);
};

window.registerLang = function(lang) {
    setCookie('lang', lang, 360);
    window.location.href = window.location.href;
};


function setCookie(name, value, expireDays) {
    const expireDate = new Date();
    expireDate.setDate(expireDate.getDate() + expireDays);
    document.cookie = name + '=' + encodeURI(value) + '; path=/' + (!expireDays ? '' : ';expires=' + expireDate.toString() + '; SameSite=Strict');
}
