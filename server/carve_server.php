#!/usr/bin/php -q 
<?
  set_time_limit(0);
  define("_IP", "211.37.179.64");
  define("_PORT", "18889");
  define("_TIMEOUT", 10);
  $start_flag = 0;
  $cSock = array();
  $cInfo = array();
  $sSock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
  msg("CREATE CLIENT SOCKET");
  socket_setopt($sSock, SOL_SOCKET, SO_REUSEADDR, 1);
  socket_bind($sSock, _IP, _PORT);
  msg("BIND CLIENT SOCKET");
  socket_listen($sSock);
  msg("LISTEN CLIENT SOCKET");
  while(1)
  {
    $sockArr = array_merge(array($sSock), $cSock);
    $tWrite = NULL;
    $tExcept = NULL;
    if(socket_select($sockArr, $tWrite, $tExcept, _TIMEOUT) > 0)
    {
      foreach($sockArr as $key => $sock)
      {
        // Listen 하고 있는 서버 소켓일 경우
        // 새로운 클라이언트의 접속을 의미
        if($sock == $sSock)
        {
          if(sizeof($cSock) == 0)
          {
            $cSock = array();
            $cInfo = array();
            $start_flag = 0;
            msg("RESET MARKINGMATE CLIENT SOCKET");
          }
          $tSock = socket_accept($sSock);
          socket_getpeername($tSock, $sockIp, $sockPort);
          $cSock[(string)$tSock] = $tSock;
          $cInfo[(string)$tSock] = array('ip'=>$sockIp, 'port'=>$sockPort);
          msg("client connect : ".$sockIp.":".$sockPort);
          print_r($cSock);
          msg("reset flag : $start_flag");
          
          // 최초접속한 클라이언트를 MarkingMate 프로그램으로 분류해 따로 관리        
          if($start_flag == 0)
          {
            $msg = "sucess connect markingmate".chr(13).chr(10);
            msg($msg);
            $mmSock = $tSock;
            socket_write($cSock[(string)$tSock] , $msg, strlen($msg)); 
            $start_flag = 1;          
            //showAscii($msg);         
          }
        }
        // 클라이언트 접속해 있는 소켓중 하나일경우
        // 해당 클라이언트에서 이벤트가 발생함을 의미
        else
        {
          $buf = @socket_read($sock, 4096);
          // 접속 종료
          if(!$buf)
          {
            exceptSocket($cSock, $cInfo, $sock);
            msg("client connection broken : ".$sockIp.":".$sockPort);
          }
          // 메시지 수신 이벤트
          else
          {
            msg("recive data : ".$buf);
            $buf = $buf.chr(13).chr(10);
            $thisSockInfo = $cInfo[(string)$sock];
            //showAscii($buf);         
            if($sock != $mmSock)
            {
              socket_write($sock, $buf.chr(13).chr(10), strlen($buf)); 
              socket_write($mmSock, $buf.chr(13).chr(10), strlen($buf)); 
              msg("SUCCESS SEND CLIENT SOCKET CLOSE \n"); 
              
              socket_close($sock);
              exceptSocket($cSock, $cInfo, $sock);
            }
          }
        }
      }
    }
  }
  
  function exceptSocket(&$sockSet, &$infoSet, $sock)
  {
    unset($sockSet[(string)$sock]);
    unset($infoSet[(string)$sock]);
    // array_merge 함수에서 error 발생을 막기위한 처리
    if(count($sockSet)==0)
    {
      $sockSet = array();
      $infoSet = array();
    }
  } 
  
  // 문자열을 아스키코드로 보여준다.
  function showAscii($str)
  {
    $sStr = str_split($str);
    $returStr = "";
    foreach($sStr AS $chr)
    {
      $returStr .= ord($chr)." ";
    }
    $returStr .=  "\n";
    msg($returStr);
  }
  
  function msg($msg)
  {
    echo "SERVER >> $msg \n";
  }
  
    