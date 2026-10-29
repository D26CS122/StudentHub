<?php
require_once __DIR__.'/../config/database.php';require_once __DIR__.'/../includes/security.php';header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
function out($data,$status=200){http_response_code($status);echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);exit;}
if(empty($_SESSION['user_id']))out(['error'=>'Authentication required'],401);if(($_SESSION['user_role']??'')!=='admin')out(['error'=>'Administrator access required'],403);
$method=$_SERVER['REQUEST_METHOD'];
if($method==='GET'){$q='%'.trim($_GET['q']??'').'%';$page=max(1,(int)($_GET['page']??1));$limit=min(50,max(1,(int)($_GET['limit']??10)));$off=($page-1)*$limit;$s=$db->prepare('SELECT id,student_id,name,email,mobile,course,year_level,status FROM students WHERE name LIKE ? OR email LIKE ? OR course LIKE ? OR student_id LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?');$s->bind_param('ssssii',$q,$q,$q,$q,$limit,$off);$s->execute();out(['data'=>$s->get_result()->fetch_all(MYSQLI_ASSOC),'page'=>$page,'limit'=>$limit]);}
if(!in_array($method,['POST','PUT','DELETE'],true))out(['error'=>'Method not allowed'],405);
sh_verify_csrf();$input=$_POST;if(str_contains($_SERVER['CONTENT_TYPE']??'','application/json')){$input=json_decode(file_get_contents('php://input'),true)??[];}
$id=(int)($input['id']??$_GET['id']??0);
try{
 if($method==='DELETE'){sh_student_delete($db,$id);sh_audit($db,'delete','student',$id);out(['message'=>'Student and login account deleted']);}
 $sid=trim($input['student_id']??'');$name=trim($input['name']??'');$email=strtolower(trim($input['email']??''));$mobile=trim($input['mobile']??'');$course=trim($input['course']??'');$year=(int)($input['year_level']??1);$pw=(string)($input['password']??'');$sid=strtoupper($sid);
 if(!$sid||mb_strlen($name)<2||!filter_var($email,FILTER_VALIDATE_EMAIL)||!$course||$year<1||$year>8)out(['error'=>'Invalid student fields'],422);
 if($method==='PUT'||$id){$s=$db->prepare('UPDATE students SET student_id=?,name=?,email=?,mobile=?,course=?,year_level=? WHERE id=?');$s->bind_param('sssssii',$sid,$name,$email,$mobile,$course,$year,$id);$s->execute();$s->close();sh_student_account_upsert($db,$id,$sid,$name,$email,$course,$year,$pw);sh_audit($db,'update','student',$id);out(['message'=>'Student updated','id'=>$id]);}
 if(strlen($pw)<8)out(['error'=>'A password of at least 8 characters is required so the student can log in'],422);
 $s=$db->prepare('INSERT INTO students(student_id,name,email,mobile,course,year_level) VALUES(?,?,?,?,?,?)');$s->bind_param('sssssi',$sid,$name,$email,$mobile,$course,$year);$s->execute();$id=$s->insert_id;$s->close();sh_student_account_upsert($db,(int)$id,$sid,$name,$email,$course,$year,$pw);sh_audit($db,'create','student',$id);out(['message'=>'Student created','id'=>$id],201);
}catch(mysqli_sql_exception $e){out(['error'=>$e->getCode()===1062?'Student ID or email already exists':'Database operation failed'],409);}
