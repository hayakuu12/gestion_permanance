<?php

$page = $_GET['page'] ?? 'dashboard';

switch ($page) {

    case 'permanences':
        require 'views/permanences.php';
        break;

    case 'heures_supp':
        require 'views/heures_supp.php';
        break;

    case 'controles':
        require 'views/controles.php';
        break;

    case 'rapports':
        require 'views/rapports.php';
        break;
    case 'observations':
        require 'views/observations.php';
        break;
    case 'statistiques':
        require 'views/statistiques.php';
        break;
    case 'services':
        require 'views/services.php';
        break;
    default:
        require 'views/dashboard.php';
        break;
}