<?php include_once('_header.php');

$action = isset($_GET['action'])?$_GET['action']:'deploy'; 

if(isset($_POST['submit'])){
    if(placeFile() == 0){
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
    ?>
<form method="POST" enctype="multipart/form-data" style="margin-left:50px;margin-top:50px;">
    <input type='hidden' value ='<?php echo $action;?>' name ='status'/>
    <input type='hidden' value ='<?php echo $_GET['id'];?>' name ='id'/>
    <input type="hidden" name="MAX_FILE_SIZE" value="104857600" />
  <table class="input">
    <tbody>
    <tr>
        <th>file:</th><td><input type="file" name='upfile'/></td>
    </tr>
    <tr>
        <th>revision:</th>
        <td>
            <select name="rvn">
                <?php 
                $rs = DataHandler::getProjectAllHistory($_GET['id']);
                while($log = $rs->fetch_assoc()): ?>
                <option value="<?php echo $log['rvn'];?>">r<?php echo $log['rvn'].' '.substr($log['commit_date'],0,16).' '.$log['username'];?></option>
                <?php endwhile; ?>
            </select>
        </td>
    </tr>
    <tr><th>date:</th><td><input type='text' name='action_date' value='<?php echo date('Y-m-d H:i:s');?>'/></td></tr>
    <tr><th>comments:</th><td><textarea name='comments'></textarea></td></tr>
    <tr><td colspan="2" align="center"><input type="submit" name="submit" value="<?php echo $action?>"/></td></tr>
    </tbody>
  </table>
</form>
<?php } ?>
<?php include_once("../_log.php"); ?>
</body>
</html>
