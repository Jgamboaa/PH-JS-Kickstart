<?php
require_once __DIR__ . '/session_config.php';
require_once dirname(__DIR__) . '/../config/db_conn.php';
require_once __DIR__ . '/security_functions.php';
date_default_timezone_set('America/Guatemala');


// Verificar y renovar la sesión
function checkSession()
{
	$max_lifetime = 30 * 24 * 60 * 60; // 30 días en segundos
	$current_time = time();

	if (
		isset($_SESSION['last_activity']) &&
		($current_time - $_SESSION['last_activity']) > $max_lifetime
	)
	{
		// La sesión ha expirado
		session_unset();
		session_destroy();
		header('location: ../index.php');
		exit();
	}

	// Renovar el tiempo de la sesión
	$_SESSION['last_activity'] = $current_time;

	// Regenerar el ID de sesión periódicamente (cada 30 minutos)
	if (!isset($_SESSION['created']))
	{
		$_SESSION['created'] = time();
	}
	else if (time() - $_SESSION['created'] > 1800)
	{
		session_regenerate_id(true);
		$_SESSION['created'] = time();
	}
}

checkSession();

if (!isset($_SESSION['admin']) || trim($_SESSION['admin']) == '')
{
	header('location: index.php');
	exit();
}

$sql = "SELECT * FROM admin WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$_SESSION['admin']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

//update last_login time
$now_login = date('Y-m-d H:i:s');
$sql = "UPDATE admin SET last_login = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$now_login, $_SESSION['admin']]);

$sql = "SELECT * FROM company_data WHERE id = 1 LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute();
$data = $stmt->fetch(PDO::FETCH_ASSOC);

$rep_name = $data['rep_name'];
$company_name = $data['company_name'];
$company_name_short = $data['company_name_short'];
$company_address = $data['address'];
$rep_age_ltr = $data['rep_age_ltr'];
$rep_marital_status = $data['rep_marital_status'];
$rep_nacionality = $data['rep_nacionality'];
$rep_studies = $data['rep_studies'];
$rep_dpi_number = $data['rep_dpi_number'];
$rep_position = $data['rep_position'];
$company_nit = $data['company_nit'];
$company_employers_number = $data['company_employers_number'];
$app_name = $data['app_name'];
$app_version = $data['app_version'];
$developer_name = $data['developer_name'];
$rep_contract = $data['rep_contract'];

$photoPath = '../images/admins/' . $user['photo'];
$defaultPhoto = '../images/admins/profile.png';
$photoSrc = (!empty($user['photo']) && file_exists($photoPath)) ? $photoPath : $defaultPhoto;
