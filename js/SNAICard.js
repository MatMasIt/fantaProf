class SNAICard {
    constructor(api, onSuccess, onFail, user) {
        this.onSuccess = onSuccess;
        this.onFail = onFail;
        this.storedData = {};
        this.user = user;
        this.api = api;
        this.namespace = "snaicards";
    }
    create(gameId, userId, bettedProfsId) {
        if (!this.user.authed) {
            this.onFail([]);
            return false;
        }
        this.api.send({
            'action': this.namespace + "/create",
            'gameId': gameId,
            'userId': userId,
            'bettedProfsId': bettedProfsId,
            'token': this.user.storedData["token"]
        }, function process(data) {
            this.get(data["id"]);
        }.bind(this), this.onFail);
    }
    get(id) {
        this.api.send({
            'action': this.namespace + "/get",
            'id': id,
            'token': this.user.storedData["token"]
        }, function process(data) {
            this.storedData = data;
            this.onSuccess(data);
        }.bind(this), this.onFail);
    }
    delete() {
        this.api.send({
            "action": this.namespace + "/delete",
            "token": this.user.storedData["token"],
            "id": this.storedData["id"]
        }, this.onSuccess, this.onFail);
    }
    update() {
        let a = objCopy(this.storedData);
        a["token"] = this.user.storedData["token"];
        a["action"] = this.namespace + "/update";
        this.api.send(a, function ok(a) {
            this.get(a["id"]);
            this.onSuccess(a);
        }.bind(this), this.onFail);
    }
    list() {
        if (!this.user.authed) {
            this.onFail([]);
            return false;
        }
        this.api.send({
            'action': this.namespace + "/list",
            'token': this.user.storedData["token"]
        }, this.onSuccess, this.onFail);

    }
}