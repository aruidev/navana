<?php
function getBasePath() {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $navanaPos = strpos($scriptDir, '/navana');
    if ($navanaPos !== false) {
        return substr($scriptDir, 0, $navanaPos + strlen('/navana')) . '/assets/';
    } else {
        $depth = substr_count($scriptDir, '/');
        return str_repeat('../', $depth) . 'assets/';
    }
}
?>