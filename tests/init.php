<?php


$GLOBALS["url"] = "http://127.0.0.1:8080/api/api.php";
require("Test.php");
require("TestSuite.php");
require("PrepareDatabase.php");
require("RestoreDatabase.php");
require("VoidTest.php");
require("CreateAccount.php");
require("AccountLoginEmail.php");
require("AccountLoginUsername.php");
require("AccountGetMe.php");
require("AccountChangePassword.php");
require("AccountLoginEmailNewPassword.php");
require("AccountLoginEmailNewPasswordIncorrect.php");
require("AccountDelete.php");
require("AccountLoginEmailAfterDeletion.php");

$testStuite = new TestSuite("Fantaprof test suite without persistence", "Simple fantaprof test suite", new PrepareDatabase(), new RestoreDatabase(), [
    new CreateAccount(),
    new AccountLoginEmail(),
    new AccountLoginUsername(),
    new AccountGetMe(),
    new AccountChangePassword(),
    new AccountLoginEmailNewPassword(),
    new AccountLoginEmailNewPasswordIncorrect(),
    new AccountDelete(),
    new AccountLoginEmailAfterDeletion()
]);

/*
$persistent = new TestSuite("Fantaprof test suite without persistence","Simple fantaprof test suite",new VoidTest(), new VoidTest(), [
    new CreateAccount()
]);
*/

$testStuite->run();
