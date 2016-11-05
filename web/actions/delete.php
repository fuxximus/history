<?php include_once('_header.php');

$action = isset($_GET['action'])?$_GET['action']:'deploy';  ?>


<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<?php if(isset($_GET['delete'])):
    $delete_ids = explode(',',$_GET['delete']);
    $delete_repos_ids = array();
    switch($_GET['what']){
        case 'deployments':
            $rs = DataHandler::getDeployments($delete_ids);
            $success = true;
            while($d = $rs->fetch_assoc()){
                if(($d['status'] == 'deploy' || $d['status'] == 'redeploy') && !empty($d['filename']) && !deleteBackup($d)){
                    $success = false;
                }
            }
            if($success){
                echo DataHandler::deleteDeployments($delete_ids);
            } else {
                echo '0';
            }
            DataHandler::close();
        break;
        case 'rvn':
            $deleted = DataHandler::deleteRvns($delete_ids);
            DataHandler::close();
            echo $deleted;
        break;

        case 'repos':
            $delete_repos_ids = $delete_ids;
            $delete_ids = DataHandler::getRepoProjectIds($delete_repos_ids);
        case 'projects':
            $deleted = DataHandler::deleteProjects($delete_ids);
            if(!empty($delete_repos_ids)){
                $deleted += DataHandler::deleteRepos($delete_repos_ids);
            }
            DataHandler::close();
            echo $deleted;
        break;
    }


    ?> rows deleted<br/>
<?php endif; ?>
<input type='button' value='OK' onclick="window.opener.location.reload(); window.close();" style="margin-left:auto;margin-right:auto;"/>

<?php include_once("../_log.php"); ?>
</body>
</html>
