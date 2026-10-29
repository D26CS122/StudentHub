<?php
require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../includes/security.php';header('Content-Type: application/json; charset=utf-8');if(empty($_SESSION['user_id'])){http_response_code(401);exit(json_encode(['error'=>'Authentication required']));}
if($_SERVER['REQUEST_METHOD']==='GET'){$uid=(int)$_SESSION['user_id'];$s=$db->prepare('SELECT r.id,r.status,r.registered_at,e.title,e.event_date,e.venue FROM registrations r JOIN events e ON e.id=r.event_id WHERE r.user_id=? ORDER BY r.id DESC');$s->bind_param('i',$uid);$s->execute();exit(json_encode(['data'=>$s->get_result()->fetch_all(MYSQLI_ASSOC)]));}
http_response_code(405);echo json_encode(['error'=>'Method not allowed']);
