<?php include_once('_header.php');
$action = isset($_GET['action'])?$_GET['action']:'undeploy';

if(isset($_POST['submit'])){
    $deployment = DataHandler::getDeployment($_POST['deploy']);
    $deployment['status'] = 'undeploy';
    $deployment['id'] = $_POST['id'];
    $deployment['action_date'] = $_POST['action_date'];
    DataHandler::insertNewDeployment($deployment);
?>
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

    $rs = DataHandler::getLatestDeployments($_GET['id']);
    if($rs->num_rows > 0){
    ?>
<form method="POST" enctype="multipart/form-data" style="margin-left:50px;margin-top:50px;">
    <input type='hidden' value ='<?php echo $action;?>' name ='status'/>
    <input type='hidden' value ='<?php echo $_GET['id'];?>' name ='id'/>
  <table class="input">
    <tbody>
    <tr>
        <th>deployment:</th><td><select name="deploy">
        <?php 
        while($deployment = $rs->fetch_assoc()): ?>
        <option value="<?php echo $deployment['id'];?>"><?php echo $deployment['filename'].' - '.$deployment['action_date'].' :r'.$deployment['rvn'];?></option>
        <?php endwhile; 
        DataHandler::close();?></select></td>
    </tr><tr>
        <th>date:</th><td><input type='text' name='action_date' value='<?php echo date('Y-m-d H:i:s');?>'/></td>
    </tr><tr><td colspan="2" align='center'>
        <input type="submit" name="submit" value="<?php echo $action?>"/>
    </td></tr>
    </tbody>
</table>
</form>
<?php }else{ ?>


<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
NOTHING TO UNDEPLOY
<input type='button' value='OK' onclick="window.opener.location.reload(); window.close();" style="margin-left:auto;margin-right:auto;"/>
<?php } } ?>
<?php include_once("../_log.php"); ?>
</body>
</html>
