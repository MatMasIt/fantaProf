class User {
    constructor(api, onSuccess, onFail) {
        // Q: What is the _ratio_ for setting the callbacks at a class-level rather than  passing them at a function level?
        // A: We don't want parallel operations to be performed on stateful objects
        this.onSuccess = onSuccess;
        this.onFail = onFail;
        this.storedData = {};
        this.authed = false;
        this.api = api;
    }
    loginWithUsername(username, password) {
        this.api.send({
            'action': "users/loginUsername",
            'username': username,
            'password': password
        }, function process(data) {
            this.storedData = data;
            this.authed = true;
            this.onSuccess(data);
        }.bind(this).bind(this), this.onFail);
    }
    loginWithEmail(email, password) {
        this.api.send({
            'action': "users/loginEmail",
            'email': email,
            'password': password
        }, function process(data) {
            this.storedData = data;
            this.authed = true;
            this.onSuccess(data);
        }.bind(this), this.onFail);
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
        }, this.onSuccess, this.onFail);
    }
    list() {
        if (!this.authed) {
            this.onFail([]);
        }
        this.api.send({
            'action': "users/list",
            'token': this.storedData["token"]
        }, this.onSuccess, this.onFail);
    }
    get(id, token) {
        if (!this.authed) {
            this.onFail([]);
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
            this.onSuccess(data);
        }.bind(this), this.onFail);

    }
    update() {
        if (!this.authed) {
            this.onFail([]);
            return false;
        }
        let a = objCopy(this.storedData);
        a["action"] = "users/update";
        this.api.send(a, function ok(a) {
            this.onSuccess(a);
        }.bind(this), this.onFail);
    }
    delete(password) {
        if (!this.authed) {
            this.onFail([]);
            return false;
        }
        this.authed = false;
        this.api.send({
            "action": "users/delete",
            "token": this.storedData["token"],
            "password": password
        }, this.onSuccess, this.onFail);

    }
}