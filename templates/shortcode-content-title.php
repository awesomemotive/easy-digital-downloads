<?php
use bpmj\wpidea\View_Hooks;
?>
<h3 itemprop="name" class="edd_download_title">
	<a  <?php View_Hooks::run(View_Hooks::RENDER_INLINE_ELEMENTS_IN_HYPERLINK_PRODUCT, get_the_ID()); ?> title="<?php the_title_attribute(); ?>" itemprop="url" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
</h3>
