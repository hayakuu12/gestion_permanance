<?php

require_once '../autoload.php';

if(isset($_GET['numero_tajir'])){

    $numero_tajir = $_GET['numero_tajir'];

    $employeModel = new Employe();

    $employe = $employeModel->getEmployeByTajir(
        $numero_tajir
    );

    header('Content-Type: application/json');

    echo json_encode($employe);

}