
<?php 
$login_error = false;

if(isset($_GET['operation'])){
  $delete_id = sanitize_text_field($_GET['operation']);
  $del = explode(' ', $delete_id);
  if(isset($del[1])){
    wp_delete_user(sanitize_text_field($del[1]));
    $traitway_users_data = get_option('ceed_user_datas');
    unset($traitway_users_data[$del[1]]);
    update_option('ceed_user_datas',$traitway_users_data);
  }
}

$traitway_users_data = get_option('ceed_user_datas');
if(isset($_POST) && !empty($_POST) && isset( $_POST['email'])){

  $user_id = wp_insert_user( array(
    'user_login' => sanitize_email($_POST['email']),
    'user_pass' => sanitize_text_field($_POST['Password']),
    'user_email' => sanitize_email($_POST['email']),
    'first_name' => '',
    'last_name' => '',
    'display_name' =>sanitize_email($_POST['email']),
    'role' => sanitize_text_field($_POST['user_role'])
  ));

  if(!isset($user_id->errors)){
    $_POST['user_id'] = $user_id;
    $UTC = sanitize_text_field($_POST['date']).' UTC '.get_option('gmt_offset');
    update_option('ceed_email_data',$_POST['default_email_content']);
    $_POST['default_email_content'] = str_replace('[CEED_VALID_TIME]',$UTC,$_POST['default_email_content']);
    $_POST['default_email_content'] = str_replace('[CEED_EMAIL]',sanitize_email($_POST['email']) ,$_POST['default_email_content']);
    $_POST['default_email_content'] = str_replace('[CEED_PASSWORD]',sanitize_text_field($_POST['Password']) ,$_POST['default_email_content']);

    if(isset($_POST['email_subject']) && !empty($_POST['email_subject'])){
      $subject_line = $_POST['email_subject'];
    }else{
      $subject_line = 'Login Details';
    }

    //E-mail the credentails
    if($_POST['email_condition']){
      wp_mail(sanitize_email($_POST['email']),$subject_line,$_POST['default_email_content'], '', array());
    }
    
    update_option($user_id.'_ceed_expired',strtotime($_POST['date']));
    $traitway_users_data = get_option('ceed_user_datas');
     if($traitway_users_data){
        $data_to_store = array_map('sanitize_text_field', $_POST);
        $traitway_users_data[$user_id] = $data_to_store;
        update_option('ceed_user_datas',$traitway_users_data);  
      }else{
        $user_data = array();
        $data_to_store = array_map('sanitize_text_field', $_POST);
        $user_data[$user_id] = $data_to_store;
        update_option('ceed_user_datas',$user_data);   
      }
  }else{
    $login_error = true;
  }
}
elseif (!empty($_POST['email_edit']) && isset($_POST['id'])) {
  $data = wp_delete_user(sanitize_text_field($_POST['id']));
  $_POST['email'] = sanitize_email($_POST['email_edit']);
  $_POST['Password'] = sanitize_text_field($_POST['password_edit']);
  $user_id = wp_insert_user( array(
    'user_login' => sanitize_email($_POST['email']),
    'user_pass' => sanitize_text_field($_POST['Password']),
    'user_email' => sanitize_email($_POST['email']),
    'first_name' => '',
    'last_name' => '',
    'display_name' =>sanitize_email($_POST['email']),
    'role' => sanitize_text_field($_POST['user_role'])
  ));
  $_POST['user_id'] = $user_id;
  $traitway_users_data = get_option('ceed_user_datas');
  if(isset($traitway_users_data[$_POST['id']])){
    unset($traitway_users_data[$_POST['id']]);
  }
  if($traitway_users_data){
    $data_to_store = array_map('sanitize_text_field', $_POST);
    $traitway_users_data[$user_id] = $data_to_store;
    update_option('ceed_user_datas',$traitway_users_data); 
  }else{
    $user_data = array();
    $data_to_store = array_map('sanitize_text_field', $_POST);
    $user_data[$user_id] = $data_to_store;
    update_option('ceed_user_datas',$user_data);
  }
  $traitway_users_data = get_option('ceed_user_datas');
}

?>
<script type="text/javascript">
  
function openPage(pageName,elmnt,color) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablink");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].style.backgroundColor = "";
  }
  document.getElementById(pageName).style.display = "block";
  elmnt.style.backgroundColor = color;
}
</script>

<div class="traitway_task_ceed" style="border: 2px solid #555;width: 98%;margin-top:10px;height: fit-content;">
  <div class="traitway_page">
    <button class="tablink" onclick="openPage('Traitway_create_login', this, '#262364')" id="defaultOpen">Create Login</button>
    <button class="tablink" onclick="openPage('Traitway_check_login', this, '#262364')">Manage Login Details</button>

    <?php $url = CEED_MAIN_URL."includes/office_logo.PNG";?>

    <div id="Traitway_create_login" class="tabcontent">
      <img src="<?php echo($url);?>" style="float: right;"/>
      <?php if($login_error){?>
        <div class="notice notice-error" style="color: black !important">
              <p>
                <strong>USER CREATE ERROR : </strong><?php echo($_POST['email']);?> already exists!    </p>
        </div>
      <?php
      }?>
      <form class="ceed_form" action="" method="post">
        <div class="form-group">
          <label for="exampleInputEmail1">Email Address</label> <span style="color: red">*</span>
          <input type="email" name="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter Email" required>
        </div>
        <div class="form-group">
          <label for="exampleInputPassword1">Password</label> <span style="color: red">*</span>
          <input type="text" name="Password" class="form-control" id="exampleInputPassword1" placeholder="Enter Password" required>
        </div>
        <div class="form-group">
          <label for="user_role">User Role</label>
          <select class="form-control" name="user_role" id="user_role">
            <?php wp_dropdown_roles( 'administrator' ); ?>
          </select>
        </div>

        <div class="form-group">
          <label>Expiry Date</label> <span style="color: red">*</span>
              <div class="form-control input-group date form_datetime col-md-5" data-date="2017-09-16T05:25:07Z" data-date-format="dd MM yyyy - HH:ii p" data-link-field="dtp_input1" required>
                <input class="form-control" name="date" size="16" type="text" value="" style="width: 80% !important;" readonly data-toggle="tooltip" title="Select login expiry date and time">
                <span class="input-group-addon"><span class="dashicons dashicons-calendar-alt"></span></span>
                <span class="input-group-addon"><span class="dashicons dashicons-no"></span></span>
              </div>
              <input type="hidden" id="dtp_input1" value="" /><br/>
        </div>
        <div class="form-group form-check">
          <input type="checkbox"  name="email_condition" id="exampleCheck1" checked>
          <label class="form-check-label" style="color: black" for="exampleCheck1"> Email the login details</label>
        </div>
        <div class="form-group">
          <label for="exampleInputsubject">Email Subject</label>
          <input type="text" name="email_subject" class="form-control" id="exampleInputsubject" placeholder="Login Details">
        </div>
        <div class="form-group form-check">
          <label class="form-check-label" style="color: black" for="exampleCheck1"> Use This Shortcodes: [CEED_EMAIL] , [CEED_PASSWORD] , [CEED_VALID_TIME]</label>
        </div>


        <div class="form-group">
          <?php 
            $default_content = get_option('ceed_email_data');

            if(empty($default_content)){
              $default_content = "Hi There!</br>

                  Please find your login details here!</br>

                  E-mail : [CEED_EMAIL] </br>
                  Password : [CEED_PASSWORD]</br>
                  Login url: </br>
                  Mentioned login credentials will be valid till [CEED_VALID_TIME]</br>
                  Thanks .";
            }
            wp_editor($default_content,'1',array('textarea_name'=>'default_email_content','media_buttons' => false,'name' => 'email_cont'));
          ?>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
      </form>
    </div>
    <?php $traitway_users_data = get_option('ceed_user_datas'); ?>
    <div id="Traitway_check_login" class="tabcontent">
      <div class="edit_ceed" >
        <div class="container" style="color: black !important;display: flex;">
                <div class="table-wrapper">
                    <div class="table-title">
                        <div class="row">
                            <div class="col-sm-8"><h2><b>Active Logins</b></h2></div>
                              <div class="col-sm-4">
                                  <button type="button" style="display: none;" class="btn btn-info add-new"><i class="fa fa-plus"></i> Add New</button>
                              </div>
                        </div>
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Email Address</th>
                                <th>Password</th>
                                <th>Role</th>
                                <th>Expires On</th>
                                <th>Edit/Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                          <?php 
                            if($traitway_users_data){
                              foreach ($traitway_users_data as $key => $value) {?>
                                <tr>
                                    <td><?php echo($value['email']);?></td>
                                    <td><?php echo($value['Password']);?></td>
                                    <td><?php echo($value['user_role']);?></td>
                                    <td class="date_edit"><?php echo($value['date']);?></td>
                                    <td>
                                        <a class="edit" data-toggle="modal" data-target="#<?php echo($key);?>"  title="Edit" data-toggle="tooltip"><i class="material-icons"></i></a>
                                        <a class="delete <?php echo($key);?>" title="Delete" data-toggle="tooltip"><i class="material-icons"></i></a>
                                    </td>
                                </tr>  
                                
                                <div class="modal fade" id="<?php echo($key);?>" role="dialog">
                                  <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                      <div class="modal-header">
                                        <?php echo($value['email']);?>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                      </div>
                                      <div class="modal-body">
                                        <form  class="ceed_form" action="" method="post">

                                          <input type="hidden" name="email_edit" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter email" value="<?php echo($value['email']);?>" required>

                                          <input type="text" name="password_edit" class="form-control" id="exampleInputPassword1" placeholder="Password" value="<?php echo($value['Password']);?>" required>


                                          <div class="form-group">
                                            <label>Expired Date</label>
                                            <div class="form-control input-group date form_datetime col-md-5" data-date="2017-09-16T05:25:07Z" data-date-format="dd MM yyyy - HH:ii p" data-link-field="dtp_input1">
                                                <input class="form-control" name="date" size="16" type="text" value="<?php echo($value['date']);?>" style="width: 80% !important;" readonly required>
                                                <span class="input-group-addon"><span class="dashicons dashicons-calendar-alt"></span></span>
                                                <span class="input-group-addon"><span class="dashicons dashicons-no"></span></span>
                                            </div>
                                            <input type="hidden" id="dtp_input1" value="" /><br/>
                                            <small id="emailHelp" class="form-text text-muted">Change expired date and time.</small>
                                          </div>
                                          <div class="form-group">
                                            <select class="form-control" name="user_role">
                                              <?php wp_dropdown_roles($value['user_role']); ?>
                                            </select>
                                            <small id="emailHelp" class="form-text text-muted">Select user role.</small>
                                          </div>
                                          <div class="form-group">
                                            <input type="hidden" name="id" value="<?php echo($key);?>">
                                          </div>
                                          <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </form>
                                      </div>
                                      <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                      </div>
                                    </div>
                                  </div>
                                </div><?php 
                              }
                            } 
                          ?>   
                      </tbody>
                  </table>
                  </div>
        </div>
      </div>    
    </div>
  </div>

</div>
