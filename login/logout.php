<?php
require_once '../config/session.php';
session_destroy();
header('Location: /EcomersPakHikmat/index.php');
exit;
