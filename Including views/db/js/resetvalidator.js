$(document).ready(function(){
   
   // add custom validator
    $.validator.addMethod(
        "patternmatch", 
        function(value, element) {
            return this.optional(element) ||/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]{9,15}$/.test(value);
        },
        "Password does not match required criteria"
    );
   
   
   $('#reset').validate({
      rules:{
         newpassword:{
            required:true,
            patternmatch:true,
         },
         cnewpassword:{
            required:true,
            equalTo:"#password"
         }
      },
      messages:{
         newpassword:{
            required:"Please enter new password",
            patternmatch:'Please enter minimum 9 and maximum 15 characters at least 1 uppercase alphabet, 1 lowercase alphabet, 1 number and 1 special character',
         },
         cnewpassword:{
            required:"Please enter confirm password",
            equalTo:"Both password does not match"
         }
      }
   })
})
