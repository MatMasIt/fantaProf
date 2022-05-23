function objCopy(obj) {
    return JSON.parse(JSON.stringify(obj));
}

class Api {
    constructor(path) {
        this.path = path
    }
    send(data, successCallback, failCallback) {

        var xhr = new XMLHttpRequest();
        xhr.open("GET", this.path);

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                let received = JSON.parse(xhr.responseText);
                if (received["ok"]) successCallback(received["data"]);
                else failCallback(received["data"]);

            }
        };

        xhr.send(data);
    }
}





