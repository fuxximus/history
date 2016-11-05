var toggleCheckedAll = function(chkbox, childstyle){
    $('.'+childstyle).prop('checked',chkbox.checked);
}

var deleteChecked = function(url, childstyle){
    if(confirm('Delete selected?')){
        var ids = [];
        $('.'+childstyle+':checked').each(function(){
            ids.push($(this).val());
        });

        window.open(url+ids.join(), '_blank');
    }
}

var editRepo = function(row_id){
    var id = row_id.substring(2);
    $('input[name=submit]').attr('value','save');
    console.log('#'+row_id+' td.v_path');
    $('input[name=path]').attr('value',$('#'+row_id+' td.v_path').first().text());
    $('input[name=svn_user]').attr('value',$('#'+row_id+' td.v_svn_user').first().text());
    $('input[name=svn_pass]').attr('value',$('#'+row_id+' td.v_svn_pass').first().text());
    $('input[name=id]').attr('value',id);
}


var editProject = function(row_id){
    var id = row_id.substring(2);
    $('input[name=submit]').attr('value','save');
    console.log('#'+row_id+' .v_path');
    $('input[name=path]').attr('value',$('#'+row_id+' .v_path').first().text());
    $('input[name=name]').attr('value',$('#'+row_id+' .v_name').first().text());
    $('select[name=repo]').val($('#'+row_id+' .v_repo').first().text());
    $('input[name=id]').attr('value',id);
}

var doAction = function(project_id, action){
    switch(action){
        case 'update':
            if(confirm('Update SVN log?')){
                window.open('update.php?id='+project_id,'', 'width=700, height=400, location=no, menubar=no, status=no,toolbar=no, scrollbars=no, resizable=no');
            }
        break;

        case 'deploy':
            window.open('actions/deploy.php?id='+project_id,'', 'width=700, height=400, location=no, menubar=no, status=no,toolbar=no, scrollbars=no, resizable=no');
        break;


        case 'redeploy':
            window.open('actions/deploy.php?action=redeploy&id='+project_id,'', 'width=700, height=400, location=no, menubar=no, status=no,toolbar=no, scrollbars=no, resizable=no');
        break;

        case 'undeploy_deploy':
            window.open('actions/undeploy_deploy.php?id='+project_id,'', 'width=700, height=400, location=no, menubar=no, status=no,toolbar=no, scrollbars=no, resizable=no');
        break;

        case 'undeploy':
            window.open('actions/undeploy.php?id='+project_id,'', 'width=700, height=400, location=no, menubar=no, status=no,toolbar=no, scrollbars=no, resizable=no');
        break;

    }
    return false;
}

var searchandhide = function(textbox){
    console.log($(textbox).val());
    $('.p_name').each(function(){
        console.log($(this).text());
        if($(this).text().indexOf($(textbox).val()) == -1){
            $('#'+$(this).attr('id')+'_row').hide();
        } else {
            $('#'+$(this).attr('id')+'_row').show();
        }
    })
}



$(document).ready(function() {
    $("table.data-table tr").not(':first').hover(
      function () {
        $(this).find('td').css("background","#ddd");
      },
      function () {
        $(this).find('td').css("background","");
      }
    );
});
