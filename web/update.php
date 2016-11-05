<?php 
require_once("../config.php");
require_once("../lib/logger.php");
require_once("../lib/svn.php");
require_once("../lib/mysql.php");
    //OD CONFIG
    DataHandler::setup($mysql_host, $mysql_db, $mysql_user, $mysql_pass);
    SvnWrapper::setup();
?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>SVN History</title>
<link href="/css/main.css" rel="stylesheet" />
<script src="/scripts/jquery-1.11.0.js"></script>
<script src="/scripts/main.js"></script>
</head>
<body style="vertical-align:middle;text-align:center;">
<?php DataHandler::updateProjectLog($_GET['id']);?><br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<input type='button' value='OK' onclick="window.opener.location.reload(); window.close();" style="margin-left:auto;margin-right:auto;"/>
<?php include_once("_log.php"); ?>
</body>
</html>
