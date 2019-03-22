<?php

function smarty_outputfilter_googleanalytics ($output, &$smarty) {
	if ($smarty->CI->config->item('googleanalytics_id') != '') {
		$code = "<script type=\"text/javascript\">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '" . $smarty->CI->config->item('googleanalytics_id') . "']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>";

		$output = str_replace('</body>', $code . "\n" . '</body>',$output);
		
		return $output;
	}
	else {
		return $output;
	}
}