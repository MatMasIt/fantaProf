<?php
require("Reply.php");
require("IdFieldList.php");
require("exceptions/AuthErrorException.php");
require("exceptions/FieldValueException.php");
require("exceptions/NotFoundException.php");
require("exceptions/UnauthorizedException.php");
require("interfaces/CRUDL.php");
require("interfaces/serializeable.php");
require("dataObjects/User.php");
require("dataObjects/Game.php");
require("dataObjects/LoggedInUser.php");
try {
    $database = new PDO("sqlite:database.sqlite3");
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if (!empty($_POST["token"])) $u = LoggedInUser::getSession($database, $_POST["token"]);
    else $u = LoggedInUser::NOT_AUTHENTICATED;
    switch ($_POST["action"]) {
        case "users/loginEmail":
            if ($u == LoggedInUser::NOT_AUTHENTICATED) {
                $u = LoggedInUser::logInWithEmail($database, $_POST["email"], $_POST["password"]);
                Reply::ok($u->getUser()->serialize());
            }
            Reply::error("ALREADY_AUTHENTICATED");
            break;
        case "users/loginUsername":
            if ($u == LoggedInUser::NOT_AUTHENTICATED) {
                $u = LoggedInUser::loginInWithUsername($database, $_POST["username"], $_POST["password"]);
                Reply::ok($u->getUser()->serialize());
            }
            Reply::error("ALREADY_AUTHENTICATED");
            break;

        case "users/delete":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $u->delete($_POST["password"]);
                Reply::ok();
            }
            Reply::error("NOT_AUTHENTICATED");
            break;

        case "users/getMe":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                Reply::ok($u->getUser()->serialize());
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "users/signUp":
            $u = LoggedInUser::signUp($database, $_POST["username"], $_POST["name"],  $_POST["surname"], $_POST["email"], $_POST["classe"], $_POST["password"], $_POST["imgUrl"]);
            Reply::ok(["id" => $u->getId()]);
            break;
        case "users/changePassword":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                if(!$u->changePassword($_POST["newPassword"], $_POST["password"]))  throw new AuthErrorException();
                Reply::ok();
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "users/list":
            
            break;
        case "users/get":
            break;
        case "games/list":
            break;
        case "games/create":
            break;
        case "games/delete":
            break;
        case "games/update":
            break;
        case "professors/list":
            break;
        case "professors/create":
            break;
        case "professors/delete":
            break;
        case "professors/update":
            break;
        case "snaicards/list":
            break;
        case "snaicards/create":
            break;
        case "snaicards/delete":
            break;
        case "snaicards/update":
            break;
        case "snaicards/list":
            break;
        case "snaicards/create":
            break;
        case "snaicards/delete":
            break;
        case "snaicards/update":
            break;
        case "descriptors/list":
            break;
        case "descriptors/create":
            break;
        case "descriptors/delete":
            break;
        case "descriptors/update":
            break; 
        case "descriptorRecords/list":
            break;
        case "descriptorRecords/create":
            break;
        case "descriptorRecords/delete":
            break;
        case "descriptorRecords/update":
            break;  
    }
} catch (Exception $e) {
    Reply::error(get_class($e) . " : " . $e->getMessage());
}

Reply::ok();
