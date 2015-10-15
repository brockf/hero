<?php

function smarty_function_disqus_comments ($params, &$smarty) {
	$shortname = setting('disqus_shortname');
	
	if (empty($shortname)) {
		return show_error('You must configure your Disqus comments section at Configuration > Disqus Comments in the control panel.');
	}
	
	return "<style>#dsq-content div, #dsq-content p, #dsq-content h3 { clear:none !important; } #dsq-content { overflow:auto !important; }</style>
	<div id=\"disqus_thread\"></div>
<script type=\"text/javascript\">
    var disqus_shortname = '" . $shortname . "';
	var disqus_identifier = '" . uri_string() . "';
	var disqus_url = '" . current_url() . "';

    (function() {
        var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
        dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
        (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
    })();
</script>
<noscript>Please enable JavaScript to view the <a href=\"http://disqus.com/?ref_noscript\">comments powered by Disqus.</a></noscript>";
}