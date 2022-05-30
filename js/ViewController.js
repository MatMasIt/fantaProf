
function setView(viewId) {
    document.querySelectorAll("[data-view]").forEach(function hide(element) {
        element.style.display = "none";
    })
    document.querySelector("[data-view=" + viewId + "]").style.display = "block";
    lastView = viewId;
    location.hash = viewId;
}
function serializeForm(form) {
    var obj = {};
    var formData = new FormData(form);
    for (var key of formData.keys()) {
        obj[key] = formData.get(key);
    }
    return obj;
}
function getFormFromView(viewId) {
    return document.querySelector("[data-view=" + viewId + "]>form");
}
function formListen(viewId, callback) {
    getFormFromView(viewId).addEventListener("submit", function (e) {
        e.preventDefault();
        callback(serializeForm(getFormFromView(viewId)));
        return false;
    })
}

function setText(view, element, text, raw) {
    if (raw) document.getElementById(view + "-" + element).innerHTML = text;
    else document.getElementById(view + "-" + element).innerText = text;
}
function htmlEntities(str) {
    return String(str).replace(/&/g, '&').replace(/</g, '<').replace(/>/g, '>').replace(/"/g, '"');
}