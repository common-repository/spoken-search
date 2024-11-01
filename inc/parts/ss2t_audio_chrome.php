<?php
if ( ! defined('ABSPATH') ) { exit; }

add_action('wp_footer', 'ss2t__audio_chrome');

function ss2t__audio_chrome() {
?>
<script>
function ss2t_recognize_speech() {
	window.SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
	if (window.SpeechRecognition) {
    let ss2tVal = '';
  	let ss2tRecognition = new SpeechRecognition();
    ss2tRecognition.interimResults = false;
    ss2tRecognition.maxAlternatives = 10;
    ss2tRecognition.continuous = false;
    ss2tRecognition.onresult = (event) => {
      let ss2tInterim = '';
      for (let i = event.resultIndex, len = event.results.length; i < len; i++) {
        let transcript = event.results[i][0].transcript;
        if (event.results[i].isFinal) {
          ss2tVal += transcript;
        } else {
          ss2tInterim += transcript;
        }
      }
      var ss2tID = 'ss2t-query';
      ss2t_change_val(ss2tID,ss2tVal);
    }
    ss2tRecognition.start();
	} else {
	  console.log('SS2T: Speech recognition not supported in this browser.');
	}
}
jQuery('.ss2t-action').css('cursor','pointer');
jQuery(document).on( 'click', '.ss2t-action', function() {
  var query = ss2t_recognize_speech();
  return false;
});
</script>
<?php
}
