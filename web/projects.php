<?php include_once('_header.php');
$error = '';
if(isset($_POST['submit'])){
    $error = DataHandler::saveProject($_POST);
    $is_new = !(isset($_POST['id']) && $_POST['id'] != null && $_POST['id']!='' && is_numeric($_POST['id']));
}

?>
<table class='data-table'>
    <thead>
        <tr>
            <th>id</th>
            <th>name</th>
            <th>svn path</th>
            <th>initial commit</th>
            <th>latest commit</th>
            <th>latest rvn</th>
            <th>last updated /svn/</th>
            <th>update</th>
            <th>edit</th>
            <th>delete</th>
        </tr>
    </thead>
    <tbody>
    <?php $result = DataHandler::queryProjects();
    while($row = $result->fetch_assoc()): ?>
        <tr id='p_<?php echo $row['id'];?>'>
            <td class='v_id'><?php echo $row['id']?></td>
            <td class='v_name'><?php echo $row['name']?></td>
            <td><?php echo $row['repo_path']?>/<span style="display:none" class="v_repo"><?php echo $row['repo']?></span><span class="v_path"><?php echo $row['path']?></span></td>
            <td class='v_initial_commit'><?php echo $row['initial_commit']?></td>
            <td class='v_initial_commit'><?php echo $row['latest_commit_date']?></td>
            <td class='v_latest_rvn'><?php echo $row['latest_rvn']?></td>
            <td class='v_updated_at'><?php $date=new DateTime($row['updated_at'], new DateTimeZone('Asia/Ulaanbaatar')); echo $date->format('Y/m/d H:i:s');?></td>
            <td><a  class='button blue' href="javascript:void(0);" onclick="window.open('<?php echo 'update.php?id='.$row['id'];?>','', 'width=700, height=400, location=no, menubar=no, status=no,toolbar=no, scrollbars=no, resizable=no');return false;">update svn log</a></td>
            <td><a  class='button orange' href="javascript:editProject('p_<?php echo $row['id'];?>')">edit</a></td>
            <td><input type='checkbox' class='delete_proj_chk' value="<?php echo $row['id'];?>"/></td>
        </tr>
    <?php endwhile; $result->close(); DataHandler::close();?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan=9></td>
            <td>
                <input type='checkbox' onchange="toggleCheckedAll(this, 'delete_proj_chk')"/> check all
                <a class='button red' href="javascript:deleteChecked('/actions/delete.php?what=projects&delete=','delete_proj_chk')" >delete</a>
            </td>
        </tr>
    </tfoot>
</table>
<form action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
<table class="input">
    <tbody>
    <tr>
        <th><label>path:</label></th>
        <td><select name="repo">
        <?php $result = DataHandler::queryRepos();
        while($row = $result->fetch_assoc()): ?>
        <option value="<?php echo $row['id']; ?>"><?php echo $row['path']; ?></option>
        <?php endwhile; ?>
        </select>/
        <input type=text value="<?php echo (isset($_POST['submit'])&&$is_new?$_POST['path']:'');?>" name='path' size=100 maxlength=255></td>
    </tr>
    <tr>
        <th><label>name:</label></th>
        <td><input type=text value="<?php echo (isset($_POST['submit'])&&$is_new?$_POST['name']:'');?>" name='name'></td>
    </tr>
    </tbody>
    <tfoot>
        <tr><td colspan="2"><input type="hidden" value="" name="id"/><input type="submit" name="submit" value="new"/>
<?php echo  $error;?>
        </td></tr>
    </tfoot>
</table>
</form>
<?php include_once("_log.php"); ?>
</body>
</html>