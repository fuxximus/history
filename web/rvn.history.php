<?php include_once('_header.php');
$dates = DataHandler::getRvnHistoryDateCounts($_GET['id']);
$rs = DataHandler::getProjectAllHistory($_GET['id']);
?>
<table class='data-table'>
<thead>
    <th>date</th>
    <th>time</th>
    <th>rvn</th>
    <th>username</th>
    <th>comments</th>
    <th>delete</th>
</thead>
<tbody>
<?php
    $count_rows = 0;
    $prev_date = '';
while($d = $rs->fetch_assoc()):
    $action_date = new DateTime($d['commit_date'], new DateTimeZone('Asia/Ulaanbaatar'));
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
    <td style="<?php echo $td_style;?>"><?php echo $d['rvn']?></td>
    <td style="<?php echo $td_style;?>"><?php echo $d['username']?></td>
    <td style="<?php echo $td_style;?>"><pre style="margin:0;width:600px"><?php echo htmlspecialchars($d['comments'])?></pre></td>
    <td style="<?php echo $td_style;?>" class="center"><input type='checkbox' class='delete_depl_chk' value="<?php echo $d['id'];?>"/></td>
</tr>
<?php endwhile; ?>
</tbody>
    <tfoot>
        <tr>
            <td colspan=5></td>
            <td colspan=1>
                <input type='checkbox' onchange="toggleCheckedAll(this, 'delete_depl_chk')"/> check all
                <a class='button red' href="javascript:deleteChecked('/actions/delete.php?what=rvn&delete=','delete_depl_chk')" >delete</a>
            </td>
        </tr>
    </tfoot>
</table>

<?php include_once("_log.php"); ?>
</body>
</head>
