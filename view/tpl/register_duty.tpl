{{include file="field_input.tpl" field=$register_duty}}
<pre id="zar083msg" class='zarhid'></pre>
<script>
  // @hilmar |->
  typeof(window.tao) == 'undefined' ? window.tao = {} : '';
  tao.zar = { vsn: '2.0.0', s: {}, t: {} };
  {{$tao}}
  $('head').append(
  '<style> '+
  '  .zarmsg { font-family: monospace; }'+
  '  .zarhid { visibility: hidden; }'+
  '</style>');
  tao.zar.op = 'zar083';
 $('#zar083a').click( function() {
   $.ajax({
       type: 'POST', url: 'admin/site', 
      data: {
      	zarop: tao.zar.op,
        register_duty: $('#id_register_duty').val(),
        form_security_token: $("input[name='form_security_token']").val() 
      }
    }).done( function(r) {
      tao.zar.r = JSON.parse(r);
      $('#zar083msg').attr('style', 'visibility: visible;');
      $('#zar083msg').text(tao.zar.r.msgbg);
    })
 });
 </script>