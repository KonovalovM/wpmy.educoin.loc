<?php get_header() ?>

<h2 class="page-title"><?=edc__( 'Страница 404' )?></h2>
<br>
<br>

<p>К сожалению объекта <i><?=get_site_url() . $_SERVER['REQUEST_URI']?></i> не обнаружено...</p>
<p>Будем благодарны, если сообщишь нам об этом.</p>
<p>Наши контакты находятся в <a href="<?=get_permalink( 508 )?>" target="_blank">"<?=get_the_title( 508 )?>"</a>.</p>

<?php get_footer() ?>