
</div>


<!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->

<!-- BEGIN CORE PLUGINS -->
<script type="text/javascript">
	var app = new Object();
</script>

<script type="text/javascript" src="<?php echo $GLOBALS['config']['cms']['design_path']; ?>js/lang/<?php echo $_SESSION['cms']['language_code']; ?>.js?v=<?php echo $GLOBALS['config']['cms']['build_version']; ?>"></script>

<!-- END PAGE LEVEL PLUGINS -->

<!-- BEGIN PAGE LEVEL SCRIPTS -->
<?php if(isset($GLOBALS['cms']['footerJSInlineCode'])) {
	echo $GLOBALS['cms']['footerJSInlineCode'];
} ?>
<?php
if(isset($GLOBALS['cms']['includeJS'])) {
	$include_js_set = array_unique($GLOBALS['cms']['includeJS']);
	foreach($include_js_set as $js) {
		echo '<script type="text/javascript" src="'.$js.'?v='.$GLOBALS['config']['cms']['build_version'].'"></script>'."\n";
	}
	unset($js);
}

?>
<script type="text/javascript">
	var design_path = '<?php echo $GLOBALS['config']['cms']['design_path'];?>';
	var theme_path = '<?php echo $GLOBALS['config']['cms']['theme_path'];?>';
	$(document).ready(function () {
		<?php if(isset($GLOBALS['cms']['footerJS'])) {
		echo $GLOBALS['cms']['footerJS'];
	} ?>
		$("html").removeClass("loadstate");
	});
	var page_limit = '<?php echo $GLOBALS['config']['cms']['page_limit'];?>';
	var language_code = '<?php echo $_SESSION['cms']['language_code'];?>';
	<?php if(isset($GLOBALS['cms']['footerJSInline'])) {
		echo $GLOBALS['cms']['footerJSInline'];
	} ?>
</script>

</body>
<!-- END BODY -->
</html>