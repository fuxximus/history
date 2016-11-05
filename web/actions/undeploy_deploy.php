<?php include_once('_header.php');

$action = isset($_GET['action'])?$_GET['action']:'undeploy_deploy';

 if(isset($_POST['submit'])){
    if(placeFile() == 0){
        $deployment = DataHandler::getDeployment($_POST['deploy']);
        $deployment['status'] = 'undeploy';
        $deployment['id'] = $_POST['id'];
        $deployment['action_date'] = $_POST['u_action_date'];
        $deployment['comments'] = $_POST['u_comments'];
        DataHandler::insertNewDeployment($deployment);

        $_POST['status'] = 'deploy';
        $_POST['filename'] = $_FILES['upfile']['name'];
        DataHandler::insertNewDeployment($_POST);
    }?>
        <br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<input type='button' value='OK' onclick="window.opener.location.reload(); window.close();" style="margin-left:auto;margin-right:auto;"/>
    <?php
} else {
    $rs = DataHandler::getLatestDeployments($_GET['id']); ?>

<form method="POST" enctype="multipart/form-data" style="margin-left:50px;margin-top:50px;">
    <table class="input">
    <tbody>
    <?php
     if($rs->num_rows > 0){
    ?>
    <input type='hidden' value ='<?php echo $_GET['id'];?>' name ='id'/>

    <tr>
        <th>undeploy:</th><td><select name="deploy">
        <?php 
        while($deployment = $rs->fetch_assoc()): ?>
        <option value="<?php echo $deployment['id'];?>"><?php echo $deployment['filename'].' - '.$deployment['action_date'].' :r'.$deployment['rvn'];?></option>
        <?php endwhile; 
        DataHandler::close();?></select>
        </td>
    </tr><tr>
        <th>undeploy date:</th><td><input type='text' name='u_action_date' value='<?php $undeploy_date = new DateTime('now'); $undeploy_date->sub(new DateInterval('PT30S')); echo $undeploy_date->format('Y-m-d H:i:s');?>'/></td>
    </tr>
    <tr><th>comments:</th><td><textarea name='u_comments'></textarea></td></tr>
<?php }else{ 
    $action='deploy';?>
<tr>
        <th>undeploy:</th><td>NOTHING TO UNDEPLOY</td>
</tr>
<?php }
    ?>
    <input type='hidden' value ='<?php echo $action;?>' name ='status'/>
    <input type="hidden" name="MAX_FILE_SIZE" value="104857600" />
    <tr>
    <th>file:</th><td><input type="file" name='upfile'/></td>
    </tr><tr>
    <th>revision:</th><td><select name="rvn">
        <?php 
        $rs = DataHandler::getProjectAllHistory($_GET['id']);
        while($log = $rs->fetch_assoc()): ?>
                <option value="<?php echo $log['rvn'];?>">r<?php echo $log['rvn'].' '.substr($log['commit_date'],0,16).' '.$log['username'];?></option>
        <?php endwhile; ?></select>
    </td>
    </tr><tr>
        <th>deploy date:</th><td><input type='text' name='action_date' value='<?php echo date('Y-m-d H:i:s');?>'/></td>
    </tr>
    <tr><th>comments:</th><td><textarea name='comments'></textarea></td></tr>
    <tr>
        <td colspan="2" align="center"><input type="submit" name="submit" value="<?php echo $action?>"/></td>
    </tr>
    </tbody>
    </table>
    </select>
</form>
<?php } ?>
<?php include_once("../_log.php"); ?>
</body>
</html>
