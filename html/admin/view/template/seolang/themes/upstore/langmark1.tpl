<?php if (count($languages) > 1) { ?>
	<style>
		.langmark {
			margin-right: 0;
			color: #888;
		}

		.langmark a {
			color: #888;
			cursor: pointer;
		}

		.langmark a:hover {
			color: #555;
			text-decoration: none;
		}

		.langmark li {
			display: inline;
			margin: 0 0 0 4px;
			padding: 0;
		}

		.langmark a.langmarkactive {
			color: #333;
			font-weight: bold;
		}

		.btn-language-top:not(.langmarkactive) {
			background: transparent;
		}

		.langmark .uf {
			width: 16px;
			height: 11px;
			float: left;
			margin-right: 0;
			padding: 0;
			margin-top: 5px;
			background-image: linear-gradient(to bottom, #0082D1, #0082D1 49%, #FFD100 51%, #FFD100);
		}

		.btn-lang-select a {
			color: inherit;
		}
	</style>
	<div class="pull-left nav langmark box-language">
		<form id="language">
			<span class="mob-text-language"><?php echo $text_language; ?></span>
			<?php foreach ($languages as $language) { ?>
				<?php if ($language['main']) { ?>
					<a class="btn-language-top dropdown-toggle <?php if ($language['current']) { ?>langmarkactive<?php } ?>" onclick="lm_deleteCookie('languageauto'); window.location = '<?php echo $language['url']; ?>'"><?php echo $language['name']; ?></a>
				<?php } else { ?>
					<a href="<?php echo $language['url']; ?>" class="btn-language-top dropdown-toggle <?php if ($language['current']) { ?>langmarkactive<?php } ?>" onclick="lm_setCookie('languageauto', '1', {expires: 180}); window.location = '<?php echo $language['url']; ?>'"><?php echo $language['name']; ?></a>
				<?php } ?>
			<?php } ?>
			<ul class="dropdown-menu dropdown-menu-right up-compact-dropdown">
				<?php foreach ($languages as $language) { ?>
					<li <?php if ($language['current']) { ?>class="active" <?php } ?>>
						<?php if ($language['main']) { ?>
							<button class="btn-lang-select" type="button" onclick="lm_deleteCookie('languageauto'); window.location = '<?php echo $language['url']; ?>'">
							<?php } else { ?>
								<button class="btn-lang-select" type="button" onclick="lm_setCookie('languageauto', '1', {expires: 180}); window.location = '<?php echo $language['url']; ?>'">
								<?php } ?>

								<?php echo $language['name']; ?></button>
					</li>
				<?php } ?>
			</ul>

		</form>
	</div>
	<script>
		function lm_setCookie(name, value, options = {}) {
			options = {
				path: '/',
				...options
			};
			let date = new Date(Date.now() + (86400e3 * options.expires));
			date = date.toUTCString();
			options.expires = date;

			let updatedCookie = encodeURIComponent(name) + "=" + encodeURIComponent(value);

			for (let optionKey in options) {
				updatedCookie += "; " + optionKey;
				let optionValue = options[optionKey];
				if (optionValue !== true) {
					updatedCookie += "=" + optionValue;
				}
			}
			document.cookie = updatedCookie;
		}

		function lm_deleteCookie(name) {
			lm_setCookie(name, "", {
				'max-age': -1
			});
		}
	</script>
<?php } ?>