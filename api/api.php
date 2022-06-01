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
require("dataObjects/Professor.php");
require("dataObjects/Descriptor.php");
require("dataObjects/SNAICard.php");
require("dataObjects/DescriptorRecord.php");
try {
    $dataJson = json_decode(file_get_contents("php://input"), true);
    $database = new PDO("sqlite:database.sqlite3");
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if (!empty($dataJson["token"])) $u = LoggedInUser::getSession($database, $dataJson["token"]);
    else $u = LoggedInUser::NOT_AUTHENTICATED;
    switch ($dataJson["action"]) {
        case "users/loginEmail":
            if ($u == LoggedInUser::NOT_AUTHENTICATED) {
                $u = LoggedInUser::logInWithEmail($database, $dataJson["email"], $dataJson["password"]);
                Reply::ok($u->getUser()->serialize());
            }
            Reply::error("ALREADY_AUTHENTICATED");
            break;
        case "users/loginUsername":
            if ($u == LoggedInUser::NOT_AUTHENTICATED) {
                $u = LoggedInUser::loginInWithUsername($database, $dataJson["username"], $dataJson["password"]);
                Reply::ok($u->getUser()->serialize());
            }
            Reply::error("ALREADY_AUTHENTICATED");
            break;

        case "users/delete":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $u->delete($dataJson["password"]);
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
            $u = LoggedInUser::signUp($database, $dataJson["username"], $dataJson["name"],  $dataJson["surname"], $dataJson["email"], $dataJson["classe"], $dataJson["password"], $dataJson["imgUrl"]);
            Reply::ok(["id" => $u->getId()]);
            break;
        case "users/changePassword":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                if (!$u->changePassword($dataJson["newPassword"], $dataJson["password"]))  throw new AuthErrorException();
                Reply::ok();
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "users/list":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $user = new User($database, $u);
                Reply::ok($user->list());
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "users/get":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $u = new User($database, $u);
                $u->get((int) $dataJson["id"]);
                Reply::ok($u->serialize());
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "users/update":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $g = new User($database, $u);
                $g->get((int) $dataJson["id"]);
                $g->deserialize($dataJson);
                $g->update();
                Reply::ok($g->serialize());
            }
            break;
        case "games/list":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $game = new Game($database, $u);
                Reply::ok($game->list());
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "games/create":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $p = new Game($database, $u);
                $p->create($dataJson);
                Reply::ok(["id" => $p->getId()]);
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "games/delete":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $user = new Game($database, $u);
                $user->get((int) $dataJson["id"]);
                $user->delete();
                Reply::ok();
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "games/update":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $g = new Game($database, $u);
                $g->get((int) $dataJson["id"]);
                $g->deserialize($dataJson);
                $g->update();
                Reply::ok($g->serialize());
            }
            break;
        case "professors/list":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $professor = new Professor($database, $u);
                Reply::ok($professor->list());
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "professors/create":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $p = new Professor($database, $u);
                $p->create($dataJson);
                Reply::ok(["id" => $p->getId()]);
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "professors/delete":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $prof = new Professor($database, $u);
                $prof->get((int) $dataJson["id"]);
                $prof->delete();
                Reply::ok();
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "professors/update":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $g = new Professor($database, $u);
                $g->get((int) $dataJson["id"]);
                $g->deserialize($dataJson);
                $g->update();
                Reply::ok($g->serialize());
            }
            break;
        case "games/get":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $u = new Game($database, $u);
                $u->get((int) $dataJson["id"]);
                Reply::ok($u->serialize());
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "professors/get":

            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $prof = new Professor($database, $u);
                $prof->get((int) $dataJson["id"]);
                Reply::ok($prof->serialize());
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "snaicards/list":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $snai = new SNAICard($database, $u);
                Reply::ok($snai->list());
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "snaicards/create":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $p = new SNAICard($database, $u);
                $p->create($dataJson);
                Reply::ok(["id" => $p->getId()]);
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "snaicards/delete":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $snai = new SNAICard($database, $u);
                $snai->get((int) $dataJson["id"]);
                $snai->delete();
                Reply::ok();
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "snaicards/update":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $g = new SNAICard($database, $u);
                $g->get((int) $dataJson["id"]);
                $g->deserialize($dataJson);
                $g->update();
                Reply::ok($g->serialize());
            }
            break;
        case "snaicards/get":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $snai = new SNAICard($database, $u);
                $snai->get((int) $dataJson["id"]);
                Reply::ok($snai->serialize());
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "descriptors/list":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $descriptor = new Descriptor($database, $u);
                Reply::ok($descriptor->list());
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "descriptors/create":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $p = new Descriptor($database, $u);
                $p->create($dataJson);
                Reply::ok(["id" => $p->getId()]);
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "descriptors/delete":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $desc = new Descriptor($database, $u);
                $desc->get((int) $dataJson["id"]);
                $desc->delete();
                Reply::ok();
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "descriptors/update":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $g = new Descriptor($database, $u);
                $g->get((int) $dataJson["id"]);
                $g->deserialize($dataJson);
                $g->update();
                Reply::ok($g->serialize());
            }
            break;
        case "descriptors/get":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $de = new Descriptor($database, $u);
                $de->get((int) $dataJson["id"]);
                Reply::ok($de->serialize());
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "descriptorRecords/list":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $descriptorRecord = new DescriptorRecord($database, $u);
                Reply::ok($descriptorRecord->list());
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "descriptorRecords/create":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $p = new DescriptorRecord($database, $u);
                $p->create($dataJson);
                Reply::ok(["id" => $p->getId()]);
            }
            Reply::error("NOT_AUTHENTICATED");

            break;
        case "descriptorRecords/delete":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $descRec = new DescriptorRecord($database, $u);
                $descRec->get((int) $dataJson["id"]);
                $descRec->delete();
                Reply::ok();
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
        case "descriptorRecords/update":

            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $g = new DescriptorRecord($database, $u);
                $g->get((int) $dataJson["id"]);
                $g->deserialize($dataJson);
                $g->update();
                Reply::ok($g->serialize());
            }
            break;
        case "descriptorRecords/get":
            if ($u != LoggedInUser::NOT_AUTHENTICATED) {
                $der = new DescriptorRecord($database, $u);
                $der->get((int) $dataJson["id"]);
                Reply::ok($der->serialize());
            }
            Reply::error("NOT_AUTHENTICATED");
            break;
    }
} catch (Exception $e) {
    Reply::error(get_class($e) . " : " . $e->getMessage(), $e->getTraceAsString());
}

Reply::ok();
