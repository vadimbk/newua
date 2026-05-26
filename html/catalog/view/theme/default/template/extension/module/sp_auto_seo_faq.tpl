<?php if ($faq) { ?>
<?php if ($microdata) { ?>
<div class="sp-seo-faq-block" itemscope="itemscope" itemtype="https://schema.org/FAQPage">
<div class="faq-title">
	<?php echo $faq_title; ?>
</div>
<ul>
<?php foreach ($faq as $item) { ?>
	<li class="faq-question" itemscope="itemscope" itemprop="mainEntity" itemtype="https://schema.org/Question">
		<div class="faq-link" itemprop="name"><?php echo $item['question']; ?></div>
		<div class="faq-text" itemscope="itemscope" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer" <?php if (!$expand) { ?>style="display:none;"<?php } ?>>
			<div itemprop="text"><?php echo $item['answer']; ?></div>
		</div>
	</li>
<?php } ?>
</ul>
</div>	
<?php if (!$expand) { ?>
<script>
const slideToggle = (element, duration = 300) => {
	if (window.getComputedStyle(element).display === 'none') {
		element.style.display = 'block';
		const height = element.scrollHeight;
		element.style.overflow = 'hidden';
		element.style.height = '0';
		element.style.transition = `height ${duration}ms ease`;
		element.offsetHeight;
		element.style.height = `${height}px`;
		setTimeout(() => {
			element.style.removeProperty('height');
			element.style.removeProperty('overflow');
			element.style.removeProperty('transition');
		}, duration);
	} else {
		element.style.overflow = 'hidden';
		element.style.height = `${element.scrollHeight}px`;
		element.style.transition = `height ${duration}ms ease`;
		element.offsetHeight;
		element.style.height = '0';
		setTimeout(() => {
			element.style.display = 'none';
			element.style.removeProperty('height');
			element.style.removeProperty('overflow');
			element.style.removeProperty('transition');
		}, duration);
	}
};

document.addEventListener('DOMContentLoaded', function() {
	const faqQuestions = document.querySelectorAll('.faq-question');
	faqQuestions.forEach(function(question) {
		question.addEventListener('click', function() {
			const faqText = this.querySelector('.faq-text');
			const faqLink = this.querySelector('.faq-link');
			if (faqText) {
				slideToggle(faqText, 300);
			}
			if (faqLink) {
				faqLink.classList.toggle('faq-open');
			}
		});
	});
	const internalLinks = document.querySelectorAll(".faq-text a");
	internalLinks.forEach(function(link) {
		link.addEventListener("click", function (e) {
			e.stopPropagation();
		});
	});
});
</script>
<?php } ?>
<?php } ?>
<?php if ($json) { ?>
<script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": [
	  <?php foreach ($faq as $i => $item) { ?>
	  {
        "@type": "Question",
        "name": "<?php echo addslashes($item['question']); ?>",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "<?php echo addslashes($item['answer']); ?>"
        }
      } <?php if ($i + 1 < count($faq)) echo ','; ?>
	  <?php } ?>
	  ]
    }
    </script>
<?php } ?>
<?php } ?>