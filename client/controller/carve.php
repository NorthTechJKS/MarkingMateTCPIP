<?
class Carve extends Controller
{
	public $address;
	public $port;

	
	function __construct()
	{
		parent::__construct();
		require_once 'model/carve_model.php';
	}
	
	//index
	function index()
	{
		$this->lists();
	}
	
	//lists : 리스트 출력
	function lists()
	{		    
		$this->view->search = $this->getRequest();
		$this->view->data = Carve_Model::selectList($this->view->search);
		$this->view->select['products'] = Carve_Model::selectListProducts();
		$this->view->select['equipments'] = Carve_Model::selectListEquipments();
    $this->view->render('template/header');
		$this->view->render('carve/app');
    $this->view->render('template/footer');
	}
	
	//detail : 상세정보
	function carve()
	{    
		$data = $this->getRequest();
		$data['carve_code'] = $data['message']; // 각인코드 자동생성

    $data1 = $this->sendMessage("ARC TEXT_NAME", $data['text_name'], $data);
    $data2 = $this->sendMessage("TEXT_CONTENT", $data['message'], $data);
    $data3 = $this->sendMessage("E", "", $data);
    $data4 = $this->sendMessage("END", "", $data);
		if($data1 != "접속실패!")
		{
			Carve_Model::updateRowMeasureLog($data);
			Carve_Model::updateRowWorkLoad($data);
		} 
		echo json_encode($data1, JSON_UNESCAPED_UNICODE);
	}
	
	// getGrade 선택한 제품과 측정로그를 참조해 등급을 판별한다.
	function getGrades()
	{
		$data = $this->getRequest();		
		$this->view->data = Carve_Model::selectListGrades($data);		
		echo json_encode($this->view->data, JSON_UNESCAPED_UNICODE);
	}

	function setGrades()
	{
		$data = $this->getRequest();		
		Carve_Model::deleteListGrades($data);		//기존의 제품 등급정보 삭제
		foreach($data['measure_grades'] as $measure_grade)
		{
			$measure_grade['product_id'] = $data['product_id'];
			$measure_grade['insert_user_id'] = $data['insert_user_id'];
			Carve_Model::insertListGrades($measure_grade);		//제품 등급정보 새로 삽입
		}		
		echo json_encode($this->view->data, JSON_UNESCAPED_UNICODE);
	}

	
	// getEquipment 선택한 제품과 측정로그를 참조해 등급을 판별한다.
	function getEquipment()
	{
		$data = $this->getRequest();		
		$this->view->data = Carve_Model::selectRowEquipment($data['equipment_id']);		
		echo json_encode($this->view->data, JSON_UNESCAPED_UNICODE);
	}

	function setEquipment()
	{
		$data = $this->getRequest();		
		Carve_Model::updateRowEquipmentTransferFlag($data);		//기존의 설비 전송정보 수정
		echo json_encode($this->view->data, JSON_UNESCAPED_UNICODE);
	}


	// getGrade 선택한 제품과 측정로그를 참조해 각인코드를 생성한다.한다.
	function getCarveCode()
	{
		$data = $this->getRequest();		
		$product_id = $data['product_id'];
		$measure_log_id = $data['measure_log_id'];
		$product = Carve_Model::selectRowProduct($product_id);
		$measure_log = Carve_Model::selectRow($measure_log_id);
		$grade = Carve_Model::selectRowGrade($product_id, $measure_log['hz']);
		$carve_code = "";


		$carve_code .= $product['name_en'];
		$carve_code .= date("y");
		$carve_code .= $this->getMonth(date("m"));
		$carve_code .= $this->getWeek(date("Y-m-d"));		
		$carve_code .= $grade['grade'];

		//$carve_code = "ABCD-0001";

		//DB를 이용할지 자체 게산식을 사용할지는 추후 적용
		$this->view->data = array(
			'carve_code' => $carve_code,
			'grade' => $grade['grade'],
			'grade_product_id' => $grade['grade_product_id']
		);
		echo json_encode($this->view->data, JSON_UNESCAPED_UNICODE);
	}

	// 현재 월에 따른 코드값을 얻어온다.
	function getMonth($month)
	{
		$code = "X";

		switch($month)
		{
			case '01':
				$code = '1';
				break;

			case '02':
				$code = '2';
				break;

			case '03':
				$code = '3';
				break;
					
			case '04':
				$code = '4';
				break;
				
			case '05':
				$code = '5';
				break;
				
			case '06':
				$code = '6';
				break;
				
			case '07':
				$code = '7';
				break;
				
			case '08':
				$code = '8';
				break;
				
			case '09':
				$code = '9';
				break;
				
			case '10':
				$code = '0';
				break;
				
			case '11':
				$code = 'A';
				break;
				
			case '12':
				$code = 'B';
				break;
			
			default :			
				$code = 'X';
				break;
		}
		
		return $code;
	}

	// 이번달이 몇주차인지 구한다.
	function getWeek($date_str) 
  { 	
    //한국 정서인지 교회정서인지는 모르겠지만 일요일부터 시작해야하기 때문에 +1 days(기본값은 월요일부터)
    $date = date("Y-m-d", strtotime("+1 days", strtotime($date_str)));
    
    //전체(년) 기준의 오늘이 몇째주 인지( ex 38째주 )
    $now_date = date("W", strtotime($date));
    
    //지난달 마지막 날짜가 몇째주 인지 (원래 -1을 하는게 맞는데, 일요일을 기준으로 하기 때문에 1일이 지난달 마지막 기준)
    $prev_date = date("W", strtotime(date("Y-m-01", strtotime($date_str))));
    return $now_date-$prev_date+1;
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
	function transfer($data)
	{
    set_time_limit(2); 
    $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 1, 'usec' => 0));
		socket_set_option($sock, SOL_SOCKET, SO_SNDTIMEO, array('sec' => 1, 'usec' => 0));
		
		if(!@socket_connect($sock, $data['address'], $data['port']))
		{
			$sMsg = "접속실패!";  
			return $sMsg;
		}
    
		
    // 사용자의 명령어를 입력받습니다. 
    // time 또는 quit 메시지 말고는 무시 합니다. 
		$sendMessage = $data['message'];
		if(!@socket_write($sock, $sendMessage))
		{
			$sMsg = "접속실패!";  
			return $sMsg;
		}
		if(!@$sMsg = socket_read($sock, 4096))
		{
			$sMsg = "접속실패!";  
			return $sMsg;
		}
		
		socket_close($sock);
		$sMsg = "각인 요청 성공";
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
	
	function sendMessage($command, $message, $data)
	{
		$data['message'] = "{$command}[{$message}]";
		return $this->transfer($data);
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
		$this->view->search = $this->getRequest();		
		$this->view->data = Carve_Model::selectList($this->view->search);		
		echo json_encode($this->view->data, JSON_UNESCAPED_UNICODE);
	}

	//loadList 목록 불러오기
	function loadListAdvanced()
	{			
	}
	
	//REQUEST값 일괄 불러오기
	function getRequest()
	{
			$measure_log_id = isset($_REQUEST['measure_log_id'])? $_REQUEST['measure_log_id'] : "0";
			$product_id = isset($_REQUEST['product_id'])? $_REQUEST['product_id'] : "0";
			$grade_product_id = isset($_REQUEST['grade_product_id'])? $_REQUEST['grade_product_id'] : "0";
			$measure_grades = isset($_REQUEST['measure_grades'])? $_REQUEST['measure_grades'] : array();
			$insert_user_id = $this->view->session->get("user_id");
			$update_user_id = $this->view->session->get("user_id");
			$response_message = isset($_REQUEST['response_message'])? $_REQUEST['response_message'] : '';
			$message = isset($_REQUEST['message'])? $_REQUEST['message'] : '';
			$grade	 = isset($_REQUEST['grade'])? $_REQUEST['grade'] : '';
			$address = isset($_REQUEST['address'])? $_REQUEST['address'] : "192.168.0.210";
			$port		 = isset($_REQUEST['port'])? $_REQUEST['port'] : "8999";
			$text_name = isset($_REQUEST['text_name'])? $_REQUEST['text_name'] : "1";

			
			$equipment_id = isset($_REQUEST['equipment_id'])? $_REQUEST['equipment_id'] : "0";
			$transfer_flag = isset($_REQUEST['transfer_flag'])? $_REQUEST['transfer_flag'] : "0";


			$searchdate = isset($_REQUEST['searchdate']) ? $_REQUEST['searchdate'] : date("Y-m-d");
			$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : "0";
			$perPage = 30; // 한번에 로드되는 row수
			$start = $page * $perPage; //데이터 로드 시작 row지점

			$data = array(
				'measure_log_id' => $measure_log_id,
				'product_id'	   => $product_id,
				'grade_product_id' => $grade_product_id,
				'measure_grades' => $measure_grades,
				'insert_user_id' => $insert_user_id,
				'update_user_id' => $update_user_id,
				'response_message'=> $response_message,
				'message'			   => $message,
				'grade'				   => $grade,
				'address'			   => $address,
				'port'				   => $port,
				'text_name'		   => $text_name,
				'equipment_id'	   => $equipment_id,
				'transfer_flag'	   => $transfer_flag,
				'searchdate' => $searchdate,
				'perPage' => $perPage,
				'start' => $start		
			);			

			return $data;
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

		//사용자ID
		if($data['product_id'] == "") 
		{
			$result['error'] = 1;
			$result['error_message'] = "제품을 선택하세요."; // 에러메세지
			return $result;
		} 

		//요청메세지
		if($data['message'] == "") 
		{
			$result['error'] = 1;
			$result['error_message'] = "요청메세지를 입력하세요."; // 에러메세지
			return $result;
		}

		//주소
		if($data['address'] == "") 
		{
			$result['error'] = 1;
			$result['error_message'] = "주소를 입력하세요."; // 에러메세지
			return $result;
		}
		
		//포트
		if($data['port'] == "") 
		{
			$result['error'] = 1;
			$result['error_message'] = "포트를 입력하세요."; // 에러메세지
			return $result;
		}

		//객체
		if($data['text_name'] == "") 
		{
			$result['error'] = 1;
			$result['error_message'] = "객체를 입력하세요."; // 에러메세지
			return $result;
		}
		return $result;
	}

}