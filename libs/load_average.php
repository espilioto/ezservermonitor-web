<?php
require '../autoload.php';

if (!($load_tmp = shell_exec('cat /proc/loadavg | awk \'{print $1","$2","$3}\'')))
{
    $load = array(0, 0, 0);
}
else
{
    // Number of cores
    $cores = Misc::getCpuCoresNumber();

    $load_exp = explode(',', $load_tmp);
}

echo json_encode($load_exp);
