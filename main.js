document.addEventListener("DOMContentLoaded", function (event) {
    setView("user-login");
});
var api = new Api("./api/api.php");
api.onError = function () {
    alertify.warning("Connection Error")
};
var me = new User(api);


// we don't want user to fill in both
document.getElementById("user-login-username").addEventListener("input", function cleanOther() {
    if (document.getElementById("user-login-email").value.length != 0) alertify.warning("Fill either username or email");
    document.getElementById("user-login-email").value = "";
});

document.getElementById("user-login-email").addEventListener("input", function cleanOther() {
    if (document.getElementById("user-login-username").value.length != 0) alertify.warning("Fill either username or email");
    document.getElementById("user-login-username").value = "";
});


function HomeView() {
    document.getElementById("user-home-image").src = me.storedData["imgUrl"];
    setText("user-home", "username", me.storedData["username"]);
    setText("user-home", "email", me.storedData["email"]);
    setText("user-home", "name", me.storedData["name"]);
    setText("user-home", "surname", me.storedData["surname"]);
    setText("user-home", "classe", me.storedData["classe"]);
    setView("user-home");
}

function GameListView() {

    let g = new Game(api, undefined, undefined, me);
    g.onSuccess = function (data) {
        document.getElementById("game-list-table").innerHTML = `
        <tr>
                <th>Title</th>
                <th>Description</th>
                <th>maxBettableProfs</th>
                <th>Start</th>
                <th>End</th>
                <th>professorIds</th>
                <th>descriptorIds</th>
        </tr>`;
        data.forEach(function iterate(game) {

            document.getElementById("game-list-table").innerHTML += `
            <tr>
            <td>`+ htmlEntities(game["title"]) + `</td>
            <td>`+ htmlEntities(game["description"]) + `</td>
            <td>`+ htmlEntities(game["maxBettableProfs"]) + `</td>
            <td>`+ htmlEntities(game["start"]) + `</td>
            <td>`+ htmlEntities(game["end"]) + `</td>
            <td>`+ htmlEntities(game["professorIds"]) + `</td>
            <td>`+ htmlEntities(game["descriptorIds"]) + `</td>
            </tr>`;
        });
        setView("game-list");
    };
    g.onFail = function (data) {
        alertify.error("Game error");
    };
    g.list();

}

function UserEditView() {
    document.querySelector("[data-view=user-update]>form>input[name=username]").value = me.storedData["username"];
    document.querySelector("[data-view=user-update]>form>input[name=name]").value = me.storedData["name"];
    document.querySelector("[data-view=user-update]>form>input[name=surname]").value = me.storedData["surname"];
    document.querySelector("[data-view=user-update]>form>input[name=email]").value = me.storedData["email"];
    document.querySelector("[data-view=user-update]>form>input[name=classe]").value = me.storedData["classe"];
    document.querySelector("[data-view=user-update]>form>input[name=imgUrl]").value = me.storedData["imgUrl"];
    setView("user-update");
}

function ProfessorListView() {
    let p = new Professor(api, undefined, undefined, me);
    p.onSuccess = function (data) {
        document.getElementById("professor-list-table").innerHTML = `
        <tr>
                <th>Tabula Picta</th>
                <th>Name</th>
                <th>Surname</th>
                <th>Comment</th>
                <th></th>
        </tr>`;
        data.forEach(function iterate(professor) {
            document.getElementById("professor-list-table").innerHTML += `
            <tr>
            <td>
                <img src="`+ htmlEntities(professor["photoUrl"]) + `" style="width:200px">
            </td>
            <td>`+ htmlEntities(professor["name"]) + `</td>
            <td>`+ htmlEntities(professor["surname"]) + `</td>
            <td>`+ htmlEntities(professor["comment"]) + `</td>
            <th><button onclick="professorEdit(+`+ professor["id"] + `);">Edit</button><button onclick="professorDelete(+` + professor["id"] + `);">Delete</button></th>
            </tr>`;
        });
        if (lastView != "professor-list") setView("professor-list");

    };
    p.onFail = function (data) {
        alertify.error("Error");
    };
    p.list();
}


function DescriptorListView() {
    let d = new Descriptor(api, undefined, undefined, me);
    d.onSuccess = function (data) {
        document.getElementById("descriptor-list-table").innerHTML = `
        <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Delta</th>
        </tr>`;
        data.forEach(function iterate(descriptor) {
            document.getElementById("descriptor-list-table").innerHTML += `
            <tr>
            <td>`+ htmlEntities(descriptor["title"]) + `</td>
            <td>`+ htmlEntities(descriptor["description"]) + `</td>
            <td>`+ htmlEntities(descriptor["delta"]) + `</td>
            <th><button onclick="DescriptorEdit(+`+ descriptor["id"] + `);">Edit</button><button onclick="DescriptorDelete(+` + descriptor["id"] + `);">Delete</button></th>
            </tr>`;
        });
        if (lastView != "descriptor-list") setView("descriptor-list");

    };
    d.onFail = function (data) {
        alertify.error("Error");
    };
    d.list();
}



var profEditId = 0;

function professorDelete(id) {

    let p = new Professor(api, undefined, undefined, me);
    if (confirm("Are you sure?")) {
        p.onSuccess = function () {
            p.onSuccess = function () {
                alertify.success("Deleted");
                ProfessorListView();
            }
            p.onFail = function () {
                alertify.error("Error");
                ProfessorListView();
            }
            p.delete();
        }
        p.onFail = function () {
            alertify.error("Error");
            ProfessorListView();
        }
        p.get(id);
    }
}

function professorEdit(id) {
    let p = new Professor(api, undefined, undefined, me);
    p.onSuccess = function (data) {
        document.querySelector("[data-view=professor-update]>form>input[name=name]").value = data["name"];
        document.querySelector("[data-view=professor-update]>form>input[name=surname]").value = data["surname"];
        document.querySelector("[data-view=professor-update]>form>input[name=photoUrl]").value = data["photoUrl"];
        document.querySelector("[data-view=professor-update]>form>input[name=comment]").value = data["comment"];
        setView("professor-update");
        profEditId = id; // global
    }
    p.onFail = function () {
        alertify.error("Auth error");
    }
    p.get(id);

}

function DescriptorEdit(id) {
    let d = new Descriptor(api, undefined, undefined, me);
    p.onSuccess = function (data) {
        document.querySelector("[data-view=descriptor-update]>form>input[name=title]").value = data["title"];
        document.querySelector("[data-view=descriptor-update]>form>input[name=description]").value = data["description"];
        document.querySelector("[data-view=descriptor-update]>form>input[name=delta]").value = data["delta"];
        setView("Descriptor-update");
        profEditId = id; // global
    }
    p.onFail = function () {
        alertify.error("Auth error");
    }
    p.get(id);

}

var DesciptorEditId = 0;

function DescriptorDelete(id) {

    let d = new Descriptor(api, undefined, undefined, me);
    if (confirm("Are you sure?")) {
        d.onSuccess = function () {
            d.onSuccess = function () {
                alertify.success("Deleted");
                DecsriptorListView();
            }
            d.onFail = function () {
                alertify.error("Error");
                DescriptorListView();
            }
            d.delete();
        }
        d.onFail = function () {
            alertify.error("Error");
            DecsriptorListView();
        }
        d.get(id);
    }
}


function UserListView() {
    me.onSuccess = function (data) {
        document.getElementById("user-list-table").innerHTML = `
        <tr>
                <th>Tabula Picta</th>
                <th>Username</th>
                <th>Email</th>
                <th>Name</th>
                <th>Surname</th>
                <th>Classe</th>
        </tr>`;
        data.forEach(function iterate(user) {
            document.getElementById("user-list-table").innerHTML += `
            <tr>
            <td>
                <img src="`+ htmlEntities(user["imgUrl"]) + `" style="width:200px">
            </td>
            <td>`+ htmlEntities(user["username"]) + `</td>
            <td>`+ htmlEntities(user["email"]) + `</td>
            <td>`+ htmlEntities(user["name"]) + `</td>
            <td>`+ htmlEntities(user["surname"]) + `</td>
            <td>`+ htmlEntities(user["classe"]) + `</td>
            </tr>`;
        });
        setView("user-list");
    };
    me.onFail = function (data) {
        alertify.error("Megasus error");
    };
    me.list();
}
document.getElementById("user-home-users").addEventListener("click", function () {
    UserListView();
});

document.getElementById("user-home-games").addEventListener("click", function () {
    GameListView();
});

document.getElementById("user-home-professors").addEventListener("click", function () {
    ProfessorListView();
});

document.getElementById("user-home-descriptors").addEventListener("click", function () {
    DescriptorListView();
})

formListen("user-login", function login(data) {
    if (data["username"].length == 0 && data["email"].length == 0) {
        alertify.error('Type username or email');
        return false;
    }
    if (data["password"].length == 0) {
        alertify.error('Type password');
        return false;
    }
    me.onSuccess = function () {
        alertify.success("Welcome, " + me.storedData["username"]);
        HomeView();
    }
    me.onFail = function () {
        alertify.error("Wrong credentials");
    }

    if (data["email"].length != 0) me.loginWithEmail(data["email"], data["password"]);
    else me.loginWithUsername(data["username"], data["password"]);

});


formListen("user-create", function login(data) {
    if (data["username"].length == 0 || data["name"].length == 0 || data["surname"].length == 0 || data["email"].length == 0 || data["classe"].length == 0 && data["password"].length == 0 || data["imgUrl"].length == 0) {
        alertify.error('Fill all fields');
        return false;
    }
    me.onSuccess = function () {
        alertify.success("Saved");
    }
    me.onFail = function () {
        alertify.error("Wrong credentials");
    }

    me.create(data["username"], data["name"], data["surname"], data["email"], data["classe"], data["password"], data["imgUrl"]);
});

document.getElementById("user-home-edit").addEventListener("click", function () {
    UserEditView();
});

formListen("user-update", function update(data) {
    if (data["username"].length == 0 || data["name"].length == 0 || data["surname"].length == 0 || data["email"].length == 0 || data["classe"].length == 0 || data["imgUrl"].length == 0) {
        alertify.error('Fill all fields');
        return false;
    }
    me.onSuccess = function () {
        alertify.success("Saved");
        HomeView();
    }
    me.onFail = function () {
        alertify.error("Auth error");
    }

    me.storedData["username"] = data["username"];
    me.storedData["name"] = data["name"];
    me.storedData["surname"] = data["surname"];
    me.storedData["email"] = data["email"];
    me.storedData["classe"] = data["classe"];
    me.storedData["imgUrl"] = data["imgUrl"];
    me.update();
});

document.getElementById("user-home-edit").addEventListener("click", function () {
    UserEditView();
});

formListen("professor-create", function (data) {

    if (data["name"].length == 0 || data["surname"].length == 0 || data["photoUrl"].length == 0 || data["comment"].length == 0) {
        alertify.error('Fill all fields');
        return false;
    }
    let p = new Professor(api, undefined, undefined, me);
    p.onSuccess = function () {
        alertify.success("Saved");
        ProfessorListView();
    }
    p.onFail = function () {
        alertify.error("Auth error");
    }
    p.create(data["name"], data["surname"], data["photoUrl"], data["comment"]);

});


formListen("professor-update", function (data) {

    let p = new Professor(api, undefined, undefined, me);


    p.onFail = function () {

        alertify.error("Error");
        ProfessorListView();
    };

    p.onSuccess = function () {


        p.onFail = function () {

            alertify.error("Error");
            ProfessorListView();
        };

        p.onSuccess = function () {
            alertify.success("Saved");
            ProfessorListView();
        };


        p.storedData["name"] = data["name"];
        p.storedData["surname"] = data["surname"];
        p.storedData["photoUrl"] = data["photoUrl"];
        p.storedData["comment"] = data["comment"];
        p.update();
    };
    p.get(profEditId);
});

formListen("descriptor-update", function (data) {

    let d = new Descriptor(api, undefined, undefined, me);


    d.onFail = function () {

        alertify.error("Error");
        ProfessorListView();
    };

    d.onSuccess = function () {


        d.onFail = function () {

            alertify.error("Error");
            DescriptorListView();
        };

        d.onSuccess = function () {
            alertify.success("Saved");
            DescriptorListView();
        };


        d.storedData["title"] = data["title"];
        d.storedData["description"] = data["description"];
        d.storedData["delta"] = data["delta"];
        d.update();
    };
    d.get(profEditId);
});