<?
class Carve extends Controller
{
	public $address;
	public $port;

	
	function __construct()
	{
		parent::__construct();
		$this->address = "192.168.40.163";
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
		$this->message = isset($_REQUEST['message'])? $_REQUEST['message'] : '1000';
		$this->address = isset($_REQUEST['address'])? $_REQUEST['address'] : "192.168.40.180";
		$this->port		 = isset($_REQUEST['port'])? $_REQUEST['port'] : "8889";
		$this->text_name = isset($_REQUEST['text_name'])? $_REQUEST['text_name'] : "1";

    $data = array(
      'errorCode' => "000",
      'message' => "전송 성공"
    );
    $data1 = $this->sendMessage("TEXT_NAME", $this->text_name);
    $data1 .= $this->sendMessage("TEXT_CONTENT", $this->message);
		echo json_encode($data1, JSON_UNESCAPED_UNICODE);
	}
	
	// //transfer : server와 연결
	// function transfer($message)
	// {
  //   set_time_limit(10); 
  //   $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
	// 	socket_bind($sock, $this->address, $this->port); 
	// 	socket_listen($sock); 
	// 	$cSock = socket_accept($sock);
  //   socket_getpeername($cSock, $sockIp, $sockPort); 
  //   // 사용자의 명령어를 입력받습니다. 
  //   // time 또는 quit 메시지 말고는 무시 합니다. 
	// 	$sendMessage = $message.chr(13).chr(10);
  //   socket_write($sock, $sendMessage); 
  //   $sMsg = socket_read($sock, 4096); 
  //   socket_close($sock);

  //   return $sMsg;
	// }
		
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

  // 표준입력을 받아 값을 리턴하는 함수 
  function read_data() 
  { 
    $in = fopen("php://stdin", "r"); 
    $in_string = fgets($in, 255); 
    fclose($in); 
    return $in_string; 
  } 
	
	function sendMessage($command, $message)
	{
		$str = "{$command}[{$message}]";
		return $this->transfer($str);
	}

	//del : user 데이터 소프트 딜리트
	function del()
	{
	}

	//delList : 선택한 항목들 일괄삭제
	function delList()
	{
	}

	//loadList 목록 불러오기
	function loadList()
	{	
	}

	//loadList 목록 불러오기
	function loadListAdvanced()
	{			
	}
	
	//REQUEST값 일괄 불러오기
	function getRequest()
	{
			$id 			      = isset($_REQUEST['id'])? $_REQUEST['id'] : '';	
			$insert_user_id = $this->view->session->get("user_id");
			$insert_date 	  = isset($_REQUEST['insert_date'])? $_REQUEST['insert_date'] : date("Y-m-d H:i:s");
			$update_user_id = $this->view->session->get("user_id");
			$update_date 	  = isset($_REQUEST['update_date'])? $_REQUEST['update_date'] : date("Y-m-d H:i:s");
			$delete_flag 	  = isset($_REQUEST['delete_flag'])? $_REQUEST['delete_flag'] : '';			
			
			$data = array(
				'id'			       => $id,
				'insert_user_id' => $insert_user_id,
				'insert_date'	   => $insert_date,
				'update_user_id' => $update_user_id,
				'update_date'	   => $update_date,
				'delete_flag'	   => $delete_flag
			);			

			return $data;
	}
	
	//REQUEST값중 검색조건값 불러오기
	function getRequestSearch()
	{			
		$searchvalue = isset($_REQUEST['searchvalue']) ? $_REQUEST['searchvalue'] : "";
		$searchtype = isset($_REQUEST['searchtype']) ? $_REQUEST['searchtype'] : "login_id";
		$category = isset($_REQUEST['category']) ? $_REQUEST['category'] : "all";
		$start_date = isset($_REQUEST['start_date']) ? $_REQUEST['start_date'] : date("Y-m-d", strtotime("-15 day"));
		$end_date = isset($_REQUEST['end_date']) ? $_REQUEST['end_date'] : date("Y-m-d", strtotime("+15 day"));
		$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : "0";
		$perPage = 30; // 한번에 로드되는 row수
		$start = $page * $perPage; //데이터 로드 시작 row지점

		switch($searchtype)
		{
			default :
				$searchwhere = 'users.login_id';
				break;				
		}

		$search = array(
			'searchvalue' => $searchvalue, 
			'searchtype' => $searchtype, 
			'searchwhere' => $searchwhere,
			'category' => $category,
			'start_date' => $start_date,
			'end_date' => $end_date,
			'perPage' => $perPage,
			'start' => $start			
		);

		return $search;
	}
	
	//입력 데이터 유효성 검사, ajax 처리
	function validate()
	{
		$data = $this->getRequest();	//request 요청으로 들어온 form 데이터 받아오기
		$arrError = $this->checkValidate($data); //request 요청으로 들어온 form 데이터 받아오기
		echo json_encode($arrError, JSON_UNESCAPED_UNICODE);
	}

	//각 항목별 입력값 유효성 체크
	function checkValidate($data)
	{
		$result = array(
				'error' => 0, //0 문제없음, 1 문제있음
				'error_message' => "" //에러메세지
		);
		//컬럼별로 예외처리항목 작성
		
		return $result;
	}
}