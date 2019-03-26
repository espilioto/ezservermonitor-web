<?php
require '../autoload.php';
$Config = new Config();

if ($Config->get('fail2ban:enable')) {
    try {
        $jails = array();
        $bans = array();
        $bansByJail;
        $bansByJail["labels"] = array();
        $bansByJail["series"] = array();

        $db = new SQLite3('/var/lib/fail2ban/fail2ban.sqlite3', SQLITE3_OPEN_READONLY);
        $queryResults = $db->query('SELECT name FROM jails WHERE enabled = 1');

        //get all jails
        while ($jail = $queryResults->fetchArray(SQLITE3_ASSOC)) {
            array_push($jails, $jail['name']);
        }

        // get bans per day for every jail
        foreach ($jails as $jail) {
            $jailBans = $db->query("SELECT strftime('%d/%m', DATE(timeofban, 'unixepoch', 'localtime')) days, COUNT() bans
                                    FROM bans
                                    WHERE jail = '" . $jail . "'
                                    GROUP BY strftime('%d', DATE(timeofban, 'unixepoch', 'localtime'))
                                    ORDER BY timeofban ASC
                                    LIMIT 14");

            $bans[$jail] = array();
            $bans[$jail]['days'] = array();
            $bans[$jail]['bans'] = array();

            while ($ban = $jailBans->fetchArray(SQLITE3_ASSOC)) {
                array_push($bans[$jail]['days'], $ban["days"]);
                array_push($bans[$jail]['bans'], $ban["bans"]);
            }
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

        $data[] = array(
            'jails' => $bans,
            'bansByJail' => $bansByJail,
        );

        echo json_encode($data);
    } catch (Exception $e) {
        echo 'Exception: ' . $e->getMessage();
    }
}
