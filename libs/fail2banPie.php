<?php
require '../autoload.php';
$Config = new Config();

if ($Config->get('fail2ban:enable')) {
    try {
        $jails = array();
        $bansByJail["labels"] = array();
        $bansByJail["series"] = array();

        $db = new SQLite3('/var/lib/fail2ban/fail2ban.sqlite3', SQLITE3_OPEN_READONLY);
        $queryResults = $db->query('SELECT name FROM jails WHERE enabled = 1');

        //get all jails
        while ($jail = $queryResults->fetchArray(SQLITE3_ASSOC)) {
            array_push($jails, $jail['name']);
        }

        //get all bans per jail for pie chart
        foreach ($jails as $jail) {
            $queryResults = $db->query("SELECT COUNT() banCount FROM bans WHERE jail = '" . $jail . "'");

            while ($var = $queryResults->fetchArray(SQLITE3_ASSOC)) {
                array_push($bansByJail["labels"], $jail);
                array_push($bansByJail["series"], $var["banCount"]);
            }
        }

        $db->close();

        echo json_encode($bansByJail);
    } catch (Exception $e) {
        echo 'Exception: ' . $e->getMessage();
    }
}
