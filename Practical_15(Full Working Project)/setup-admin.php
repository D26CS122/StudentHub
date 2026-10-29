<?php
// Run only from CLI: php setup-admin.php admin@example.test
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/config/database.php';
$email=$argv[1]??'';if(!filter_var($email,FILTER_VALIDATE_EMAIL)){fwrite(STDERR,"Usage: php setup-admin.php admin@example.test\n");exit(2);}
fwrite(STDOUT,"Administrator display name: ");$name=trim((string)fgets(STDIN));fwrite(STDOUT,"Choose a strong administrator password (input may be visible): ");$password=trim((string)fgets(STDIN));
if(strlen($password)<12||!preg_match('/[A-Z]/',$password)||!preg_match('/[a-z]/',$password)||!preg_match('/[0-9]/',$password)){fwrite(STDERR,"Password must be 12+ characters with upper/lowercase letters and a number.\n");exit(2);}
$hash=password_hash($password,PASSWORD_DEFAULT);$s=$db->prepare("INSERT INTO users(name,email,password,role) VALUES(?,?,?,'admin') ON DUPLICATE KEY UPDATE name=VALUES(name),password=VALUES(password),role='admin',status='active'");$s->bind_param('sss',$name,$email,$hash);$s->execute();fwrite(STDOUT,"Admin account configured for $email. Delete or retain this CLI utility according to your local policy.\n");
