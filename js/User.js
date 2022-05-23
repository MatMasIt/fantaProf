class User {
    constructor(successCallback, failCallback) {
        this.successCallback = successCallback;
        this.failCallback = failCallback;
        this.storedData = {};
        this.authed = false;
        this.api = new Api("./api");
    }
    loginWithUsername(username, password) {
        this.api.send({
            'action': "users/loginUsername",
            'username': username,
            'password': password
        }, function process(data) {
            this.storedData = data;
            this.authed = true;
            this.successCallback(data);
        }, this.failCallback);
    }
    loginWithEmail(email, password) {
        this.api.send({
            'action': "users/loginUsername",
            'email': email,
            'password': password
        }, function process(data) {
            this.storedData = data;
            this.authed = true;
            this.successCallback(data);
        }, this.failCallback);
    }
    create(username, name, surname, email, classe, password, imgUrl) {
        this.api.send({
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
    list() {
        if (!this.authed) {
            this.failCallback([]);
        }
        this.api.send({
            'action': "users/list",
            'token': this.storedData["token"]
        }, this.successCallback, this.failCallback);
    }
    get(id, token) {
        if (!this.authed) {
            this.failCallback([]);
            return false;
        }
        this.api.send({
            'action': "users/get",
            'id': id,
            'token': token
        }, function process(data) {
            // preserve token nontheless, when changing user in view
            this.storedData = data;
            this.storedData["token"] = token;
            this.authed = true;
            this.successCallback(data);
        }, this.failCallback);

    }
    update() {
        if (!this.authed) {
            this.failCallback([]);
            return false;
        }
        let a = objCopy(this.storedData);
        a["action"] = "users/update";
        this.api.send(a, function ok(a) {
            this.getMe();
            this.successCallback(a);
        }, this.failCallback);
    }
    delete(password) {
        if (!this.authed) {
            this.failCallback([]);
            return false;
        }
        this.authed = false;
        this.api.send({
            "action": "users/delete",
            "token": this.storedData["token"],
            "password": password
        }, this.successCallback, this.failCallback);

    }
}