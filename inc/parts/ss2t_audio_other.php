<?php
if ( ! defined('ABSPATH') ) { exit; }

add_action('wp_footer', 'ss2t__audio_other');

function ss2t__audio_other() {
?>
<script>
function ss2t_get_token(callback) {
  var ss2t_ajax = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
  var ss2t_nonce = '<?php echo wp_create_nonce( "ss2t_ajax_nonce" ); ?>'; 
  jQuery.ajax({
    type : 'get',
    url  : ss2t_ajax,
    data : {
      action   : 'ss2t_get_spoken_token',
      security : ss2t_nonce
    },
    success : function( response ) {
      callback(response);
    }
  });
}
function ss2t_process_request(response) {
  let authorizationToken = response;
  let token = JSON.parse(atob(response.split(".")[1]));
  let serviceRegion = token.region;
  var speechConfig;
  if (authorizationToken) {
    speechConfig = SpeechSDK.SpeechConfig.fromAuthorizationToken(authorizationToken, serviceRegion);
  }
  speechConfig.speechRecognitionLanguage = "en-US";
  var audioConfig  = SpeechSDK.AudioConfig.fromDefaultMicrophoneInput();
  recognizer = new SpeechSDK.SpeechRecognizer(speechConfig, audioConfig);
  recognizer.recognizeOnceAsync(
    function (result) {
      var ss2tID = 'ss2t-query';
      var text = result.text;
      if ( (text[text.length-1] === ".") || (text[text.length-1] === ",") || (text[text.length-1] === "!") ) {
        text = text.slice(0,-1);
      }
      ss2t_change_val(ss2tID,text);
      recognizer.close();
      recognizer = undefined;
    },
    function (err) {
      recognizer.close();
      recognizer = undefined;
    }
  );
}
jQuery('.ss2t-action').css('cursor','pointer');
jQuery(document).on( 'click', '.ss2t-action', function() {
  if (!!window.SpeechSDK) {
    SpeechSDK = window.SpeechSDK;
  }
  var query = ss2t_get_token(ss2t_process_request);
  return false;
});
</script>
<?php
}
