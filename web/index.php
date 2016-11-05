<?php include_once('_header.php');
$end_date = new DateTime((isset($_GET['end_date'])?$_GET['end_date']:date('Y/m/d')), new DateTimeZone('Asia/Ulaanbaatar'));
$end_date->add(new DateInterval('P1D'));
if(isset($_GET['start_date'])){
    $start_date = new DateTime($_GET['start_date'], new DateTimeZone('Asia/Ulaanbaatar'));
} else {
    $start_date = new DateTime(date('Y/m/d'), new DateTimeZone('Asia/Ulaanbaatar'));
    $start_date->sub(new DateInterval('P3M'));
}

$actual_end_date = clone $end_date;
$actual_end_date->sub(new DateInterval('P1D'));
$timeline_width = 800;
$total_period = $end_date->getTimestamp() - $start_date->getTimestamp();


function offset_($datetime){
    global $start_date, $total_period, $timeline_width;
    $offset = $datetime->getTimestamp() - $start_date->getTimestamp();
    $offset_percent = $offset/$total_period;
    return ($timeline_width*$offset_percent);
}

function width_($start, $end){
    global $total_period, $timeline_width;
    $width = $end->getTimestamp() - $start->getTimestamp();
    $width_percent = $width/$total_period;
    return ($timeline_width*$width_percent);
}

$prefixes = DataHandler::getAllAvailablePrefixes($start_date, $end_date);
$doubles = array( 0=>'double0', 1=>'double1');
$triples = array( 0=>'triple0', 1=>'triple1', 2=>'triple2');


switch(count($prefixes)){
    case 0:break;

    case 1:
        foreach($prefixes as $prefix => $val){
            $prefixes[$prefix] = 'single0';
        }
    break;

    case 2:
        foreach($prefixes as $prefix => $val){
            $prefixes[$prefix] = $doubles[$val];
        }
    break;

    case 3:
    default:
        foreach($prefixes as $prefix => $val){
            $prefixes[$prefix] = $triples[$val];
        }
    break;
}


?>
<form method="get" action="/" style="display:inline-block;">
<input type='text' name='start_date' value="<?php echo $start_date->format('Y-m-d')?>" size='10'/> - <input type='text'  name='end_date' value="<?php echo $actual_end_date->format('Y-m-d')?>" size='10'/>
<input type='submit' value='go' >
</form>
filter: <input type="text" value="" onkeyup="searchandhide(this);"/>
<table class='data-table'>
<thead>
    <th>project</th>
    <th style="padding:1px"><div class='timeline' style="width:<?php echo $timeline_width ?>px">
        <?php $current_date = clone $start_date;
        while($current_date->getTimestamp() < $end_date->getTimestamp()):
            $offset = $current_date->getTimestamp() - $start_date->getTimestamp();
            $percent = $offset/$total_period;
            ?>
            <div class="tick <?php echo ($current_date->format('j')=='1'?'month':'day');?>" style="left:<?php echo $timeline_width*$percent-1;?>px"><div>&nbsp;</div></div>
        <?php $current_date->add(new DateInterval('P1D'));
        endwhile; ?>
    </div></th>
    <th>actions</th>
</thead>
<tbody>
    <?php $result = DataHandler::queryProjects();
    $count = 0;
    while($row = $result->fetch_assoc()):
        $count++;
        $latest_commit = new DateTime($row['latest_commit_date'], new DateTimeZone('Asia/Ulaanbaatar'));
    ?>
        <tr id='p_<?php echo $row['id'];?>_row' class="even">
            <td id='p_<?php echo $row['id'];?>' class='p_name'><?php echo $row['name']?></td>
            <td style="vertical-align:middle;">
                <div class='timeline' style="width:<?php echo $timeline_width;?>px;">
                    <?php
                    $updated_at = new DateTime($row['updated_at'], new DateTimeZone('Asia/Ulaanbaatar'));
                    if($updated_at->getTimestamp()<=$end_date->getTimestamp() && $updated_at->getTimestamp()>=$start_date->getTimestamp()):?>
                    <div class='tick updated_at' id="tick_<?php echo $row['id'];?>_updated_at" style="left:<?php echo offset_(new DateTime($row['updated_at'], new DateTimeZone('Asia/Ulaanbaatar')))-1;?>px"><div>&nbsp;</div></div>

                    <?php endif;

                    foreach($prefixes as $prefix => $class){
                        echo '<div class="prefix_container '.$class.'">';
                        $actions[$prefix] = array();
                        $rs_deploys = DataHandler::getProjectDeployments($row['id'], $prefix, $start_date, $end_date);
                        $num_rows = $rs_deploys->num_rows;
                        $count = 0;
                        $prev_deploy_action[$prefix] = null;
                        while($action = $rs_deploys->fetch_assoc()):
                            $action['action_date'] = new DateTime($action['action_date'], new DateTimeZone('Asia/Ulaanbaatar'));
                             ?>
                            <div class='tick <?php echo $action['status']?> action' id="tick_d_<?php echo $row['id'];?>_<?php echo $action['id']?>" style="left:<?php echo offset_($action['action_date'])-1;?>px"><div>&nbsp;</div>
                            </div>
                        <?php


                        if($count == ($num_rows - 1) && $action['status'] != 'undeploy'){
                            $action['latest'] = true;
                        } else {
                            $action['latest'] = false;
                        }

                        if($action['status']=='undeploy' || $action['status']=='redeploy'){
                            $prev_deploy_action[$prefix]['until'] = $action['action_date'];
                            $prev_deploy_action[$prefix]['unending'] = false;
                            $actions[$prefix][$prev_deploy_action[$prefix]['rvn']] = $prev_deploy_action[$prefix];
                        }

                        //if($action['status']!='undeploy'){
                            $action['until'] = $end_date;
                            $action['unending'] = true;
                            $prev_deploy_action[$prefix] = $action;
                            $actions[$prefix][$action['rvn'].($action['status']=='undeploy'?'_u':'')] = $action;
                        //}
                        $count++;
                        endwhile;
                        echo '</div>';
                    }
                    $rs_history = DataHandler::getProjectHistory($row['id'], $start_date, $end_date);
                    if($latest_commit != null && $latest_commit->getTimestamp()> $start_date->getTimestamp()){
                        if($latest_commit->getTimestamp() > $end_date->getTimestamp()){
                            $latest_commit = clone $end_date;
                        }

                        $initial_commit = clone $start_date;
                        if($row['initial_commit']!=''){
                            $initial_commit = new DateTime($row['initial_commit'], new DateTimeZone('Asia/Ulaanbaatar'));
                            if($initial_commit->getTimestamp() < $start_date->getTimestamp()){
                                $initial_commit = clone $start_date;
                            }
                        }

                        if($initial_commit->getTimestamp() < $end_date->getTimestamp() && $latest_commit->getTimestamp() > $start_date->getTimestamp()){
                            echo '<div class="bar commit" style="left:'.offset_($initial_commit).'px;width:'.width_($initial_commit, $latest_commit).'px">&nbsp;</div>';
                        }

                    }
                    $history = array();
                    $prefix_lines = array();
                    while($log = $rs_history->fetch_assoc()):
                        $log['datetime'] = new DateTime($log['datetime'], new DateTimeZone('Asia/Ulaanbaatar'));
                        array_push($history, $log);
                         ?>
                        <div class='tick commit' id="tick_<?php echo $row['id'];?>_<?php echo $log['r']?>" style="left:<?php echo offset_($log['datetime'])-1;?>px"><div>&nbsp;</div>
                        </div>
                    <?php

                    foreach($prefixes as $prefix => $class){
                        if(array_key_exists($log['r'], $actions[$prefix])){
                            if(!isset($prefix_lines[$prefix])){
                                $prefix_lines[$prefix] = '';
                            }
                            $prefix_lines[$prefix] .= '<div class="bar deployed'.($actions[$prefix][$log['r']]['latest']?' tick_d_'.$row['id'].'_current current':'').'" id="tick_d_'.$row['id'].'_'.$actions[$prefix][$log['r']]['id'].'_commit" style="left:'.offset_($initial_commit).'px;width:'.width_($initial_commit, $log['datetime']).'px;'.($actions[$prefix][$log['r']]['latest']?'':'display:none;').'">&nbsp;</div>';
                            if($actions[$prefix][$log['r']]['status'] != 'undeploy'){
                                $prefix_lines[$prefix] .= '<div class="line'.($actions[$prefix][$log['r']]['latest']?' current':'').($actions[$prefix][$log['r']]['unending']?' unending':'').'" id="tick_d_'.$row['id'].'_'.$actions[$prefix][$log['r']]['id'].'_active" style="left:'.offset_($actions[$prefix][$log['r']]['action_date']).'px;width:'.width_($actions[$prefix][$log['r']]['action_date'], $actions[$prefix][$log['r']]['until']).'px;"><div></div></div>';
                            }
                        }
                    }
                    endwhile;

                    foreach($prefixes as $prefix => $class){
                        if(isset($prefix_lines[$prefix])){
                            echo '<div class="prefix_container '.$class.'">'.$prefix_lines[$prefix].'</div>';
                        }
                    }
                    ?>
                </div>
                <?php foreach($history as $log):?>
                <div style="display:none;" class='tick_info' id="tick_<?php echo $row['id'];?>_<?php echo $log['r']?>_info" >
                    <label>date:</label> <strong><?php echo $log['datetime']->format('Y/m/d H:i:s')?></strong><br/>
                    <label>rev.</label> <strong><?php echo $log['r']?></strong>, <label>commit by</label> <strong><?php echo $log['username']?></strong></br>
                    <label>comments:</label><br/>
                    <?php echo str_replace("\n", '</br>', $log['comments'])?>
                </div>
                <?php endforeach;

                    foreach($prefixes as $prefix => $class){
                    foreach($actions[$prefix] as $action):?>
                    <div style="display:none;" class='tick_info' id="tick_d_<?php echo $row['id'];?>_<?php echo $action['id']?>_info" >
                        <label>date:</label> <strong><?php echo $action['action_date']->format('Y/m/d H:i:s')?></strong><br/>
                    <?php if($action['status']!='undeploy'):?>
                        <label>until:</label> <strong><?php echo ($action['until']->getTimestamp() == $end_date->getTimestamp()?'':$action['until']->format('Y/m/d H:i:s'))?></strong><br/>
                    <?php endif; ?>
                        <label>action:</label> <strong class="<?php echo $action['status']?>"><?php echo $action['status']?></strong><br/>
                        <label>rev.</label> <strong><?php echo $action['rvn']?></strong></br>
                        <label>comments:</label><br/>
                        <?php echo str_replace("\n", '</br>', $action['comments'])?>
                    </div>
                    <?php endforeach;
                }
                ?>
                <!--
                <table class='data-table'>
                <thead>
                    <tr>
                        <th>rev.</th>
                        <th>user</th>
                        <th>commit datetime</th>
                        <th>comments</th>
                    </tr>
                </thead>
                    <tbody><?php /*foreach($history as $log):?>
                    <tr>
                        <td><?php echo $log['r']?></td>
                        <td><?php echo $log['username']?></td>
                        <td><?php echo $log['datetime']->format('Y/m/d H:i')?></td>
                        <td><?php echo str_replace("\n", '</br>', $log['comments'])?></td>
                    </tr>
                    <?php endforeach; */
                    ?></tbody>
                </table>-->
            </td>
            <td>
                <a href="rvn.history.php?id=<?php echo $row['id']?>" class='button blue'>detailed</a>
                <a href="javascript:void(0);" onclick="doAction(<?php echo $row['id']?>, 'update')" class='button blue'>update</a>
                <a href="javascript:void(0);" onclick="doAction(<?php echo $row['id']?>, 'deploy')" class='button green'>deploy</a>
                <a href="javascript:void(0);" onclick="doAction(<?php echo $row['id']?>, 'redeploy')" class='button green'>redeploy</a>
                <a href="javascript:void(0);" onclick="doAction(<?php echo $row['id']?>, 'undeploy_deploy')" class='button orange'>undeploy deploy</a>
                <a href="javascript:void(0);" onclick="doAction(<?php echo $row['id']?>, 'undeploy')" class='button red'>undeploy</a>
            </td>
        </tr>
    <?php endwhile; $result->close(); DataHandler::close();?>
</tbody>
</table>
<script>
$(document).ready(function() {
    $('.tick').hover(function(event) {
            $('#'+this.id+'_info').css({top: event.clientY+30, left: event.clientX - 10}).show();
            $(this).addClass('highlighted');
            //$('#'+this.id).css({'z-index':99});
        }, function() {
            $('#'+this.id+'_info').hide();
            $(this).removeClass('highlighted');
            //$('#'+this.id).css({'z-index':''});
        });

    $('.tick.action').hover(function(event) {
            var current_row_id = this.id.split('_')[2];

            $('.tick_d_'+current_row_id+'_current').hide();
            $('#'+this.id+'_commit').show();
            $('#'+this.id+'_commit').addClass('highlighted');
            $('#'+this.id+'_active').addClass('highlighted');
            //$('#'+this.id).css({'z-index':99});
        }, function() {
            var current_row_id = this.id.split('_')[2];
            $('#'+this.id+'_commit').hide();
            $('#'+this.id+'_commit').removeClass('highlighted');
            $('#'+this.id+'_active').removeClass('highlighted');
            $('.tick_d_'+current_row_id+'_current').show();
            //$('#'+this.id).css({'z-index':''});
        });



    $('.line').hover(function(event) {
            var current_row_id = this.id.split('_')[2];
            var deploy_id = this.id.substring(0, this.id.length - "_active".length);
            $('.tick_d_'+current_row_id+'_current').hide();
            $('#'+deploy_id+'_commit').show();
            $('#'+deploy_id).addClass('highlighted');
            $('#'+deploy_id+'_commit').addClass('highlighted');
            $('#'+deploy_id+'_active').addClass('highlighted');
            $('#'+deploy_id+'_info').css({top: event.clientY+3, left: event.clientX}).show();
            //$('#'+this.id).css({'z-index':99});
        }, function() {
            var current_row_id = this.id.split('_')[2];
            var deploy_id = this.id.substring(0, this.id.length - "_active".length);
            $('#'+deploy_id+'_commit').hide();
            $('#'+deploy_id).removeClass('highlighted');
            $('#'+deploy_id+'_commit').removeClass('highlighted');
            $('#'+deploy_id+'_active').removeClass('highlighted');
            $('#'+deploy_id+'_info').hide();
            $('.tick_d_'+current_row_id+'_current').show();
            //$('#'+this.id).css({'z-index':''});
        });
});
</script>
<?php include_once("_log.php"); ?>
</body>
</html>
