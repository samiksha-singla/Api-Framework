<table cellspacing="0" cellpadding="10" style="color:#666;font:13px Arial;line-height:1.4em;width:100%;">
	<tbody>
		<tr>
            <td style="color:#4D90FE;font-size:22px;border-bottom: 2px solid #4D90FE;">
				Cimba User Registeration            </td>
		</tr>
		<tr>
            <td style="color:#777;font-size:16px;padding-top:5px;">
            	            </td>
		</tr>
		<tr>
            <td>
                  <p><?php echo $salutation_message?></p>
                  <p></p>
                  <p><?php echo $greeting_message;?></p>
                  <p></p>
                  <?php if(count($message_content) > 0):?>
                      <?php foreach($message_content as $key=>$value){?>
                          <p><?php echo $value;?></p>
                      <?php };?>
                  <?php endif;?>
	            </td>
		</tr>
		<tr>
            <td style="padding:15px 20px;text-align:right;padding-top:5px;border-top:solid 1px #dfdfdf">
				
			</td>
		</tr>
	</tbody>
</table>