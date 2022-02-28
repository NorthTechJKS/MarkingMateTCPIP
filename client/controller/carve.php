<?
class Carve extends Controller
{
	public $address;
	public $port;

	
	function __construct()
	{
		parent::__construct();
		$this->address = "127.0.0.1";
		$this->port = "8889";
	}
	
	//index
	function index()
	{
		$this->lists();
	}
	
	//lists : 리스트 출력
	function lists()
	{		    
    $this->view->render('template/header');
		$this->view->render('carve/app');
    $this->view->render('template/footer');
	}
	
	//detail : 상세정보
	function carve()
	{    
		$message = isset($_REQUEST['message'])? $_REQUEST['message'] : '1000';
		$this->address = isset($_REQUEST['address'])? $_REQUEST['address'] : "127.0.0.1";
		$this->port		 = isset($_REQUEST['port'])? $_REQUEST['port'] : "8889";

    $data = array(
      'errorCode' => "000",
      'message' => "전송 성공"
    );
    $data1 = $this->transfer($message);
		echo json_encode($data1, JSON_UNESCAPED_UNICODE);
	}
		
	//transfer : server와 연결
	function transfer($message)
	{
    set_time_limit(2); 
    $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 1, 'usec' => 0));
		socket_set_option($sock, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 1, 'usec' => 0));
		
		if(!@socket_connect($sock, $this->address, $this->port))
		{
			$sMsg = "접속실패!";  
		}

    // 사용자의 명령어를 입력받습니다. 
    // time 또는 quit 메시지 말고는 무시 합니다. 
		$sendMessage = $message;
		if(!@socket_write($sock, $sendMessage))
		{
			$sMsg = "접속실패!";  
		}
		if(!@$sMsg = socket_read($sock, 4096))
		{
			$sMsg = "접속실패!";  
		}
		
		socket_close($sock);
		return $sMsg;
	}
}