<?php

use \Bramus\Ansi\Ansi;
use \Bramus\Ansi\Writers\StreamWriter;
use \Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;


class AccountDelete extends Test
{
    public function __construct()

    {
        parent::__construct("Delete account", "Use token and password for account deletion");
    }
    public function run(array $resultValues): bool
    {

        //The data you want to send via POST
        $fields = [
            'action'      => "users/delete",
            'token'         => $resultValues[count($resultValues) - 2]["data"]["token"],
            "password" => "prova2"
        ];

        //url-ify the data for the POST
        $fields_string = http_build_query($fields);

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $GLOBALS["url"]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);
        $this->ansi->text("Data: ")->lf();
        $this->ansi->text(json_encode($fields, JSON_PRETTY_PRINT))->lf();
        $this->ansi->text("Response: ")->lf();
        $this->ansi->lf()->text($result)->lf();
        curl_close($ch);
        if (!curl_errno($ch)) {
            $info = curl_getinfo($ch);
            if ($info['http_code'] != 200) {
                $this->ansi->color(SGR::COLOR_FG_RED)
                    ->text('Got ' . $info['http_code'] . ' HTTP response')
                    ->nostyle()->lf()->bell()->lf();
                return false;
            }
            if (($data = json_decode($result, true)) == null) {
                $this->ansi->color(SGR::COLOR_FG_RED)
                    ->text('Reponse is not valid json')
                    ->nostyle()->lf()->bell()->lf();
                return false;
            }
            $this->setResultValues($data);
            if (!$data["ok"]) {
                $this->ansi->color(SGR::COLOR_FG_RED)
                    ->text('Reponse is not in a positive status')
                    ->nostyle()->lf()->bell()->lf();
                return false;
            }
            $this->ansi->text('Took ' . $info['total_time'] . ' s')->nostyle()->lf();
            return true;
        } else {
            $this->ansi->color(SGR::COLOR_FG_RED)
                ->text('Could not connect to api. Is the server up?')
                ->nostyle()->lf()->bell()->lf();
            return false;
        }
    }
}
