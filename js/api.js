function objCopy(obj) {
    return JSON.parse(JSON.stringify(obj));
}

class Api {
    constructor(path) {
        this.path = path;
        this.onError = function () { };
    }
    send(data, onSuccess, onFail) {

        var xhr = new XMLHttpRequest();
        xhr.open("POST", this.path);

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                let received = JSON.parse(xhr.responseText);
                if (received["ok"]) onSuccess(received["data"]);
                else onFail(received["data"]);

            }
        };
        try {

            xhr.send(JSON.stringify(data));
        } catch (e) {
            this.onError(e);
        }
    }
}





