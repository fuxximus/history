<?php
class SvnWrapper{
    public static $history = array();
    public static function setup(){
    }

    public static function doSomething(){
        echo "doing something!";
    }

    public static function getFirstLog($repo, $path){
      $request =
          '<?xml version="1.0"?>'.
            '<S:log-report xmlns:S="svn:">'.
              '<S:start-revision>1</S:start-revision>'.
              '<S:limit>1</S:limit>'.
            '</S:log-report>';
      $errno = "";
      $errstr = "";
      $xml = self::svn($request, $repo, $path, $errno, $errstr);

      $row = array();
      $log_items = $xml->xpath('//S:log-report/S:log-item');
      $log = self::parseLog($log_items);
      return $log;
    }

    public static function parseLog(&$log_items){
      $log = false;
      $log_item = current($log_items);
      if(is_object($log_item)){
        $log['datetime'] = new DateTime($log_item->xpath('S:date')[0]);
        $log['comments'] = $log_item->xpath('D:comment')[0];
        $log['username'] = $log_item->xpath('D:creator-displayname')[0];
        $log['r'] = $log_item->xpath('D:version-name')[0];
        next($log_items);
      } else {
        return false;
      }
      return $log;
    }

    public static function getHistoryFromRvnRows($repo, $path, $start_rvn, $orderBy = 'DESC'){
      $from = $start_rvn;
      $to = '-1';//date('Y-m-dTH:i:s.u');//2006-02-27T18:44:26.149336Z
      $request =
          '<?xml version="1.0"?>'.
            '<S:log-report xmlns:S="svn:">'.
              '<S:start-revision>'.($orderBy=='DESC'?$to:$from).'</S:start-revision>'.
              '<S:end-revision>'.($orderBy=='DESC'?$from:$to).'</S:end-revision>'.
              //'<S:discover-changed-paths/>'.
            '</S:log-report>';
      $errno = "";
      $errstr = "";
      $xml = self::svn($request, $repo, $path, $errno, $errstr);


      if(is_array($error = @$xml->xpath('//D:error/m:human-readable'))&&$error[0]['errcode']=='160006'){
        echo 'no updates: ['.$error[0]['errcode'].'] '.$error[0];
        return array();
      }
      // echo '<pre>';
      // echo htmlspecialchars($request)."\n\n";
      // echo htmlspecialchars($xml->asXML());
      // echo '</pre>';
      $rows = $xml->xpath('//S:log-report/S:log-item');
      return $rows;
    }

    public static function getAllHistoryRows($repo, $path, $orderBy = 'DESC'){
      return self::getHistoryFromRvnRows($repo, $path, '1', $orderBy);
    }
    private static function svn($request, $svn_repo, $path, &$errno, &$errstr) {
      $ch = curl_Init();
      curl_setopt_array($ch, array(
        CURLINFO_HEADER_OUT => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL => $svn_repo->url.'/'.$path,
        CURLOPT_CUSTOMREQUEST => 'REPORT',
        CURLOPT_HTTPHEADER => array(
          'Content-Type: text/xml'
        ),
        CURLOPT_USERPWD => $svn_repo->user.':'.$svn_repo->pass,
        CURLOPT_POSTFIELDS => $request
      ));

      $str = curl_exec($ch);
      $info = curl_getinfo($ch);

      // echo '<pre>';
      // print_r($info['request_header']);
      // print_r(htmlspecialchars($request));
      // echo "\n\n";
      // echo "\n\n";
      // echo htmlspecialchars($str);
      // echo '</pre>';

      $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if($httpcode != 200 && $httpcode != 400){
        return;
      }
      curl_close($ch);
      return simplexml_load_string($str);
    }
}

class SvnRepo{
    public $url;
    public $user;
    public $pass;
    function __construct($url, $user, $pass){
        $this->url = $url;
        $this->user = $user;
        $this->pass = $pass;
    }

    function getHost(){
      $var = parse_url($this->url);
      return $var['host'];
    }
    function getPath(){
      $var = parse_url($this->url);
      return $var['path'];
    }
}
