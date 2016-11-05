<?php include_once('_header.php');
$dates = DataHandler::getDeployHistoryDateCounts();
$rs = DataHandler::getAllDeployHistory();
?>
<table class='data-table'>
<thead>
    <th>date</th>
    <th>time</th>
    <th>action</th>
    <th>file</th>
    <th>rvn</th>
    <th>comments</th>
    <th>delete</th>
</thead>
<tbody>
<?php 
    $count_rows = 0;
    $prev_date = '';
while($d = $rs->fetch_assoc()):
    $action_date = new DateTime($d['action_date'], new DateTimeZone('Asia/Ulaanbaatar'));
?>
<tr>
<?php if($prev_date!=$action_date->format('Y-m-d')):
    $prev_date = $action_date->format('Y-m-d');
    $td_style = 'border-top:1px solid #999';
?>
    <td rowspan="<?php echo $dates[$prev_date]; ?>" style="<?php echo $td_style;?>"><?php echo $action_date->format('Y/m/d')?></td>
<?php else:
    $td_style ="";
endif; ?>
    <td style="<?php echo $td_style;?>"><?php echo $action_date->format('H:i')?></td>
    <td style="<?php echo $td_style;?>" class='<?php echo $d['status'];?>'><?php echo $d['status'];?></td>
    <td style="<?php echo $td_style;?>"><?php echo $d['filename']?></td>
    <td style="<?php echo $td_style;?>"><?php echo $d['status']=='undeploy'?'':$d['rvn']?></td>
    <td style="<?php echo $td_style;?>"><?php echo $d['comments']?></td>
    <td style="<?php echo $td_style;?>"><input type='checkbox' class='delete_depl_chk' value="<?php echo $d['id'];?>"/></td>
</tr>
<?php endwhile; ?>
</tbody>
    <tfoot>
        <tr>
            <td colspan=6></td>
            <td>
                <input type='checkbox' onchange="toggleCheckedAll(this, 'delete_depl_chk')"/> check all
                <a class='button red' href="javascript:deleteChecked('/actions/delete.php?what=deployments&delete=','delete_depl_chk')" >delete</a>
            </td>
        </tr>
    </tfoot>
</table>

<?php include_once("_log.php"); ?>
</body>
</head>