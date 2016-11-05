<?php include_once('_header.php');
$error = '';
if(isset($_POST['submit'])){
    $error = DataHandler::saveRepo($_POST);

    $is_new = !(isset($_POST['id']) && $_POST['id'] != null && $_POST['id']!='' && is_numeric($_POST['id']));
}

?>
<table class='data-table'>
    <thead>
        <tr>
            <th>id</th>
            <th>svn path</th>
            <th>username</th>
            <th>password</th>
            <th>edit</th>
            <th>delete</th>
        </tr>
    </thead>
    <tbody>
    <?php $result = DataHandler::queryRepos();
    while($row = $result->fetch_assoc()): ?>
        <tr id='r_<?php echo $row['id'];?>'>
            <td class='v_id'><?php echo $row['id']?></td>
            <td class='v_path'><?php echo $row['path']?></td>
            <td class='v_svn_user'><?php echo $row['svn_user']?></td>
            <td class='v_svn_pass'><?php echo $row['svn_pass']?></td>
            <td><a  class='button orange' href="javascript:editRepo('r_<?php echo $row['id'];?>')">edit</a></td>
            <td><input type='checkbox' value='<?php echo $row['id'];?>' class='delete_repo_chk'/></td>
        </tr>
    <?php endwhile; $result->close(); DataHandler::close();?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan=5></td>
            <td>
                <input type='checkbox' onchange="toggleCheckedAll(this, 'delete_repo_chk')"/> check all
                <a  class='button red' href="javascript:deleteChecked('/actions/delete.php?what=repos&delete=','delete_repo_chk')" >delete</a>
            </td>
        </tr>
    </tfoot>
</table>
<form action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
<table class="input">
    <tbody>
    <tr>
        <th><label>svn path:</label></th>
        <td><input type=text value="<?php echo (isset($_POST['submit'])&&$is_new?$_POST['path']:'');?>" name='path'></td>
    </tr>
    <tr>
        <th><label>user:</label></th>
        <td><input type=text value="<?php echo (isset($_POST['submit'])&&$is_new?$_POST['svn_user']:'');?>" name='svn_user'></td>
    </tr>
    <tr>
        <th><label>pass:</label></th>
        <td><input type=text value="<?php echo (isset($_POST['submit'])&&$is_new?$_POST['svn_pass']:'');?>" name='svn_pass'></td>
    </tr>
    </tbody>
    <tfoot>
        <tr><td colspan="2"><input type="hidden" value="" name="id"/><input type="submit" name="submit" value="new"/>
<?php echo  $error;?>
        </td></tr>
    </tfoot>
</table>
</form>
<?php
include_once("_log.php");
?>
</body>
</html>