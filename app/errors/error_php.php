<?php if (defined("_API")) { ?>
<?php echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"; ?>
<response>
	<error>00</error>
	<error_text>System PHP Error (line <?php echo $line; ?> of <?php echo $filepath; ?>): <?php echo strip_tags($message); ?></error_text>
</response>
<? die(); ?>
<?php } else { ?>

<div style="border:1px solid #990000;padding-left:20px;margin:0 0 10px 0;">

<h4>A PHP Error was encountered</h4>

<p>Severity: <?php echo $severity; ?></p>
<p>Message:  <?php echo $message; ?></p>
<p>Filename: <?php echo $filepath; ?></p>
<p>Line Number: <?php echo $line; ?></p>

</div>

<?php } ?>