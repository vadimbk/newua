<?php if (count($languages) > 1) { ?>
<style>
.langmark {
margin-top: 6px;
color: #888;
}
.langmark a {
color: #888;
}
.langmark a:hover {
color: #333;
}
.langmark a.langmarkactive {
color: #555;
font-weight: bold;
}
</style>
<div class="nav pull-left langmark">
<ul class="list-inline"><?php foreach ($languages as $language) { ?><li><span><a href="<?php echo $language['url']; ?>" <?php if ($language['current']) { ?>  class="langmarkactive" <?php } ?> onclick="window.location = '<?php echo $language['url']; ?>'"><?php echo $language['name']; ?></a></span></li><?php
	if($language !== end($languages)) {
		echo "<li>|</li>";
	}
	?><?php } ?></ul>
</div>
<?php } ?>