<script language="javascript">
  //
  //데이터목록 불러오기 + 무한스크롤링
  //
  let tb_page = 0; //로드된 스크롤 페이지 수
  let search_advanced = 0; // 상세검색 사용 유무
  $(document).ready(function() {
    let timeout;
    loadList();
    $(document).scroll(function() {
      clearTimeout(timeout);  //이전 휠 이벤트 제거
      timeout = setTimeout(function() { //다시 휠 이벤트 발생  0.15초후
      scrollDown();
      }, 150);
    });
  });


  function scrollDown() {
    let scrolltop = $(document).scrollTop();//스크롤바가 내려온 위치의 맨위
    let height = $(document).height();//스크롤을 포함한 창의 크기
    let height_win = $(window).height();//모니터에 보이는 창의 크기
    let need_heigth = height - height_win - 100;//마우스 휠 한칸이 100
    if (scrolltop >= need_heigth) {
      loadList(); //데이터 불러오기
    }    
  }

  //검색조건에 따라 값 불러오기
  function loadList() {
    let searchdate = '<?=$this->search['searchdate']?>';
      
    let page = tb_page;
    let advanced = search_advanced;

    let search_func = "loadList";
    let form_data = $("#frm-input").serializeArray();
    form_data.push({name:"searchdate", value: searchdate});
    form_data.push({name:"page", value: page});
    
    $.ajax({
      url: search_func,
      data: form_data,
      async:false,
      method: "POST",
      dataType: "json",
      success: function(data) {
        for(let rowData of data) {
          viewRowData(rowData);
        }
        tb_page++;
      },
      error: function(request, status, error) {
        console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error);
      }
    });    
  }

  //상세검색 버튼 눌렀을시 액션
  $(document).on("click", "#btn-search-advanced", function() {
      search_advanced = 1;
      tb_page = 0;
      $("#tb-list > tbody:last").empty();
      loadList(); //데이터 불러오기
    });

  //리스트에 행을 추가하고 값을 넣기
  //표시할 list 내용에 따라 수정
  function viewRowData(data) {
    let $tr = $("<tr>");

    $chk_id = $("<td>", {text:data.id});  
    $productName   = $("<td>", {text: data.product_name});
    $grade = $("<td>", {text: data.grade});
    $hz = $("<td>", {text: data.hz});
    $ohm = $("<td>", {text: data.ohm});
    $measureDate = $("<td>", {text: data.measure_date});
    carveChar = data.carve_flag == 1 ? 'Y': 'N';
    $carveFlag = $("<td>", {text: carveChar});
    $carveCode = $("<td>", {text: data.carve_code});
    $carveDate = $("<td>", {text: data.carve_date})
    $manage = $("<td>", {});
    if(data.carve_flag == 0)
    {
      $manage.append($("<input>", 
      {
        type: "button",
        param: data.id,
        value: "전송",
        class: "btn btn-sm btn-success btn-request-carve"
      }));
    }

    $tr.append($chk_id);
    $tr.append($productName);
    $tr.append($grade);
    $tr.append($hz);
    $tr.append($ohm);
    $tr.append($measureDate);
    $tr.append($carveFlag);
    $tr.append($carveCode);
    $tr.append($carveDate);
    $tr.append($manage);
    $("#tb-list > tbody:last").append($tr); //리스트페이지의 tbody 내 삽입
  }
</script>

<!-- 전송관련 시작 -->
<script language="javascript">

  //상세검색 버튼 눌렀을시 액션
  $(document).on("click", ".btn-request-carve", function() 
    {
      $(".txt-request-measure_log_id").val($(this).attr("param"));
      
      getCarveCode($(this).attr("param"));      
    }
  );

  //전송 버튼 눌렀을시 액션
  $(document).on("click", ".btn-submit-carve", function() 
    {
      
      $("#Modal-request").modal("hide");
      $.ajax({
        url: "./validate",
        data: $("#frm-input").serialize(),
        method: "POST",
        dataType: "json",
        success: function(data)
        {
          if(data.error == 0)
          {
            transferCarve();
          }
          else
          {
          //alert("error : " + data.error + ", message : " + data.error_message);
          $("#error_message").text(data.error_message);
          }
        },
        error: function(request, status, error)
        {
          alert("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" +
            error);
        }
      });
      
    }
  );

  function transferCarve()
  {
    
    $.ajax({
      url: "./carve", 
      data: $("#frm-input").serialize(),
      method: "POST",
      async:false,
      dataType: "json",
      success: function(data) 
      {
        $("#response_message").val(data);
        $("#frm-input").submit();
      },
      error: function(request, status, error) {
        console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error); 
      }
    });
  }

  function getCarveCode(measure_log_id)
  {
    $.ajax({
      url: "./getCarveCode", 
      data: {
        measure_log_id : measure_log_id,
        product_id : $(".measure_product_id").val()
      },
      method: "POST",
      async:false,
      dataType: "json",
      success: function(data) 
      {
        $(".txt-request-carve").val(data.carve_code);
        $(".txt-request-grade").val(data.grade);
        $(".txt-request-grade_product_id").val(data.grade_product_id);
        $("#Modal-request").modal("show");
        // 각인코드를 input-text에 넣고 MODAL창을 보여준다.
      },
      error: function(request, status, error) {
        console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error); 
      }
    });
  }
</script>
<!-- 전송관련 끝 -->

<!-- 등급설정관련 시작 -->
<script language="javascript">
  
  $(document).ready(function() {
    
    resetRow();
    getGrades();
  });
  
  //항목 추가
  $(document).on("click", ".btn-add-grade", function () 
  {
    let input = {};
    input.grade = "";
    input.max_hz = 0;
    input.min_hz = 0;

    insertRow(input);
  });

  $(document).on("click", ".btn-set-grade", function () 
  {
    setGrades();
  });

  $(document).on("click", ".btn-del-grade", function () 
  {
    $(this).parents(".measure_grade_row").remove();
  });

  $(document).on("change", "#sel-modal-product_id", function () 
  {
    resetRow();
    getGrades();
  });
  
  //user_author 입력폼을 추가하고 값을 넣기
  let gRow = 0; // 등급 row 수
  function insertRow(input) 
  {
    let $tr     = $("<tr>",{id:"measure_grade_row"+gRow, class:"measure_grade_row" });	
    $product_name = $("<td>",{});
    $grade = $("<td>",{});
    $max_hz = $("<td>",{});
    $min_hz 	= $("<td>",{});
    $btn_del    = $("<td>", {}); 
    product_name_val = input.product_name;
    product_id_val = input.grade_product_id;
    grade_val = input.grade;
    max_hz_val = input.max_hz;
    min_hz_val = input.min_hz;

    $product_name.append($("<input>", {type:"text", value:product_name_val, name:"measure_grades["+ gRow +"][product_name]", class:"bg-c-white w100 outline py4 measure_grade_product_name", readonly:true}));
    $product_name.append($("<input>", {type:"hidden", value:product_id_val, name:"measure_grades["+ gRow +"][grade_product_id]", class:"measure_grade_product_id", readonly:true}));
    $grade.append($("<input>", {type:"text", value:grade_val, name:"measure_grades["+ gRow +"][grade]", class:"bg-c-white w100 outline py4 measure_grade_grade"}));
    $min_hz.append($("<input>", {type:"text", value:min_hz_val, name:"measure_grades["+ gRow +"][min_hz]", class:"bg-c-white w100 outline py4 onlyNumber measure_grade_min_hz"}));
    $max_hz.append($("<input>", {type:"text", value:max_hz_val, name:"measure_grades["+ gRow +"][max_hz]", class:"bg-c-white w100 outline py4 onlyNumber measure_grade_max_hz"}));
    $tr.append($product_name);
    $tr.append($grade);
    $tr.append($min_hz);
    $tr.append($max_hz);

    $("#tb-grade-list > tbody:last").append($tr); //목록 row 삽입
    gRow++;
  }

  function resetRow()
  {
    gRow = 0; // 등급 row 수 초기화
    $("#Modal-grade-message").text("");
    $("#tb-grade-list > tbody:last").empty();
  }

  // getGrades : 등급 목록을 불러온다.
  function getGrades()
  {
    $.ajax({
        url: "./getGrades",
        data: $("#modal-grade-list").serialize(),
        method: "POST",
        dataType: "json",
        success: function(data)
        {
          for(let measure_grade of data)
          {
            insertRow(measure_grade) 
          }
        },
        error: function(request, status, error)
        {
          alert("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" +
            error);
          $("#Modal-grade-message").text("불러오기 실패!");
        }
     });
  }

  // setGrades : 등급목록을 저장한다.
  function setGrades()
  {
    $.ajax({
        url: "./setGrades",
        data: $("#modal-grade-list").serialize(),
        method: "POST",
        success: function(data)
        {
          $("#Modal-grade-message").text("저장 성공!");
        },
        error: function(request, status, error)
        {        
          alert("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" +
            error);
          $("#Modal-grade-message").text("저장 실패!");
        }
      });
  }
</script>
<!-- 등급설정관련 끝 -->
<!-- 설비설정관련 시작 -->
<script language="javascript">
  
  $(document).ready(function() {
    getEquipment();
  });

  $(document).on("click", ".btn-set-equipment", function () 
  {
    setEquipment();
  });

  $(document).on("change", "#sel-modal-equipment_id", function () 
  {
    $("#Modal-equipment-message").text("");
    getEquipment();
  });

  // getEquipment : 등급 목록을 불러온다.
  function getEquipment()
  {
    $.ajax({
        url: "./getEquipment",
        data: $("#modal-equipment-list").serialize(),
        method: "POST",
        dataType: "json",
        success: function(data)
        {
          $("#sel-modal-transfer_flag").val(data.transfer_flag);
        },
        error: function(request, status, error)
        {
          alert("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" +
            error);
          $("#Modal-equipment-message").text("불러오기 실패!");
        }
     });
  }

  // setEquipment : 등급목록을 저장한다.
  function setEquipment()
  {
    $.ajax({
        url: "./setEquipment",
        data: $("#modal-equipment-list").serialize(),
        method: "POST",
        success: function(data)
        {
          $("#Modal-equipment-message").text("저장 성공!");
        },
        error: function(request, status, error)
        {        
          alert("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" +
            error);
          $("#Modal-equipment-message").text("저장 실패!");
        }
      });
  }
</script>
<!-- 설비설정관련 끝 -->


<!--  content wrap start  -->
<div class="content">

  <!--  content start  -->
  <div class="mainbar">

    <!--  main title & Bread crumbs wrap start  -->
    <div class="grid ai-start jc-space-between fw-wrap">
      <div class="grid--cell c-blue fs-title">
        <!--  main title start  -->
        <h1>각인요청</h1>
        <!--  main title end  -->

        <!--  Bread_crumbs start -->
        <ol class="Bread_crumbs mb32">
          <li class="breadcrumb-item"><a href="#">각인요청</a></li>
          <li><i class="fa fa-chevron-right"></i></li>
          <li class="breadcrumb-item"><a href="#">각인요청</a></li>
        </ol>
        <!--  Bread_crumbs end -->

      </div>
    </div>
    <!--  main title & Bread crumbs wrap end  -->

    <!--  main_content start  -->

    <div class="card">      
      <form name="input_form" id="frm-input">
      <div class="container">
        <div class="row">
          <!--  left box content start  -->
          <div class="left-box-contant card-body col col-lg-3 bg-c-light-gray bc-gray overflow-auto">
            <!-- inline height 나중에 class값으로 변경예정!! -->
            <div class="d-flex-js mt6 mb20">
              <!--  left title start  -->
              <h2 class="sub-title fs-body3 input-form-title">요청항목 </h2>
              <!--  left title end  -->

                <!--  left button start  -->
                <div>
                  <br>
                  <span name='error_message' id="error_message" style="color:#FF1616">
                  <!-- 에러메세지가 출력되는 부분 -->
                  </span>
                </div>
                <!--  sub button end  -->
            </div>
            <div class="left-content-list">
                <div class="form-group row">
                  <label for="" class="col-sm-4 bg-c-gay-g">
                    <i class="ace-icon fa fa-caret-right blue"></i>
                    응답메세지
                  </label>
                  <div class="col-sm-8 bg-c-white m-auto">
                    <input type='text' class='bg-c-white w100 outline py4' name='response_message' id="response_message"
                      autocomplete="off" value="<?=$this->search['response_message']?>" readonly>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="" class="col-sm-4 bg-c-gay-g">
                    <i class="ace-icon fa fa-caret-right blue"></i>
                    제품
                  </label>
                  <div class="col-sm-8 bg-c-white m-auto">
                    <select name='product_id' id="measure_product_id" class="custom-select2 measure_product_id">
                      <?
                      $str = "";
                      if($product['id'] == $this->search['product_id'])
                      {
                          $str="selected";
                      }

                      foreach($this->select['products'] as $product)
                      {
                      ?>
                      <option value='<?=$product['id']?>' {$str}><?="[{$product['lot_num']}]{$product['name']}"?></option>
                      <?
                      }
                      ?>
                    </select>
                    <input type='hidden' class='txt-request-carve' name="message"
                      autocomplete="off" value="">
                    <input type='hidden' class='txt-request-grade' name="grade"
                      autocomplete="off" value="">
                    <input type='hidden' class='txt-request-grade_product_id' name="grade_product_id"
                      autocomplete="off" value="">
                    <input type='hidden' name="measure_log_id" class="txt-request-measure_log_id" value="<?=$this->search['message']?>">
                    <input type='hidden' class='txt-transfer-address' name='address'
                      autocomplete="off" value="<?=$this->search['address']?>">
                    <input type='hidden' class='txt-transfer-port' name='port'
                      autocomplete="off" value="<?=$this->search['port']?>">
                    <input type='hidden' class='txt-text_name' name="text_name"
                      autocomplete="off" value="<?=$this->search['text_name']?>">
                  </div>
                </div>
            </div>
          </div>
          <!--  left box content end  -->

          <!--  right box content start  -->

          <div class="card-body col col-lg-8 h-100 d-inline-block">
            
            <!-- right table start -->
            <div class="table-responsive card-body">
                <!-- ================= -->
                <!-- 검색바 시작 -->
                <!-- ================= -->
                <div class="col-lg-12">
                  <form method="get" action="./">
                    <div class="s-form-inline s-form-group s-form-row">
                      <div class="col-4 input-group">
                        <input type="text" name='searchdate' class="bg-c-white w100 outline py4 datepicker"
                          value='<?=$this->search['searchdate']?>'>
                      </div>
                      <div class="col-3 input-group mo-resize">
                        <input type="submit" class="btn2 btn-info mr8" value="검색" />
                        <input type='button' class="btn2 btn-sm mr8 btn-warning btn-setting" value="등급 설정"
                          data-toggle="modal" data-target="#Modal-setting" />
                        <input type='button' class="btn2 btn-sm mr8 btn-warning btn-setting" value="설비 설정"
                          data-toggle="modal" data-target="#Modal-equipment" />
                      </div>
                      <div class="col-5 input-group tblet-none"> 
                      </div>

                    </div>
                  </form>
                </div>
                <!-- ================= -->
                <!-- 검색바 끝 -->
                <!-- ================= -->
              </div>

              <form name="frm_list" id="frm-list">
                <table id="tb-list" class="table table-bordered">
                  <thead class="dddcolor thin-buser-bottom">
                    <tr>
                      <th>
                        <i class="ace-icon fa fa-caret-right blue"></i>
                        NO
                      </th>
                      <th>
                        <i class="ace-icon fa fa-caret-right blue"></i>
                        제품
                      </th>
                      <th>
                        <i class="ace-icon fa fa-caret-right blue"></i>
                        등급
                      </th>
                      <th>
                        <i class="ace-icon fa fa-caret-right blue"></i>
                        Hz
                      </th>
                      <th>
                        <i class="ace-icon fa fa-caret-right blue"></i>
                        Ω
                      </th>
                      <th>
                        <i class="ace-icon fa fa-caret-right blue"></i>
                        측정일시
                      </th>
                      <th>
                        <i class="ace-icon fa fa-caret-right blue"></i>
                        각인여부
                      </th>
                      <th>
                        <i class="ace-icon fa fa-caret-right blue"></i>
                        각인코드
                      </th>
                      <th>
                        <i class="ace-icon fa fa-caret-right blue"></i>
                        각인일시
                      </th>
                      <th>
                        <i class="ace-icon fa fa-caret-right blue"></i>
                        관리
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </form>
              <!-- 설정 modal 시작-->
              <div class="modal fade" id="Modal-setting" role="dialog">
                <div class="modal-dialog">
                  <!-- Modal content-->
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title">각인제품 등급설정</h4>                      
                      <span name='grade_message' id="Modal-grade-message" style="color:#FF1616">
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                      <form id="modal-grade-list">
                      <p align="left">제품</p>
                      <select name='product_id' id="sel-modal-product_id" class="custom-select">
                        <?
                        foreach($this->select['products'] as $product)
                        {
                        ?>
                        <option value='<?=$product['id']?>'><?="[{$product['lot_num']}]{$product['name']}"?></option>
                        <?
                        }
                        ?>
                      </select>
                      <br>
                      <div class="s-form-inline s-form-group s-form-row">
                        <div class="col-3 input-group">
                        </div>
                        <div class="col-3 input-group">
                        </div>
                        <div class="col-4 input-group">
                        </div>
                        <div class="col-2 input-group"> 
                          <input type="button" class="btn btn-success btn-set-grade" value="저장">
                        </div>
                      </div>
                      <table id="tb-grade-list" class="table table-bordered">
                        <thead>
                          <tr>
                            <th>
                              제품명
                            </th>
                            <th>
                              등급명
                            </th>
                            <th>
                              최소진동수
                            </th>
                            <th>
                              최대진동수
                            </th>
                          </tr>
                        </thead>
                        <tbody>
                          
                        </tbody>
                      </table>
                      </form>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-default" data-dismiss="modal">닫기</button>
                    </div>
                  </div>
                </div>
              </div>
              <!-- 설정 modal 끝-->
              <!-- 설비 modal 시작-->
              <div class="modal fade" id="Modal-equipment" role="dialog">
                <div class="modal-dialog">
                  <!-- Modal content-->
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title">설비 전송 사용설정</h4>                      
                      <span name='equipment_message' id="Modal-equipment-message" style="color:#FF1616">
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                      <form id="modal-equipment-list">
                      <p align="left">설비</p>
                      <select name='equipment_id' id="sel-modal-equipment_id" class="custom-select">
                        <?
                        foreach($this->select['equipments'] as $equipment)
                        {
                        ?>
                        <option value='<?=$equipment['id']?>'><?="{$equipment['name']}"?></option>
                        <?
                        }
                        ?>
                      </select>
                      <br>
                      <div class="s-form-inline s-form-group s-form-row">
                        <div class="col-3 input-group">
                        </div>
                        <div class="col-3 input-group">
                        </div>
                        <div class="col-4 input-group">
                        </div>
                        <div class="col-2 input-group"> 
                          <input type="button" class="btn btn-success btn-set-equipment" value="저장">
                        </div>
                      </div>
                      <table id="tb-equipment-list" class="table table-bordered">
                        <thead>
                          <tr>
                            <th>
                              전송감지사용
                            </th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>                              
                              <select name='transfer_flag' id="sel-modal-transfer_flag" class="custom-select">
                                <option value='0'>미사용</option>
                                <option value='1'>사용</option>
                              </select>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                      </form>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-default" data-dismiss="modal">닫기</button>
                    </div>
                  </div>
                </div>
              </div>
              <!-- 설비 modal 끝-->
              <!-- 전송 modal 시작-->
              <div class="modal fade" id="Modal-request" role="dialog">
                <div class="modal-dialog">
                  <!-- Modal content-->
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title">전송확인</h4>
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                      <p align="center">다음 각인코드를 전송합니다.</p>
                      <br>
                      <input type="text" name="message" class="bg-c-white w100 outline py4 txt-request-carve">
                    </div>
                    <div class="modal-footer">
                      <input type="button" id="btn-submit-del" class="btn btn-success btn-submit-carve" value="전송">
                      <button type="button" class="btn btn-default" data-dismiss="modal">닫기</button>
                    </div>
                  </div>
                </div>
              </div>
              <!-- 전송 modal 끝-->
            </div>
            <!--  right table end  -->
          </div>
          <!--  right box conten end  -->
        </div>
      </div>      
      </form>
    </div>
    <!-- main-content end  -->
  </div>
  <!--  content wrap end  -->
</div>
</div>

<!--
    <footer id="footer" class="site-footer js-footer" role="contentinfo">
        <div class="site-footer--container">

        </div>

    </footer>
-->

</body>

</html>