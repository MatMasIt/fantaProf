class DescriptorRecord {
    constructor(successCallback, failCallback, user) {
        this.successCallback = successCallback;
        this.failCallback = failCallback;
        this.storedData = {};
        this.user = user;
        this.api = new Api("./api");
        this.namespace = "descriptorRecords";
    }
    create(title, SNAICardId, profId, descriptorId, instant, comment) {
        if (!this.user.authed) {
            this.failCallback([]);
            return false;
        }
        this.api.send({
            'action': this.namespace + "/create",
            'SNAICardId': SNAICardId,
            'profId': profId,
            'descriptorId': descriptorId,
            'instant': instant,
            'comment': comment,
            'token': this.storedData["token"]
        }, function process(data) {
            this.get(data["id"]);
        }, this.failCallback);
    }
    get(id) {
        this.api.send({
            'action': this.namespace + "/get",
            'id': id,
            'token': this.storedData["token"]
        }, function process(data) {
            this.storedData = data;
            this.successCallback(data);
        }, this.failCallback);

    }
    delete() {
        this.api.send({
            "action": this.namespace + "/delete",
            "token": this.user.storedData["token"]
        }, this.successCallback, this.failCallback);
    }
    update() {
        let a = objCopy(this.storedData);
        a["token"] = this.user.storedData["token"];
        a["action"] = this.namespace + "/update";
        this.api.send(a, function ok(a) {
            this.get(a["id"]);
            this.successCallback(a);
        }, this.failCallback);
    }
    list() {
        if (!this.user.authed) {
            this.failCallback([]);
            return false;
        }
        this.api.send({
            'action': this.namespace + "/list",
            'token': this.user.storedData["token"]
        }, this.successCallback, this.failCallback);

    }
}