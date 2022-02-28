#!/usr/bin/php -q 
<?
  //PHP Socket 서버 연결확인용
  //테스트 클라이언트 프로그램
  $sockIp = "127.0.0.1"; 
  $sockPort = "8889"; 
  
  echo "CLIENT >> socket connecting...\n";
  $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
  socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 1, 'usec' => 0));
  socket_set_option($sock, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 1, 'usec' => 0));
  socket_connect($sock, $sockIp, $sockPort);
  echo "CLIENT >> socket connect to ".$sockIp.":".$sockPort."\n";
    
    msg("Enter command : \n"); 
    // 사용자의 명령어를 입력받습니다. 
    $stdin = "test date : ".date("Y-m-d")."\n"; 
    //$stdin = preg_replace("/\n|\r/", "", read_data()); 
    // time 또는 quit 메시지 말고는 무시 합니다. 
    msg("Input command : ".$stdin."\n"); 
    //$cData = chr(0x02).$stdin.chr(0x03);     
    $cData = "\x02".$stdin."\x03"; 
    socket_write($sock, $cData, strlen($cData)); 
    $sData = "";
    //socket_recv($sock, $sData, 4096, MSG_WAITALL);
    
    $sData = socket_read($sock, 4096);
    echo "SERVER >>$sData\n";
    $sStr = str_split($sData);
    foreach($sStr AS $chr)
    {
      echo "SERVER >>". ord($chr)  ."\n";
    }
    
  socket_close($sock);
  echo "CLIENT >> socket closed. \n"; 
  
  // 표준입력을 받아 값을 리턴하는 함수 
  function read_data() 
  { 
    $in = fopen("php://stdin", "r"); 
    $in_string = fgets($in, 255); 
    fclose($in); 
    return $in_string; 
  } 
  
  // 로그를 출력합니다. 디버그용 d
  function msg($msg) 
  { 
    echo "CLIENT >> ".$msg; 
  } 
 ?>
