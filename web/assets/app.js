
window.addToList = function (buildingId, buildingName)
{
    let pattern = buildingId + ' x(\\d+)';
    let regexp  = new RegExp(pattern);

    let select  = document.getElementById('buildings-list');

    //~ Update existing entry
    for (let index in select.options) {
        let option   = select.options[index];
        let matches  = regexp.exec(option.text);

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

window.updateSelectValues = function ()
{
    let pattern = 'x(\\d+)';
    let regexp  = new RegExp(pattern);

    let select  = document.getElementById('buildings-list');

    //~ Update existing entry
    for (let index in select.options) {
        let option = select.options[index];

        let matches  = regexp.exec(option.text);
        let quantity = parseInt(matches[1]);
        option.value = option.value + ' x' + (quantity + 1);
    }

    return true;
}
