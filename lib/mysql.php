<?php
class DataHandler{
    private static $_host;
    private static $_db;
    private static $_user;
    private static $_pass;
    private static $_conn;


    public static function setup($host, $db, $user, $pass){
        self::$_host = $host;
        self::$_db = $db;
        self::$_user = $user;
        self::$_pass = $pass;
    }
    //HISTORY ==============================================================================
    public static function getAllDeployHistory(){
        self::connect();
        return self::query('SELECT * FROM deployments ORDER BY action_date DESC');
    }

    public static function getDeployHistoryDateCounts(){
        self::connect();
        $rs = self::query('SELECT LEFT(action_date, 10) as _date, COUNT(*) AS cnt FROM deployments GROUP BY LEFT(action_date, 10) ORDER BY action_date DESC');
        $dates = array();
        while($date = $rs->fetch_assoc()){
            $dates[$date['_date']] = $date['cnt'];
        }
        return $dates;
    }

    public static function getRvnHistoryDateCounts($id){
        self::connect();
        $q = 'SELECT LEFT(commit_date, 10) as _date, COUNT(*) AS cnt FROM logs as l '.
        'INNER JOIN (SELECT CONCAT(r.path,\'/\',p.path) AS path FROM projects AS p INNER JOIN repos AS r ON p.repo = r.id WHERE p.id = '.mysqli_escape_string(self::$_conn, $id).') AS r ON r.path = l.path GROUP BY LEFT(commit_date, 10) ORDER BY commit_date DESC';
        $rs = self::query($q);
        $dates = array();
        if(!$rs){
          echo $q;
          return false;
        }
        while($date = $rs->fetch_assoc()){
            $dates[$date['_date']] = $date['cnt'];
        }
        return $dates;
    }

    //DEPLOYMENT ============================================================================

    public static function getAllAvailablePrefixes($start_date, $end_date){
        self::connect();
        $rs = self::query('SELECT CASE WHEN prefix = \'\' THEN \'_\' ELSE prefix END AS prefix FROM deployments WHERE action_date >= \''.$start_date->format('Y/m/d 00:00:00').'\' AND action_date <\''.$end_date->format('Y/m/d 00:00:00').'\' GROUP BY prefix');
        $prefixes = array();
        $count = 0;
        while($prefix = $rs->fetch_assoc()){
            $prefixes[$prefix['prefix']] = $count++;
        }
        self::close();
        return $prefixes;

    }

    public static function deleteDeployments($ids){
        self::connect();
        self::query('DELETE FROM deployments WHERE id IN (\''.implode('\',\'', $ids).'\')');
        return self::$_conn->affected_rows;
    }

    public static function insertNewDeployment($data){

        $project = self::getProject($data['id']);
        /*
        array(5) {
          ["action"]=>
          string(6) "deploy"
          ["id"]=>
          string(1) "6"
          ["rvn"]=>
          string(4) "1387"
          ["date"]=>
          string(19) "2014-02-21 05:07:06"
          ["submit"]=>
          string(6) "deploy"
        }*/

        $prepost = explode($project['name'], $data['filename']);
        $prefix = isset($prepost[0])&&$prepost[0]!=$data['filename']?trim(trim($prepost[0]),'-'):'null';
        $prefix = empty($prefix)?'_':$prefix;
        self::connect();
        self::query('INSERT INTO deployments(rvn, action_date, project_path, status, filename, prefix, version, comments)
            VALUES(\''.mysqli_escape_string(self::$_conn, $data['rvn']).'\',
                \''.mysqli_escape_string(self::$_conn, $data['action_date']).'\',
                \''.mysqli_escape_string(self::$_conn, $project['repo_path'].'/'.$project['path']).'\',
                \''.mysqli_escape_string(self::$_conn, $data['status']).'\',
                \''.mysqli_escape_string(self::$_conn, $data['filename']).'\',
                \''.mysqli_escape_string(self::$_conn, (isset($data['prefix'])?$data['prefix']:$prefix)).'\',
                \''.mysqli_escape_string(self::$_conn, (isset($data['version'])?$data['version']:(isset($prepost[1])?basename('/'.trim(trim($prepost[1]),'-'), '.'.$data['ext']):'null'))).'\',
                \''.mysqli_escape_string(self::$_conn, $data['comments']).'\' )');

        self::close();
    }

    public static function getDeployments($ids){
        self::connect();
        return self::query('SELECT * FROM deployments WHERE id IN (\''.implode('\',\'', $ids).'\')');
    }

    public static function getProjectDeployments($project_id, $prefix, $start_date, $end_date){
        self::connect();
        return self::query('SELECT * FROM deployments AS d INNER JOIN (SELECT CONCAT(r.path,\'/\',p.path) AS path FROM projects AS p INNER JOIN repos AS r ON p.repo = r.id WHERE p.id = '.mysqli_escape_string(self::$_conn, $project_id).') AS r ON r.path = d.project_path WHERE prefix = \''.mysqli_escape_string(self::$_conn, $prefix).'\' AND action_date >= \''.$start_date->format('Y/m/d 00:00:00').'\' AND action_date <\''.$end_date->format('Y/m/d 00:00:00').'\' ORDER BY d.action_date ASC');
    }

    public static function getLatestDeployments($project_id){
        self::connect();
        return self::query('
SELECT * FROM deployments AS d
WHERE id IN (SELECT MAX(id) FROM deployments AS d INNER JOIN
(SELECT CONCAT(r.path,\'/\',p.path) AS path FROM projects AS p INNER JOIN repos AS r ON p.repo = r.id WHERE p.id = '.mysqli_escape_string(self::$_conn, $project_id).') AS r
ON r.path = d.project_path GROUP BY d.prefix) AND (d.status = \'deploy\' OR d.status = \'redeploy\') ');
    }

    public static function getDeployment($id){
        self::connect();
        $rs = self::query('SELECT * FROM deployments WHERE id = '.mysqli_escape_string(self::$_conn, $id));
        return $rs->fetch_assoc();
    }



    //PROJECTS ==========================================================================================================================================================================\

    public static function getProjectAllHistory($id){
        self::connect();
        return self::query('SELECT l.* FROM logs AS l INNER JOIN (SELECT CONCAT(r.path,\'/\',p.path) AS path FROM projects AS p INNER JOIN repos AS r ON p.repo = r.id WHERE p.id = '.mysqli_escape_string(self::$_conn, $id).') AS r ON r.path = l.path ORDER BY l.rvn DESC');
    }

    public static function getProjectHistory($id, $start_date, $end_date){
        self::connect();
        return self::query('SELECT l.rvn AS r, l.comments, l.username, l.commit_date AS `datetime` FROM logs AS l INNER JOIN (SELECT CONCAT(r.path,\'/\',p.path) AS path FROM projects AS p INNER JOIN repos AS r ON p.repo = r.id WHERE p.id = '.mysqli_escape_string(self::$_conn, $id).') AS r ON r.path = l.path WHERE l.commit_date >=\''.$start_date->format('Y/m/d 00:00:00').'\' AND l.commit_date <\''.$end_date->format('Y/m/d 00:00:00').'\'');
    }

    public static function updateProjectLog($id){
        $project = self::getProject($id);
        $sql = 'INSERT INTO logs(path, rvn, username, commit_date, comments) VALUES';
        $rows = SvnWrapper::getHistoryFromRvnRows(new SvnRepo($project['repo_path'], $project['repo_user'], $project['repo_pass']), $project['path'], $project['latest_rvn']+1 ,'ASC');
        $first = true;
        $project['latest_commit_date'] = new DateTime( $project['latest_commit_date'], new DateTimeZone('Asia/Ulaanbaatar'));
        while($log = SvnWrapper::parseLog($rows)){
            if(!$first){
                $sql.=','."\n";
            } else {
                $first = false;
            }
            $sql.='(\''.mysqli_escape_string(self::$_conn, $project['repo_path'].'/'.$project['path']).'\',\''.
              mysqli_escape_string(self::$_conn, $log['r']).'\',\''.
              mysqli_escape_string(self::$_conn, $log['username']).'\',\''.
              mysqli_escape_string(self::$_conn, $log['datetime']->format('Y-m-d H:i:s')).'\',\''.
              mysqli_escape_string(self::$_conn, $log['comments']).'\')';
            $project['latest_rvn'] = $log['r'];
            $project['latest_commit_date'] = $log['datetime'];
        }
        $sql.='';
        if(!$first) { self::query($sql); }


        return self::updateProject($project);

    }

    public static function getProject($id){
        self::connect();
        $rs = self::query('SELECT p.*, r.path AS repo_path, r.svn_user AS repo_user, r.svn_pass AS repo_pass FROM projects p LEFT JOIN repos r ON p.repo = r.id WHERE p.id = '.mysqli_escape_string(self::$_conn, $id).' ORDER BY p.name');
        return $rs->fetch_assoc();
    }

    public static function queryProjects(){
        self::connect();
        return self::query('SELECT p.*, r.path AS repo_path, r.svn_user AS repo_user, r.svn_pass AS repo_pass FROM projects p LEFT JOIN repos r ON p.repo = r.id ORDER BY p.name');
    }

    public static function saveProject($vals){
        if(!isset($vals['path']) ||
            $vals['path']=='' ||
            !isset($vals['name']) ||
            $vals['name']=='' ||
            !isset($vals['repo']) ||
            $vals['repo']==''){
            return 'cannot be empty';
        }

        self::connect();
        $rs = self::query('SELECT * FROM repos WHERE id = \''.mysqli_escape_string(self::$_conn, $vals['repo']).'\'');
        if($rs->num_rows == 0){
            return 'invalid repo /does not exist/';
        }
        $repo = $rs->fetch_array();
        $log = SvnWrapper::getFirstLog(new SvnRepo($repo['path'], $repo['svn_user'], $repo['svn_pass']), $vals['path']);

        // echo "Log:";
        // print_r($log);
        if($log){
          $vals['initial_commit'] = $log['datetime']->format('Y-m-d H:i:s');

          if(!(preg_match('/^[\\w\\.\\/\\-\\s]+$/', $vals['name']) > 0)){
              return 'name invalid /regex/';
          }

          if(!(preg_match('/^[\\w\\.\\/\\-]+$/', $vals['path']) > 0)){
              return 'path invalid /regex/';
          }

          self::query('DELETE FROM logs WHERE path = \''.$repo['path'].'/'.$vals['path'].'\'');

          $sql = 'INSERT INTO logs(path, rvn, username, commit_date, comments) VALUES';
          $rows = SvnWrapper::getAllHistoryRows(new SvnRepo($repo['path'], $repo['svn_user'], $repo['svn_pass']), $vals['path'], 'ASC');
          $first = true;
          $vals['latest_rvn'] = 0;
          $vals['latest_commit_date'] = null;
          while($log = SvnWrapper::parseLog($rows)){
              if(!$first){
                  $sql.=','."\n";
              } else {
                  $first = false;
              }
              $sql.='(\''.mysqli_real_escape_string(self::$_conn, $repo['path'].'/'.$vals['path']).'\',\''.
                mysqli_real_escape_string(self::$_conn,$log['r']).'\',\''.
                mysqli_real_escape_string(self::$_conn,$log['username']).'\',\''.
                mysqli_real_escape_string(self::$_conn,$log['datetime']->format('Y-m-d H:i:s')).'\',\''.
                mysqli_real_escape_string(self::$_conn,$log['comments']).'\')';
              $vals['latest_rvn'] = $log['r'];
              $vals['latest_commit_date'] = $log['datetime'];
          }
          $sql.='';

          self::query($sql);


          if(isset($vals['id']) && $vals['id'] != null && $vals['id']!='' && is_numeric($vals['id'])) {
              return self::updateProject($vals);
          } else {
              return self::insertProject($vals);
          }
        }
    }

    public static function updateProject($vals){
        self::connect();

        self::query('UPDATE projects SET path = \''.mysqli_escape_string(self::$_conn, $vals['path']).'\',
            name = \''.mysqli_escape_string(self::$_conn, $vals['name']).'\',
            repo = \''.mysqli_escape_string(self::$_conn, $vals['repo']).'\',
            initial_commit = \''.mysqli_escape_string(self::$_conn, $vals['initial_commit']).'\',
            updated_at = NOW(),
            latest_rvn = \''.$vals['latest_rvn'].'\',
            latest_commit_date = \''.$vals['latest_commit_date']->format('Y-m-d H:i:s').'\'
            WHERE id = \''.mysqli_escape_string(self::$_conn, $vals['id']).'\'
            ');

        self::close();
    }

    public static function insertProject($vals){
        $error = 0;
        /*
        $tmp = $svn_local_path;
        $count = 0;
        while($error = SvnWrapper::checkout($svn_local_path, $vals['path'], $vals['svn_user'], $vals['svn_pass'])=='folder already exists'){
            $svn_local_path = $tmp.$count;
        }*/
        if($error == 0){
            self::connect();
            self::query('INSERT INTO projects(path, repo, name, initial_commit, updated_at,latest_rvn,latest_commit_date)
                VALUES(\''.mysqli_escape_string(self::$_conn, $vals['path']).'\',
                    \''.mysqli_escape_string(self::$_conn, $vals['repo']).'\',
                    \''.mysqli_escape_string(self::$_conn, $vals['name']).'\',
                    \''.mysqli_escape_string(self::$_conn, $vals['initial_commit']).'\',
                    NOW(),
                    \''.$vals['latest_rvn'].'\',
                    \''.$vals['latest_commit_date']->format('Y-m-d H:i:s').'\')');
            self::close();
        }

        return $error;
    }

    public static function deleteProjects($ids){
        $row_affected = 0;
        self::connect();
        self::query('DELETE FROM logs WHERE path IN (SELECT CONCAT(r.path,\'/\',p.path) FROM projects AS p LEFT JOIN repos AS r ON p.repo = r.id WHERE p.id IN (\''.implode('\',\'', $ids).'\') GROUP BY p.id, r.id  )');
        $row_affected += self::$_conn->affected_rows;
        self::query('DELETE FROM projects WHERE id IN (\''.implode('\',\'', $ids).'\')');
        return $row_affected + self::$_conn->affected_rows;

    }


    //REPO ==============================================================================================================================================================================
    public static function queryRepos(){
        self::connect();
        return self::query('SELECT * FROM repos');
    }

    public static function getRepoProjectIds($repo_ids){
        self::connect();
        $ids = array();
        $rs = self::query('SELECT id FROM projects WHERE repo IN (\''.implode('\',\'', $repo_ids).'\')');
        while($row = $rs->fetch_array(MYSQLI_NUM)){
            $ids[] = $row[0];
        }
        return $ids;
    }

    public static function deleteRvns($rvn_ids){
        self::connect();
        self::query('DELETE FROM logs WHERE id IN (\''.implode('\',\'', $rvn_ids).'\')');
        return self::$_conn->affected_rows;
    }

    public static function saveRepo($vals){

        if(!isset($vals['path']) ||
            $vals['path']=='' ||
            !isset($vals['svn_user']) ||
            $vals['svn_user']=='' ||
            !isset($vals['svn_pass']) ||
            $vals['svn_pass']==''){
            return 'cannot be empty';
        }

        if(filter_var($vals['path'], FILTER_VALIDATE_URL) === false){
            return 'path invalid /FILTER_VALIDATE_URL/';
        }

        if(!(preg_match('/^[\\w_~!@#$%^&*()_\\+=\\-<>\\?:"{}\\|[\\]\\\\;\',\\.\\/]+$/', $vals['svn_pass']) > 0)){
            return 'pass invalid /regex/';
        }


        if(!(preg_match('/^[\\w\\.]+$/', $vals['svn_user']) > 0)){
            return 'user invalid /regex/';
        }

        if(isset($vals['id']) && $vals['id'] != null && $vals['id']!='' && is_numeric($vals['id'])) {
            return self::updateRepo($vals);
        } else {
            return self::insertRepo($vals);
        }
    }

     public static function updateRepo($vals){
        self::connect();

        self::query('UPDATE repos SET path = \''.mysqli_escape_string(self::$_conn, $vals['path']).'\',
            svn_user = \''.mysqli_escape_string(self::$_conn, $vals['svn_user']).'\',
            svn_pass = \''.mysqli_escape_string(self::$_conn, $vals['svn_pass']).'\'
        WHERE id = \''.mysqli_escape_string(self::$_conn, $vals['id']).'\'
            ');

        self::close();
    }

    public static function insertRepo($vals){
        $error = 0;
        /*
        $tmp = $svn_local_path;
        $count = 0;
        while($error = SvnWrapper::checkout($svn_local_path, $vals['path'], $vals['svn_user'], $vals['svn_pass'])=='folder already exists'){
            $svn_local_path = $tmp.$count;
        }*/
        if($error == 0){
            self::connect();
            self::query('INSERT INTO repos(path, svn_user, svn_pass)
                VALUES(\''.mysqli_escape_string(self::$_conn, $vals['path']).'\',
                    \''.mysqli_escape_string(self::$_conn, $vals['svn_user']).'\',
                    \''.mysqli_escape_string(self::$_conn, $vals['svn_pass']).'\')');
            self::close();
        }

        return $error;
    }

    public static function deleteRepos($ids){
        self::connect();
        self::query('DELETE FROM repos WHERE id IN (\''.implode('\',\'', $ids).'\')');
        return self::$_conn->affected_rows;
    }

    private static function connect(){
        self::$_conn = mysqli_connect(self::$_host, self::$_user, self::$_pass, self::$_db);
        mysqli_set_charset( self::$_conn , 'utf8' );
    }

    private static function query($sql){
        array_push(Logger::$history, preg_replace( '/\s+/', ' ', $sql ));
        return self::$_conn->query($sql);
    }


    public static function close(){
        mysqli_close(self::$_conn);
    }
}
