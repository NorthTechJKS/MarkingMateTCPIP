<script language="javascript">
//상세검색 버튼 눌렀을시 액션
$(document).on("click", ".btn-request-carve", function() 
  {
    $.ajax({
      url: "./carve", 
      data: $("#carve-form").serialize(),
      method: "POST",
      dataType: "json",
      success: function(data) 
      {
        alert(data);
        $("#response_message").val(data);
      },
      error: function(request, status, error) {
        console.log("code:" + request.status + "\n" + "message:" + request.responseText + "\n" + "error:" + error); 
      }
    });
  }
);

</script>

<!--  content wrap start  -->
<div class="content">

  <!--  content start  -->
  <div class="mainbar">

    <!--  main title & Bread crumbs wrap start  -->
    <div class="grid ai-start jc-space-between fw-wrap">
      <div class="grid--cell c-blue fs-title">
        <!--  main title start  -->
        <h1>각인요청 테스트 프로그램</h1>
        <!--  main title end  -->

        <!--  Bread_crumbs start -->
        <ol class="Bread_crumbs mb32">
          <li class="breadcrumb-item"><a href="#">테스트</a></li>
          <li><i class="fa fa-chevron-right"></i></li>
          <li class="breadcrumb-item"><a href="#">테스트</a></li>
        </ol>
        <!--  Bread_crumbs end -->

      </div>
    </div>
    <!--  main title & Bread crumbs wrap end  -->

    <!--  main_content start  -->

    <div class="card">
      <div class="container">
        <div class="row">
          <!--  left box content start  -->
          <div class="left-box-contant card-body col col-lg-3 bg-c-light-gray bc-gray overflow-auto">
            <!-- inline height 나중에 class값으로 변경예정!! -->
            <div class="d-flex-js mt6 mb20">
              <!--  left title start  -->
              <h2 class="sub-title fs-body3 input-form-title">TCP/IP 테스트 페이지</h2>
              <!--  left title end  -->
            </div>

            <div class="left-content-list">
              <form name="input_form" id="frm-input" method="post">
                <div class="form-group row">
                  <label for="" class="col-sm-4 bg-c-gay-g">
                    <i class="ace-icon fa fa-caret-right blue"></i>
                    응답메세지
                  </label>
                  <div class="col-sm-8 bg-c-white m-auto">
                    <input type='text' class='bg-c-white w100 outline py4' name='response_message' id="response_message"
                      autocomplete="off" value="">
                  </div>
                </div>
              </form>
            </div>
          </div>
          <!--  left box content end  -->

          <!--  right box content start  -->

          <div class="card-body col col-lg-8 h-100 d-inline-block">
            <!-- right table start -->
            <div class="table-responsive card-body">
              <form name="carve_form" id="carve-form">
                <table id="" class="table table-bordered">
                  <thead class="dddcolor thin-border-bottom"> 
                  </thead>
                  <tbody> 
                    <tr>
                      <td>
                        IP
                      </td>
                      <td>  
                        <input type='text' class="bg-c-white w100 outline py4 txt-transfer-address" name="address" value="211.37.179.64" />                    
                      </td>
                      <td>
                        PORT
                      </td>
                      <td>  
                        <input type='text' class="bg-c-white w100 outline py4 txt-transfer-port" name="port" value="8889" />                    
                      </td>
                    </tr>   
                    <tr>
                      <td>
                        객체명
                      </td>
                      <td>  
                        <input type='text' class="bg-c-white w100 outline py4 txt-text_name" name="text_name" value="1" placeholder="객체명 입력"/>                    
                      </td>
                      <td>
                      </td>
                    </tr>  
                    <tr>
                      <td>
                        요청메세지
                      </td>
                      <td>  
                        <input type='text' class="bg-c-white w100 outline py4 txt-request-carve" name="message" value="" placeholder="요청 메세지 입력"/>                    
                      </td>
                      <td>
                        <input type='button' class="btn2 btn-success mr8 btn-request-carve" value="선택" />
                      </td>
                    </tr>   
                  </tbody>
                </table>  
              </form>              
              <!-- 삭제 modal 시작-->
              <div class="modal fade" id="Modal-del" role="dialog">
                <div class="modal-dialog">
                  <!-- Modal content-->
                  <div class="modal-content">
                    <div class="modal-header">
                      <h4 class="modal-title">삭제확인</h4>
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                      <p align="center">정말 삭제하시겠습니까?</p>
                    </div>
                    <div class="modal-footer">
                      <input type="hidden" id="del_id">
                      <input type="button" id="btn-submit-del" class="btn btn-danger" value="삭제">
                      <button type="button" class="btn btn-default" data-dismiss="modal">닫기</button>
                    </div>
                  </div>
                </div>
              </div>
              <!-- 삭제 modal 끝-->
            </div>
            <!--  right table end  -->
          </div>
          <!--  right box conten end  -->
        </div>
      </div>
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