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
                <th>Max Bettable Prof</th>
                <th>Start</th>
                <th>End</th>
                <th>Professors</th>
                <th>Descriptors</th>
                <th>Owner</th>
                <th></th>
        </tr>`;
        data.forEach(function iterate(game) {
            console.log(game);
            let html = ` <tr>
            <td>`+ htmlEntities(game["title"]) + `</td>
            <td>`+ htmlEntities(game["description"]) + `</td>
            <td>`+ htmlEntities(game["maxBettableProfs"]) + `</td>
            <td>`+ htmlEntities(game["start"]) + `</td>
            <td>`+ htmlEntities(game["end"]) + `</td>
            <td>
                <ul>`;
            game["professorIds"].forEach(function iterate(id) {
                html += `<li data-prof="` + id + `"></li>`
            })
            html += `</ul></td>
            
            <td>
            <ul>`;
            game["descriptorIds"].forEach(function iterate(id) {
                html += `<li data-descriptor="` + id + `"></li>`
            })
            html += `</ul></td>
            <td data-owner="`+ game["gameMasterId"] + `"></td><td>`;
            if (me.storedData["id"] == game["gameMasterId"]) {
                html += `<button onclick="gameUpdatePreapare(` + game["id"] + `);">Edit</button><button onclick="gameDelete(` + game["id"] + `);"> Delete</button>`
            }
            html += `<button onclick="SNAICardView(+` + game["id"] + `);">My Game card</button>`;
            html += `</td></tr> `;
            document.getElementById("game-list-table").innerHTML += html;
        });
        setView("game-list");
        asyncTableListsFill();
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
                    <tr >
                <th>Tabula Picta</th>
                <th>Name</th>
                <th>Surname</th>
                <th>Comment</th>
                <th></th>
        </tr > `;
        data.forEach(function iterate(professor) {
            document.getElementById("professor-list-table").innerHTML += `
                    <tr >
            <td>
                <img src="`+ htmlEntities(professor["photoUrl"]) + `" style="width:200px">
            </td>
            <td>`+ htmlEntities(professor["name"]) + `</td>
            <td>`+ htmlEntities(professor["surname"]) + `</td>
            <td>`+ htmlEntities(professor["comment"]) + `</td>
            <th><button onclick="professorEdit(+`+ professor["id"] + `);">Edit</button><button onclick="professorDelete(+` + professor["id"] + `);" > Delete</button ></th >
            </tr > `;
        });
        setView("professor-list");

    };
    p.onFail = function (data) {
        alertify.error("Error");
    };
    p.list();
}

function gameCardShow(id) {
    setView("snaicard-list");
}


function DescriptorListView() {
    let d = new Descriptor(api, undefined, undefined, me);
    d.onSuccess = function (data) {
        document.getElementById("descriptor-list-table").innerHTML = `
                    <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Delta</th>
        </tr > `;
        data.forEach(function iterate(descriptor) {
            document.getElementById("descriptor-list-table").innerHTML += `
                    <tr>
            <td>`+ htmlEntities(descriptor["title"]) + `</td>
            <td>`+ htmlEntities(descriptor["description"]) + `</td>
            <td>`+ htmlEntities(descriptor["delta"]) + `</td>
            <th><button onclick="DescriptorEdit(+`+ descriptor["id"] + `);">Edit</button><button onclick="DescriptorDelete(+` + descriptor["id"] + `);" > Delete</button></th>
            </tr > `;
        });
        setView("descriptor-list");

    };
    d.onFail = function (data) {
        alertify.error("Error");
    };
    d.list();
}


function gameDelete(id) {

    let p = new Game(api, undefined, undefined, me);
    if (confirm("Are you sure?")) {
        p.onSuccess = function () {
            p.onSuccess = function () {
                alertify.success("Deleted");
                GameListView();
            }
            p.onFail = function () {
                alertify.error("Error");
                GameListView();
            }
            p.delete();
        }
        p.onFail = function () {
            alertify.error("Error");
            GameListView();
        }
        p.get(id);
    }
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

var descriptorEditId = 0;

function DescriptorEdit(id) {
    let d = new Descriptor(api, undefined, undefined, me);
    d.onSuccess = function (data) {
        document.querySelector("[data-view=descriptor-update]>form>input[name=title]").value = data["title"];
        document.querySelector("[data-view=descriptor-update]>form>input[name=description]").value = data["description"];
        document.querySelector("[data-view=descriptor-update]>form>input[name=delta]").value = data["delta"];
        setView("descriptor-update");
        descriptorEditId = id; // global
    }
    d.onFail = function () {
        alertify.error("Auth error");
    }
    d.get(id);

}


function DescriptorDelete(id) {

    let d = new Descriptor(api, undefined, undefined, me);
    if (confirm("Are you sure?")) {
        d.onSuccess = function () {
            d.onSuccess = function () {
                alertify.success("Deleted");
                DescriptorListView();
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
        </tr > `;
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
            </tr > `;
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
    if (data["title"].length == 0 || data["description"].length == 0 || data["maxBettableProfs"].length == 0 || data["start"].length == 0 || data["end"].length == 0 || data["imgUrl"].length == 0) {
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
    d.get(descriptorEditId);
});


formListen("descriptor-create", function login(data) {

    let d = new Descriptor(api, undefined, undefined, me);
    if (data["title"].length == 0 || data["description"].length == 0 || data["delta"].length == 0) {
        alertify.error('Fill all fields');
        return false;
    }
    d.onSuccess = function () {
        alertify.success("Saved");
        DescriptorListView();
    }
    d.onFail = function () {
        alertify.error("Wrong credentials");
    }

    d.create(data["title"], data["description"], data["delta"]);
});



function fillSelectorBoxProf(element, callback = undefined) {
    let p = new Professor(api, undefined, undefined, me);
    p.onFail = function () {

        alertify.error("Error");
        GameListView();
    };

    p.onSuccess = function (data) {
        element.innerHTML = "";
        data.forEach(function iterate(prof) {
            element.innerHTML += `<input type = "checkbox" name = "` + element.id + `-` + prof["id"] + `" /> ` + htmlEntities(prof["name"]) + " " + htmlEntities(prof["surname"]) + ` <br /> `;
        });
        if (callback != undefined) callback();
    };
    p.list();
}

function fillSelectorBoxDescriptors(element, callback = undefined) {
    let p = new Descriptor(api, undefined, undefined, me);
    p.onFail = function () {

        alertify.error("Error");
        GameListView();
    };

    p.onSuccess = function (data) {
        element.innerHTML = "";
        data.forEach(function iterate(prof) {
            element.innerHTML += `<input type = "checkbox" name = "` + element.id + `-` + prof["id"] + `" /> ` + htmlEntities(prof["title"]) + " (" + htmlEntities(prof["delta"]) + `) <br /> `;
        });
        if (callback != undefined) callback();
    };
    p.list();
}


function prepareGameCreate() {
    fillSelectorBoxProf(document.getElementById("game-create-prof-box"));
    fillSelectorBoxDescriptors(document.getElementById("game-create-descriptor-box"));
    setView('game-create');
}

formListen("game-create", function (data) {
    let finalData = {};
    finalData["title"] = data["title"];
    finalData["description"] = data["description"];
    finalData["start"] = data["start"];
    finalData["end"] = data["end"];
    finalData["maxBettableProfs"] = data["maxBettableProfs"];
    finalData["professors"] = [];
    finalData["descriptors"] = [];
    Object.keys(data).forEach(function iterate(key) {
        if (key.startsWith("game-create-descriptor-box-")) {
            let k = key.split("-");
            finalData["descriptors"].push(parseInt(k[k.length - 1]));
        }
        else if (key.startsWith("game-create-prof-box-")) {
            let k = key.split("-");
            finalData["professors"].push(parseInt(k[k.length - 1]));
        }
    });

    if (data["title"].length == 0 || data["description"].length == 0 || data["maxBettableProfs"].length == 0) {
        alertify.error("Fill all fields");
        return false;
    }
    finalData["maxBettableProfs"] = parseInt(finalData["maxBettableProfs"]);

    if (finalData["professors"].length == 0) {
        alertify.error("Choose at least one professor");
        return false;
    }


    if (finalData["descriptors"].length == 0) {
        alertify.error("Choose at least one descriptor");
        return false;
    }
    let g = new Game(api, undefined, undefined, me);
    g.onFail = function () {
        alertify.error("Error");
        GameListView();
    }
    g.onSuccess = function () {
        alertify.success("Success");
        GameListView();
    }
    g.create(finalData["title"], finalData["description"], finalData["maxBettableProfs"], finalData["start"], finalData["end"], finalData["professors"], finalData["descriptors"]);
});

function asyncTableListsFill() {
    document.querySelectorAll("[data-prof]").forEach(function iterate(element) {/* tutti quelli che hanno l'attributo*/
        let p = new Professor(api, undefined, undefined, me);
        p.onFail = function () {
            alertify.error("SPLAASH! perepe perererepepepepepe");
        }
        p.onSuccess = function (data) {
            element.innerHTML = data["name"] + " " + data["surname"];
        }
        p.get(element.getAttribute("data-prof"));

    });
    document.querySelectorAll("[data-descriptor]").forEach(function iterate(element) {/* tutti quelli che hanno l'attributo*/
        let p = new Descriptor(api, undefined, undefined, me);
        p.onFail = function () {
            alertify.error("SPLAASH! perepe perererepepepepepe");
        }
        p.onSuccess = function (data) {
            element.innerHTML = data["title"];
        }
        p.get(element.getAttribute("data-descriptor"));

    });
    document.querySelectorAll("[data-owner]").forEach(function iterate(element) {/* tutti quelli che hanno l'attributo*/
        let u = new User(api, undefined, undefined, me);
        u.storedData = {};
        u.storedData["token"] = me.storedData["token"];
        u.authed = true;
        u.onFail = function () {
            alertify.error("SPLAASH! perepe perererepepepepepe");
        }
        u.onSuccess = function (data) {
            element.innerHTML = data["username"];
        }
        u.get(element.getAttribute("data-owner"));
    });

    document.querySelectorAll("[data-game]").forEach(function iterate(element) {/* tutti quelli che hanno l'attributo*/
        let u = new Game(api, undefined, undefined, me);
        u.storedData = {};
        u.storedData["token"] = me.storedData["token"];
        u.authed = true;
        u.onFail = function () {
            alertify.error("SPLAASH! perepe perererepepepepepe");
        }
        u.onSuccess = function (data) {
            element.innerHTML = data["title"];
        }
        u.get(element.getAttribute("data-game"));
    });
}

var gameEditId = 0;
function gameUpdatePreapare(id) {
    let g = new Game(api, undefined, undefined, me);
    g.onSuccess = function (data) {
        fillSelectorBoxProf(document.getElementById("game-update-prof-box"), function () {
            fillSelectorBoxDescriptors(document.getElementById("game-update-descriptor-box"), function () {
                document.querySelector("[data-view=game-update]>form>input[name=title]").value = data["title"];
                document.querySelector("[data-view=game-update]>form>input[name=description]").value = data["description"];
                document.querySelector("[data-view=game-update]>form>input[name=start]").value = data["start"].split("+")[0];
                document.querySelector("[data-view=game-update]>form>input[name=end]").value = data["end"].split("+")[0];
                document.querySelector("[data-view=game-update]>form>input[name=maxBettableProfs]").value = data["maxBettableProfs"]
                data["professorIds"].forEach(function iterate(id) {
                    document.querySelector("[name=game-update-prof-box-" + id + "]").checked = true;
                });
                data["descriptorIds"].forEach(function iterate(id) {
                    document.querySelector("[name=game-update-descriptor-box-" + id + "]").checked = true;
                });
                setView("game-update");
            });
        });
    }
    g.onFail = function () {
        alertify.error("Auth error");
        GameListView();
    }
    g.get(id);
    gameEditId = id;
}


formListen("game-update", function (data) {
    let g = new Game(api, undefined, undefined, me);
    g.onSuccess = function () {
        let finalData = {};
        finalData["title"] = data["title"];
        finalData["description"] = data["description"];
        finalData["start"] = data["start"];
        finalData["end"] = data["end"];
        finalData["maxBettableProfs"] = data["maxBettableProfs"];
        finalData["professors"] = [];
        finalData["descriptors"] = [];
        Object.keys(data).forEach(function iterate(key) {
            if (key.startsWith("game-update-descriptor-box-")) {
                let k = key.split("-");
                finalData["descriptors"].push(parseInt(k[k.length - 1]));
            }
            else if (key.startsWith("game-update-prof-box-")) {
                let k = key.split("-");
                finalData["professors"].push(parseInt(k[k.length - 1]));
            }
        });

        if (data["title"].length == 0 || data["description"].length == 0 || data["maxBettableProfs"].length == 0) {
            alertify.error("Fill all fields");
            return false;
        }
        finalData["maxBettableProfs"] = parseInt(finalData["maxBettableProfs"]);

        if (finalData["professors"].length == 0) {
            alertify.error("Choose at least one professor");
            return false;
        }


        if (finalData["descriptors"].length == 0) {
            alertify.error("Choose at least one descriptor");
            return false;
        }
        g.storedData["title"] = finalData["title"];
        g.storedData["description"] = finalData["description"];
        g.storedData["start"] = finalData["start"];
        g.storedData["end"] = finalData["end"];
        g.storedData["start"] = finalData["start"];
        g.storedData["end"] = finalData["end"];
        g.storedData["maxBettableProfs"] = finalData["maxBettableProfs"];
        g.storedData["professorIds"] = finalData["professors"];
        g.storedData["descriptorIds"] = finalData["descriptors"];
        g.onSuccess = function () {
            alertify.success("Saved");
            GameListView();
        }
        g.onFail = function () {
            alertify.error("Auth error");
            GameListView();
        }
        g.update();
    }
    g.onFail = function () {
        alertify.error("Error");
        GameListView();
    }

    g.get(gameEditId);
});

let creatingGameId = 0;

function SNAICardView(gameId) {
    creatingGameId = gameId;
    let d = new SNAICard(api, undefined, undefined, me);
    d.onSuccess = function (data) {
        let html = `
        <tr>
        <th>Game</th>
        <th>Professors</th>
        <th></th>
    </tr> `;
        let tot = 0;
        data.forEach(function iterate(snai) {
            if (snai["userId"] != me.storedData["id"]) return;
            if (snai["gameId"] != gameId) return;
            html += `
                    <tr>
            <td data-game="`+ snai["gameId"] + `"></td><td><ul>`
            snai["bettedProfsId"].forEach(function iterate(id) {
                html += `<li><a href="javascript:DescriptorRecordsList(` + snai["id"] + `, ` + id + `);" data-prof="` + id + `"></a></li>`
            })
            html += `</ul></td>
            <td><button onclick="snaiCardDelete(+` + snai["id"] + `);" > Delete</button></td>
            </tr> `;
            document.getElementById("snaicard-list-table").innerHTML = html;
            setView("snaicard-list");
            tot++;
        });

        asyncTableListsFill();
        if (tot == 0) {
            alertify.success("Does not exist yet");

            fillSelectorBoxProf(document.getElementById("snaicard-create-prof-box"));
            setView("snaicard-create");
        }
    };
    d.onFail = function (data) {
        alertify.error("Error");
    };
    d.list();
}

function snaiCardDelete(id) {

    let p = new SNAICard(api, undefined, undefined, me);
    if (confirm("Are you sure?")) {
        p.onSuccess = function () {
            p.onSuccess = function () {
                alertify.success("Deleted");
                GameListView();
            }
            p.onFail = function () {
                alertify.error("Error");
                GameListView();
            }
            p.delete();
        }
        p.onFail = function () {
            alertify.error("Error");
            GameListView();
        }
        p.get(id);
    }
}


function descriptorRecordDelete(id) {

    let p = new DescriptorRecord(api, undefined, undefined, me);
    if (confirm("Are you sure?")) {
        p.onSuccess = function () {
            p.onSuccess = function () {
                alertify.success("Deleted");
                GameListView();
            }
            p.onFail = function () {
                alertify.error("Error");
                GameListView();
            }
            p.delete();
        }
        p.onFail = function () {
            alertify.error("Error");
            GameListView();
        }
        p.get(id);
    }
}
formListen("snaicard-create", function (data) {
    let p = new SNAICard(api, undefined, undefined, me);
    let profs = [];
    Object.keys(data).forEach(function (n) {
        let l = n.split("-");
        profs.push(parseInt(l[l.length - 1]));
    });
    p.onFail = function () {
        alertify.error("Error");
        GameListView();
    };
    p.onSuccess = function () {
        alertify.success("Saved");
        GameListView();
    };
    p.create(creatingGameId, me.storedData["id"], profs);
});


var lastSnaiCard = 0;
var lastProfId = 0;
function DescriptorRecordsList(snaiCardId, profId) {
    lastSnaiCard = snaiCardId;
    lastProfId = profId;
    let d = new DescriptorRecord(api, undefined, undefined, me);
    d.onSuccess = function (data) {
        let html = `
        <tr>
        <th>Professor</th>
        <th>Instant</th>
        <th>Comment</th>
        <th></th>
    </tr> `;
        data.forEach(function iterate(desc) {
            if (desc["SNAICardId"] != lastSnaiCard) return;
            if (desc["profId"] != lastProfId) return;
            html += `
                    <tr>
            <td data-prof="` + profId + `"></td>
            <td>`+ htmlEntities(desc["instant"]) + `</td>
            <td>`+ htmlEntities(desc["comment"]) + `</td>
            <td><button onclick="descriptorRecordDelete(+` + desc["id"] + `);" > Delete</button></td>
            </tr> `;
            //<button onclick="descriptorRecordAdd("`+snaiCardId+`","`+profId+`");" > Create</button>
        });
        document.getElementById("descriporRecord-list-table").innerHTML = html;
        asyncTableListsFill();
        setView("descriptorRecord-list");
    };
    d.onFail = function (data) {
        alertify.error("Error");
    };
    d.list();
}

function descriptorRecordAdd() {

    setView("descriptorRecord-create");
    fillSelectorSelect(document.querySelector("select[name=descriptorId]"));
}

function fillSelectorSelect(element, callback = undefined) {
    let p = new Descriptor(api, undefined, undefined, me);
    p.onFail = function () {

        alertify.error("Error");
        GameListView();
    };

    p.onSuccess = function (data) {
        element.innerHTML = "";
        data.forEach(function iterate(prof) {
            element.innerHTML += `<option value = "` + prof["id"] + `" /> ` + htmlEntities(prof["title"]) + " (" + htmlEntities(prof["delta"]) + `) </option> `;
        });
        if (callback != undefined) callback();
    };
    p.list();
}

formListen("descriptorRecord-create", function (data){
    console.log(data);
    let p = new DescriptorRecord(api, undefined, undefined, me);
    p.onFail = function () {
        alertify.error("Error");
        GameListView();
    };

    p.onSuccess = function (data) {
        alertify.success("Error");
        GameListView();
    
    };
    p.create(lastSnaiCard, lastProfId, data["descriptorId"], data["instant"], data["comment"]);
})