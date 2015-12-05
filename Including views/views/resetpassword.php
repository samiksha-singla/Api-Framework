<!DOCTYPE html>
<html>
   <head>
      <title>CIMBA</title>      
         <link href="/css/style.css" media="screen" rel="stylesheet" type="text/css" >
         <link href="/css/login_form.css" media="screen" rel="stylesheet" type="text/css" >
         <script src="/js/jquery-1.10.2.min.js"></script>
         <script src="/js/jquery.validate.js"></script>
         <script src="/js/resetvalidator.js"></script>       
   </head>
   <body>
      <div id="container">
         <header class="header">
            <img style='float: left' src="/images/cimba_logo_1C_white_italy.png" width="180px" height="52" alt="">
         </header>
         <div class="content">
         <div class="login_wrapper">
            <h1>Reset Password</h1>
            <div class="login_form">
               <div style="color:red;text-align: center"><?php echo $message ?></div>
               <form name="reset" id="reset" method="post" class="niceform">
                  <div class="login_form_wrap" style='width:100%'>
                     <div class="login_element">
                        <div style='width:40%;float:left'>
                           <span style='width:60%;float:right'><label>Password:</label></span>
                        </div>
                        <div style='width:60%;float:left'>
                          <input type="password" name="newpassword" id="password" value="" style="float: left">              
                        </div>
                     </div>
                     <div class="login_element">
                        <div style='width:40%;float:left'>
                           <span style='width:60%;float:right'><label>Confirm Password:</label></span>
                        </div>
                        <div style='width:60%;float:left'>
                          <input type="password" name="cnewpassword" id="cpassword" value="" style="float: left">               
                        </div>
                     </div>
                     <div class="login_element" style='text-align: center;margin-left: -8%;'>
                     <input type="submit" name="submit" id="submit" value="submit">            </div>
                  </div>
               </form>
            </div>
         </div>
         </div>
      </div>
      <div style="min-height: 50px; clear: both;"></div>
      <footer class="footer">       
      </footer>
   </body>
   </html>
