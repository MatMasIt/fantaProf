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
                if (received["ok"]) successCallback(received);
                else failCallback(data);

            }
        };

        xhr.send(data);
    }
}

class UserFactory {
    constructor(successCallback, failCallback, api) {
        this.successCallback = successCallback;
        this.failCallback = failCallback;
        this.api = api;
    }
    loginWithUsername(username, password) {

    }
    loginWithEmail(email, password) {

    }
    create(username, name, surname, email, classe, password, imgUrl) {
        api.send({
            'action': "users/signUp",
            'username': username,
            'name': name,
            'surname': surname,
            'email': email,
            'classe': classe,
            'password': password,
            'imgUrl': imgUrl
        }, this.successCallback, this.failCallback);
    }
}
class User {

}