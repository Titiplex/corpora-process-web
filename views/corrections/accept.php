<?php

use model\Corpus;

require_once __DIR__ . '/../../controllers/CorrectionController.php';
require_once __DIR__ . '/../../config/App.php';
require_once __DIR__ . '/../../config/Db.php';
require_once __DIR__ . '/../../models/Corpus.php';

session_start();
if (!isset($_SESSION['user_id']) || Corpus::findById(Db::getConn(), $_GET['corpus'])->getCreatedBy() != $_SESSION['user_id'])
    header('Location: ../../index.php');

try {
    CorrectionController::accept($_GET['id']);
    header("Location: ../frontside/view_comments.php?id={$_GET['id']}&corpus={$_GET['corpus']}");
} catch (Exception $e) {
    App::getLogger()->error($e->getMessage());
}